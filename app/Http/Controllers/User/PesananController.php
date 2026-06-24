<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\QrCodeService;
use App\Services\User\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesananController extends Controller
{
    /**
     * Daftar pesanan dengan filter status dan pencarian.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Order::with('event')
            ->withCount([
                'tickets',
                'tickets as tickets_checked_in_count' => fn ($q) => $q->where('is_checked_in', true),
                'merchandise',
                'merchandise as merchandise_picked_up_count' => fn ($q) => $q->where('is_picked_up', true),
            ])
            ->where('user_id', Auth::id());

        if ($status && $status !== 'semua') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('midtrans_order_id', 'like', "%{$search}%")
                    ->orWhereHas('event', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $pesanan = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('user.pesanan', compact('pesanan'));
    }

    /**
     * Detail satu pesanan.
     */
    public function show(string $id, QrCodeService $qrCodeService, MidtransService $midtransService, CheckoutService $checkoutService): View
    {
        $pesanan = Order::with(['event', 'tickets.ticketCategory', 'merchandise.merchandiseVariant.item'])
            ->findOrFail($id);

        abort_if($pesanan->user_id !== Auth::id(), 403);

        // Active status polling: If order is pending, check with Midtrans to see if paid
        if ($pesanan->status === OrderStatus::Pending) {
            $statusData = $midtransService->getTransactionStatus($pesanan->midtrans_order_id ?? $pesanan->id);
            if ($statusData) {
                $transactionStatus = $statusData['transaction_status'] ?? '';
                $paymentType = $statusData['payment_type'] ?? '';
                $transactionId = $statusData['transaction_id'] ?? '';

                $checkoutService->updateOrderStatus($pesanan, $transactionStatus, $paymentType, $transactionId);
                $pesanan->refresh();
            }
        }

        // Auto-cancel expired pending order to release stock on page view
        if ($pesanan->status === OrderStatus::Pending && $pesanan->isExpired()) {
            $checkoutService->updateOrderStatus($pesanan, 'expire', $pesanan->payment_type ?? '', $pesanan->midtrans_transaction_id ?? '');
            $pesanan->refresh();
        }

        $ticketQrs = [];
        $merchQrs = [];

        if ($pesanan->status === OrderStatus::Paid) {
            foreach ($pesanan->tickets as $ticket) {
                if ($ticket->qr_token) {
                    $ticketQrs[$ticket->id] = $qrCodeService->generateSvg($ticket->qr_token);
                }
            }

            foreach ($pesanan->merchandise as $merch) {
                if ($merch->merch_token) {
                    $merchQrs[$merch->id] = $qrCodeService->generateSvg($merch->merch_token);
                }
            }
        }

        // Hitung apakah checkout session masih valid untuk retry payment
        $canRetryPayment = false;
        if ($pesanan->status === OrderStatus::Pending && $pesanan->snap_token) {
            if ($pesanan->stock_reserved_until && now()->lt($pesanan->stock_reserved_until)) {
                $canRetryPayment = true;
            }
        }

        return view('user.detail-pesanan', compact('pesanan', 'ticketQrs', 'merchQrs', 'canRetryPayment'));
    }

    /**
     * Unduh invoice (placeholder).
     */
    public function invoice(string $id): RedirectResponse
    {
        // TODO: generate PDF invoice
        return back()->with('info', 'Fitur unduh invoice segera hadir.');
    }

    /**
     * Ulangi pembayaran pesanan yang tertunda.
     */
    public function retryPayment(Order $order, MidtransService $midtransService): RedirectResponse
    {
        abort_if($order->user_id !== Auth::id(), 403);

        if (! $order->canRetryPayment()) {
            return back()->with('error', 'Pembayaran tidak dapat diulang (mungkin sudah kedaluwarsa atau batas percobaan habis).');
        }

        try {
            DB::transaction(function () use ($order, $midtransService) {
                $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();
                if (! $lockedOrder->canRetryPayment()) {
                    throw new \InvalidArgumentException('Batas waktu pembayaran habis.');
                }

                // Generate fresh snap token
                $snapToken = $midtransService->createSnapToken($lockedOrder);
                if (! $snapToken) {
                    throw new \InvalidArgumentException('Gagal menghubungi Payment Gateway. Silakan coba lagi.');
                }

                $lockedOrder->update([
                    'snap_token' => $snapToken,
                    'snap_retry_count' => $lockedOrder->snap_retry_count + 1,
                ]);
            });

            return back()->with('success', 'Silakan selesaikan pembayaran baru Anda.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
