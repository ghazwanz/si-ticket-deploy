<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use App\Services\User\CheckoutService;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    /**
     * Manually sync order payment status with Midtrans.
     */
    public function sync(
        Order $order,
        MidtransService $midtransService,
        CheckoutService $checkoutService
    ): RedirectResponse {
        $statusData = $midtransService->getTransactionStatus($order->midtrans_order_id);

        if ($statusData === null) {
            return back()->with('error', 'Gagal mendapatkan status transaksi dari Midtrans.');
        }

        $transactionStatus = $statusData['transaction_status'] ?? '';
        $paymentType = $statusData['payment_type'] ?? '';
        $transactionId = $statusData['transaction_id'] ?? '';

        $oldStatus = $order->status->value ?? $order->status;

        $checkoutService->updateOrderStatus($order, $transactionStatus, $paymentType, $transactionId);

        return back()->with('success', "Status pesanan '{$order->midtrans_order_id}' berhasil disinkronkan.");
    }
}
