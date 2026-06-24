<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Admin\EventAnalyticsService;
use App\Services\Admin\EventService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected EventAnalyticsService $analyticsService
    ) {}

    public function index(Request $request): View
    {
        $events = $this->eventService->getPaginatedEvents($request->all());

        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        $event->load([
            'category',
            'organizer.organizerProfile',
            'ticketCategories',
            'merchandiseItems.variants',
            'latestCancellationRequest',
        ]);

        $intelligence = $this->analyticsService->getEventIntelligence($event);

        return view('admin.events.show', compact('event', 'intelligence'));
    }

    public function updateStatus(Request $request, Event $event)
    {
        $allowedStatuses = ['draft', 'published', 'completed', 'cancelled', 'reject', 'awaiting_approval'];

        // Enforce transition rules for published events
        if ($event->status === EventStatus::Published) {
            $allowedStatuses = ['completed', 'cancelled'];
        }

        $request->validate([
            'status' => 'required|in:'.implode(',', $allowedStatuses),
            'rejection_message' => 'required_if:status,reject,cancelled|nullable|string',
        ]);

        $this->eventService->updateEventStatus($event, $request->status, $request->rejection_message);

        return back()->with('status', 'Status event berhasil diperbarui.');
    }

    public function toggleFeatured(Event $event)
    {
        if ($event->status !== EventStatus::Published) {
            return back()->withErrors(['error' => 'Hanya acara yang telah diterbitkan yang dapat ditandai sebagai Unggulan.']);
        }

        $event->update([
            'is_featured' => ! $event->is_featured,
        ]);

        $message = $event->is_featured
            ? 'Acara berhasil ditandai sebagai Unggulan.'
            : 'Acara berhasil dihapus dari Unggulan.';

        return back()->with('status', $message);
    }
}
