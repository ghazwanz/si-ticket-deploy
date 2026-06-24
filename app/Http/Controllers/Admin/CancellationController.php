<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectCancellationRequest;
use App\Models\CancellationRequest;
use App\Services\Admin\CancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CancellationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly CancellationService $cancellationService
    ) {}

    /**
     * Display a listing of cancellation requests.
     */
    public function index(Request $request): View
    {
        $cancellations = $this->cancellationService->getAllRequests($request->all());

        return view('admin.cancellations.index', compact('cancellations'));
    }

    /**
     * Approve the specified cancellation request.
     */
    public function approve(CancellationRequest $cancellationRequest, Request $request): RedirectResponse
    {
        try {
            $this->cancellationService->approveCancellation($cancellationRequest, $request->user());

            return back()->with('success', 'Permohonan pembatalan acara berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Reject the specified cancellation request.
     */
    public function reject(CancellationRequest $cancellationRequest, RejectCancellationRequest $request): RedirectResponse
    {
        try {
            $this->cancellationService->rejectCancellation(
                $cancellationRequest,
                $request->user(),
                $request->validated('rejection_reason')
            );

            return back()->with('success', 'Permohonan pembatalan acara berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
