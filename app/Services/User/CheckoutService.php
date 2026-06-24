<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Models\Event;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Notifications\SendETicketNotification;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CheckoutService
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    /**
     * Create an Order with atomic stock reservation.
     */
    public function createOrder(
        User $user,
        Event $event,
        array $ticketSelections,
        array $merchSelections,
        array $holderNames
    ): Order {
        // Enforce server-side cancellation guard (Task 4.6)
        if ($event->status !== EventStatus::Published) {
            throw new InvalidArgumentException('Tiket tidak dapat dibeli karena penjualan untuk acara ini telah ditutup.');
        }

        return DB::transaction(function () use ($user, $event, $ticketSelections, $merchSelections, $holderNames) {
            // Lock Event to prevent state change
            $lockedEvent = Event::where('id', $event->id)->lockForUpdate()->firstOrFail();
            if ($lockedEvent->status !== EventStatus::Published) {
                throw new InvalidArgumentException('Tiket tidak dapat dibeli karena penjualan untuk acara ini telah ditutup.');
            }

            $orderItems = [];
            $ticketsToCreate = [];
            $merchToCreate = [];
            $subtotal = 0;

            // 1. Process Ticket Selections
            foreach ($ticketSelections as $catId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                // Lock ticket category
                $category = TicketCategory::where('id', $catId)
                    ->where('event_id', $event->id)
                    ->lockForUpdate()
                    ->first();

                if (! $category) {
                    throw new InvalidArgumentException('Kategori tiket tidak valid.');
                }

                if (! $category->is_active) {
                    throw new InvalidArgumentException("Kategori tiket '{$category->name}' saat ini tidak aktif.");
                }

                if ($category->sale_start_at && now()->lt($category->sale_start_at)) {
                    throw new InvalidArgumentException("Penjualan tiket '{$category->name}' belum dimulai.");
                }

                if ($category->sale_end_at && now()->gt($category->sale_end_at)) {
                    throw new InvalidArgumentException("Penjualan tiket '{$category->name}' telah berakhir.");
                }

                // Check quota
                $remainingQuota = $category->quota - $category->sold_count;
                if ($qty > $remainingQuota) {
                    throw new InvalidArgumentException("Kategori tiket '{$category->name}' kehabisan kuota. Tersisa: {$remainingQuota}");
                }

                // Validate max_per_user cap (including previous active orders)
                $previousTicketsCount = OrderTicket::whereHas('order', function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->whereIn('status', [OrderStatus::Paid, OrderStatus::Pending])
                        ->where(function ($sq) {
                            $sq->whereNull('stock_reserved_until')
                                ->orWhere('stock_reserved_until', '>=', now());
                        });
                })
                    ->where('ticket_category_id', $category->id)
                    ->count();

                if ($previousTicketsCount + $qty > $category->max_per_user) {
                    $allowed = $category->max_per_user - $previousTicketsCount;
                    throw new InvalidArgumentException("Batas maksimal pembelian untuk kategori '{$category->name}' adalah {$category->max_per_user} tiket. Anda hanya dapat membeli {$allowed} tiket lagi.");
                }

                // Add to process lists
                $subtotal += $category->price * $qty;
                $category->increment('sold_count', $qty);

                for ($i = 0; $i < $qty; $i++) {
                    $name = $holderNames[$catId][$i] ?? $user->name;
                    $ticketsToCreate[] = [
                        'ticket_category_id' => $category->id,
                        'holder_name' => $name,
                        'unit_price' => $category->price,
                    ];
                }
            }

            if (empty($ticketsToCreate)) {
                throw new InvalidArgumentException('Minimal harus memilih 1 tiket untuk melakukan checkout.');
            }

            // 2. Process Merchandise Selections
            foreach ($merchSelections as $variantId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                // Lock merchandise variant
                $variant = MerchandiseVariant::where('id', $variantId)
                    ->whereHas('item', function ($q) use ($event) {
                        $q->where('event_id', $event->id)->where('is_available', true);
                    })
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw new InvalidArgumentException('Varian merchandise tidak valid.');
                }

                if ($qty > $variant->stock) {
                    throw new InvalidArgumentException("Stok merchandise '{$variant->item->name} - {$variant->variant_value}' tidak cukup. Tersisa: {$variant->stock}");
                }

                $price = $variant->final_price;
                $subtotal += $price * $qty;
                $variant->decrement('stock', $qty);

                $merchToCreate[] = [
                    'merchandise_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                ];
            }

            // 3. Compute final costs
            $serviceFee = 15000;
            $tax = (int) round($subtotal * 0.1);
            $totalAmount = $subtotal + $serviceFee + $tax;

            // Generate unique midtrans_order_id
            $midtransOrderId = 'JF-'.strtoupper(Str::random(10));
            while (Order::where('midtrans_order_id', $midtransOrderId)->exists()) {
                $midtransOrderId = 'JF-'.strtoupper(Str::random(10));
            }

            // 4. Create Order
            $order = Order::create([
                'id' => (string) Str::uuid(),
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => OrderStatus::Pending,
                'total_amount' => $totalAmount,
                'payment_type' => null,
                'snap_retry_count' => 0,
                'midtrans_order_id' => $midtransOrderId,
                'stock_reserved_until' => now()->addMinutes(15),
            ]);

            // 5. Create tickets
            foreach ($ticketsToCreate as $ticket) {
                OrderTicket::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'ticket_category_id' => $ticket['ticket_category_id'],
                    'qr_token' => (string) Str::uuid(),
                    'holder_name' => $ticket['holder_name'],
                    'unit_price' => $ticket['unit_price'],
                ]);
            }

            // 6. Create merchandise
            foreach ($merchToCreate as $merch) {
                OrderMerchandise::create([
                    'id' => (string) Str::uuid(),
                    'order_id' => $order->id,
                    'merchandise_variant_id' => $merch['merchandise_variant_id'],
                    'merch_token' => (string) Str::uuid(),
                    'quantity' => $merch['quantity'],
                    'unit_price' => $merch['unit_price'],
                ]);
            }

            // 7. Request Midtrans Snap Token
            $snapToken = $this->midtransService->createSnapToken($order);
            if ($snapToken) {
                $order->update(['snap_token' => $snapToken]);
            }

            return $order;
        });
    }

    /**
     * Process transaction status update from Midtrans.
     */
    public function updateOrderStatus(Order $order, string $transactionStatus, string $paymentType, string $transactionId): void
    {
        DB::transaction(function () use ($order, $transactionStatus, $paymentType, $transactionId) {
            $order->lockForUpdate();

            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                if ($order->status === OrderStatus::Paid) {
                    return;
                }

                $order->update([
                    'status' => OrderStatus::Paid,
                    'paid_at' => now(),
                    'payment_type' => $paymentType,
                    'midtrans_transaction_id' => $transactionId,
                ]);

                // Dispatch Email Notification containing E-tickets & QR Codes
                try {
                    $order->user->notify(new SendETicketNotification($order));
                } catch (\Exception $e) {
                    Log::error('Failed to send E-ticket email notification', [
                        'order_id' => $order->id,
                        'message' => $e->getMessage(),
                    ]);
                }

            } elseif ($transactionStatus === 'pending') {
                if (in_array($paymentType, ['bank_transfer', 'echannel', 'permata_va']) || str_contains($paymentType, 'va')) {
                    $order->update([
                        'payment_type' => $paymentType,
                        'stock_reserved_until' => $order->created_at->addHours(24),
                    ]);
                    Log::info("Extended stock reservation to 24 hours for bank transfer order: {$order->id}");

                }

            } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                if (in_array($order->status, [OrderStatus::Failed, OrderStatus::Cancelled])) {
                    return;
                }

                $newStatus = $transactionStatus === 'deny' ? OrderStatus::Failed : OrderStatus::Cancelled;
                $order->update([
                    'status' => $newStatus,
                    'failed_at' => $transactionStatus === 'deny' ? now() : null,
                    'cancelled_at' => in_array($transactionStatus, ['expire', 'cancel']) ? now() : null,
                ]);

                $order->load(['tickets', 'merchandise']);
                foreach ($order->tickets as $ticket) {
                    $ticket->ticketCategory()->decrement('sold_count', 1);
                }
                foreach ($order->merchandise as $merch) {
                    $merch->merchandiseVariant()->increment('stock', $merch->quantity);
                }

                Log::info("Released reserved stock back for failed/cancelled order: {$order->id}");

                $action = $newStatus === OrderStatus::Failed ? 'order_failed' : 'order_cancelled';
                $reason = $transactionStatus === 'expire' ? 'Payment reservation expired' : ($transactionStatus === 'cancel' ? 'Cancelled by user/gateway' : 'Payment denied');

            }
        });
    }
}
