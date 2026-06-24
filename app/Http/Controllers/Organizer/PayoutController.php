<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organizer;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\RequestPayoutRequest;
use App\Models\Event;
use App\Services\Organizer\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

final class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService
    ) {}

    /**
     * Display a listing of organizer's events with their payout details.
     */
    public function index(Request $request): View
    {
        $query = Event::with(['payouts', 'finalPayout'])
            ->where('organizer_id', $request->user()->id);

        // Search by Event Name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'date_desc':
                $query->orderBy('event_date', 'desc');
                break;
            case 'date_asc':
                $query->orderBy('event_date', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $events = $query->paginate(10)->withQueryString();

        // Map status for display
        $events->getCollection()->transform(function ($event) {
            $event->payout_summary = $this->payoutService->getAdvanceSummary($event);

            return $event;
        });

        $eventsWithMissingBank = Event::where('organizer_id', $request->user()->id)
            ->whereHas('finalPayout', function ($query) {
                $query->where('missing_bank_details', true);
            })
            ->get();

        return view('organizer.payouts.index', compact('events', 'eventsWithMissingBank'));
    }

    /**
     * Show detailed payout details and advance request form for an event.
     */
    public function show(Event $event, Request $request): View
    {
        if ($event->organizer_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        $summary = $this->payoutService->getAdvanceSummary($event);
        $payouts = $event->payouts()->latest()->get();

        return view('organizer.payouts.show', compact('event', 'summary', 'payouts'));
    }

    /**
     * Submit request for a payout (advance or final).
     */
    public function requestPayout(Event $event, RequestPayoutRequest $request): RedirectResponse
    {
        if ($event->organizer_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $this->payoutService->requestPayout($event, $request->validated());

            $message = ($event->status === EventStatus::Completed || $event->status === 'completed')
                ? 'Pengajuan pembayaran akhir (final payout) berhasil dikirim.'
                : 'Pengajuan pembayaran awal (advance payout) berhasil dikirim.';

            return redirect()->route('organizer.payouts.show', $event)
                ->with('success', $message);
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
