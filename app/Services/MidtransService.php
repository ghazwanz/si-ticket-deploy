<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;

    private string $clientKey;

    private bool $isProduction;

    private string $snapUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key', '');
        $this->clientKey = config('services.midtrans.client_key', '');
        $this->isProduction = (bool) config('services.midtrans.is_production', false);
        $this->snapUrl = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    /**
     * Create Snap Token for an Order.
     */
    public function createSnapToken(Order $order): ?string
    {
        $order->load(['tickets.ticketCategory', 'merchandise.merchandiseVariant.item']);

        $itemDetails = [];

        // Add tickets to items detail
        foreach ($order->tickets as $ticket) {
            $itemDetails[] = [
                'id' => $ticket->ticket_category_id,
                'price' => $ticket->unit_price,
                'quantity' => 1,
                'name' => 'Tiket: '.$ticket->ticketCategory->name,
            ];
        }

        // Add merchandise to items detail
        foreach ($order->merchandise as $merch) {
            $itemDetails[] = [
                'id' => $merch->merchandise_variant_id,
                'price' => $merch->unit_price,
                'quantity' => $merch->quantity,
                'name' => 'Merch: '.$merch->merchandiseItem->name.' ('.$merch->merchandiseVariant->variant_value.')',
            ];
        }

        // Calculate and add service fee / service cost if there's any discrepancy
        $itemsTotal = collect($itemDetails)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $serviceFee = 15000;
        $tax = (int) round($itemsTotal * 0.1);
        $expectedTotal = $itemsTotal + $serviceFee + $tax;

        // If the calculated total matches the order total, add tax and service fee to item details
        if ($expectedTotal === $order->total_amount) {
            $itemDetails[] = [
                'id' => 'service-fee',
                'price' => $serviceFee,
                'quantity' => 1,
                'name' => 'Biaya Layanan',
            ];
            $itemDetails[] = [
                'id' => 'tax',
                'price' => $tax,
                'quantity' => 1,
                'name' => 'Pajak (10%)',
            ];
        } else {
            // Fallback: if there's a custom calculation, just send a single line item representing total
            $itemDetails = [
                [
                    'id' => 'total-order',
                    'price' => $order->total_amount,
                    'quantity' => 1,
                    'name' => 'Pembayaran Acara: '.$order->event->name,
                ],
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->midtrans_order_id ?? $order->id,
                'gross_amount' => $order->total_amount,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->user->name,
                'email' => $order->user->email,
                // Fallback phone number if not present in profile or order
                'phone' => '08123456789',
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minutes',
                'duration' => 15,
            ],
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($this->snapUrl, $payload);

            if ($response->successful()) {
                return $response->json()['token'] ?? null;
            }

            Log::error('Midtrans Snap API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap HTTP Exception', [
                'message' => $e->getMessage(),
                'order_id' => $order->id,
            ]);
        }

        return null;
    }

    /**
     * Get the status of an order from Midtrans.
     */
    public function getTransactionStatus(string $orderId): ?array
    {
        $url = $this->isProduction
            ? "https://api.midtrans.com/v2/{$orderId}/status"
            : "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Midtrans Status API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId,
            ]);
        } catch (\Exception $e) {
            Log::error('Midtrans Status HTTP Exception', [
                'message' => $e->getMessage(),
                'order_id' => $orderId,
            ]);
        }

        return null;
    }

    /**
     * Verify Midtrans Callback Webhook Signature.
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $signatureKey) {
            return false;
        }

        // Midtrans signature formula: SHA512(order_id + status_code + gross_amount + ServerKey)
        $expectedSignature = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
