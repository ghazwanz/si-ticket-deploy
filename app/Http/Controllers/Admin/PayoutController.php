<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveAdvancePayoutRequest;
use App\Http\Requests\Admin\DisbursePayoutRequest;
use App\Http\Requests\Admin\RejectAdvancePayoutRequest;
use App\Models\Event;
use App\Models\Order;
use App\Models\Payout;
use App\Services\Admin\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $payouts = $this->payoutService->getPaginatedPayouts($request->all());

        return view('admin.payouts.index', compact('payouts', 'status'));
    }

    public function show(Payout $payout): View
    {
        $payout->load(['event.payouts', 'organizer.organizerProfile', 'reviewer', 'disburser']);

        return view('admin.payouts.show', compact('payout'));
    }

    public function initializeFinalPayout(Event $event): RedirectResponse
    {
        try {
            $this->payoutService->initializeFinalPayout($event);

            return back()->with('success', 'Final payout initialized successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Payout $payout, Request $request): RedirectResponse
    {
        try {
            $this->payoutService->approvePayout($payout, $request->user());

            return back()->with('success', 'Payout approved for disbursement.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approveAdvance(Payout $payout, ApproveAdvancePayoutRequest $request): RedirectResponse
    {
        try {
            $this->payoutService->approveAdvancePayout($payout, $request->user(), $request->validated()['approved_amount']);

            return back()->with('success', 'Pembayaran uang muka disetujui untuk pencairan.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectAdvance(Payout $payout, RejectAdvancePayoutRequest $request): RedirectResponse
    {
        try {
            $this->payoutService->rejectAdvancePayout($payout, $request->user(), $request->validated()['rejection_reason']);

            return back()->with('success', 'Advance payout request rejected.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disburse(Payout $payout, DisbursePayoutRequest $request): RedirectResponse
    {
        try {
            $this->payoutService->disbursePayout(
                $payout,
                $request->user(),
                $request->file('proof_photo'),
                $request->validated()['transfer_reference']
            );

            return back()->with('success', 'Payout berhasil dicairkan.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function auditLogs(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $sort = $request->query('sort', 'created_at');
        $orderDir = $request->query('order_dir', 'desc');

        if (! in_array($sort, ['created_at', 'total_amount'])) {
            $sort = 'created_at';
        }
        if (! in_array($orderDir, ['asc', 'desc'])) {
            $orderDir = 'desc';
        }

        $query = Order::with(['user', 'event', 'tickets.ticketCategory', 'merchandise.merchandiseVariant', 'merchandise.merchandiseItem']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('midtrans_order_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('event', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy($sort, $orderDir)
            ->paginate(20)
            ->withQueryString();

        $statuses = OrderStatus::cases();

        return view('admin.payouts.audit-logs', compact(
            'orders',
            'search',
            'status',
            'statuses',
            'sort',
            'orderDir'
        ));
    }
}
