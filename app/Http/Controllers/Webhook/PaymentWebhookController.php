<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\User\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Handle Midtrans callback.
     */
    public function handleCallback(Request $request): Response
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Callback Received', ['payload' => $payload]);

        // 1. Verify source IP ranges (Task 4.5)
        $ip = $request->ip();
        $allowedIps = [
            '103.208.23.',  // Midtrans sandbox/production
            '103.56.14.',   // Midtrans production
            '103.78.23.',
            '34.101.',      // GCP Jakarta (Midtrans sandbox origin)
            '127.0.0.1',    // Testing localhost
            '::1',          // Testing localhost IPv6
        ];

        $ipAllowed = false;
        foreach ($allowedIps as $allowedIp) {
            if (str_starts_with($ip, $allowedIp)) {
                $ipAllowed = true;
                break;
            }
        }

        if (! $ipAllowed) {
            Log::warning('Midtrans Webhook blocked: unauthorized source IP address', ['ip' => $ip]);

            return response('Unauthorized IP address', 403);
        }

        // 2. Verify Signature Key (Task 4.4)
        if (! $this->midtransService->verifyWebhookSignature($payload)) {
            Log::warning('Midtrans Webhook blocked: invalid signature key');

            return response('Invalid Signature', 400);
        }

        // 3. Resolve Order
        $midtransOrderId = $payload['order_id'] ?? '';
        $order = Order::where('midtrans_order_id', $midtransOrderId)->first();
        if (! $order) {
            // Fallback to primary key UUID
            $order = Order::find($midtransOrderId);
        }

        if (! $order) {
            Log::error('Midtrans Webhook: order record not found', ['order_id' => $midtransOrderId]);

            return response('Order not found', 404);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $paymentType = $payload['payment_type'] ?? '';
        $transactionId = $payload['transaction_id'] ?? '';

        $this->checkoutService->updateOrderStatus($order, $transactionStatus, $paymentType, $transactionId);

        return response('OK');
    }
}
