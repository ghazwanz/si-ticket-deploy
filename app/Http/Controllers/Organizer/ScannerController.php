<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScannerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        if (app()->environment('testing') && request()->route()->getName() === 'organizer.scanner.index') {
            logger()->info('Testing Session:', ['active' => session('active_event_id'), 'all' => session()->all()]);
        }
        $organizer = Auth::user();

        // Fetch all active/published events belonging to the organizer
        $events = Event::forOrganizer($organizer->id)
            ->whereIn('status', [
                EventStatus::Published,
                EventStatus::AwaitingCancellation,
                EventStatus::Cancelled,
                EventStatus::Completed,
            ])
            ->orderBy('event_date', 'desc')
            ->get();

        $activeEventId = session('active_event_id');

        // Auto-select event context if organizer has exactly one event
        if (! $activeEventId && $events->count() === 1) {
            $activeEventId = $events->first()->id;
            session(['active_event_id' => $activeEventId]);
        }

        $activeEvent = null;
        $stats = null;
        $recentScans = [];

        if ($activeEventId) {
            $activeEvent = Event::forOrganizer($organizer->id)->find($activeEventId);
            if ($activeEvent) {
                // Compute statistics
                $ticketsSold = OrderTicket::whereHas('ticketCategory', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Paid))
                    ->count();

                $ticketsScanned = OrderTicket::whereHas('ticketCategory', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->where('is_checked_in', true)
                    ->count();

                $merchSold = OrderMerchandise::whereHas('merchandiseVariant.item', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->whereHas('order', fn ($q) => $q->where('status', OrderStatus::Paid))
                    ->sum('quantity');

                $merchClaimed = OrderMerchandise::whereHas('merchandiseVariant.item', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->where('is_picked_up', true)
                    ->sum('quantity');

                $stats = [
                    'tickets_sold' => $ticketsSold,
                    'tickets_scanned' => $ticketsScanned,
                    'merch_sold' => $merchSold,
                    'merch_claimed' => $merchClaimed,
                ];

                // Get recent ticket check-ins
                $recentTickets = OrderTicket::with('ticketCategory')
                    ->whereHas('ticketCategory', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->where('is_checked_in', true)
                    ->orderBy('checked_in_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn ($t) => [
                        'type' => 'ticket',
                        'name' => $t->holder_name,
                        'detail' => $t->ticketCategory->name,
                        'time' => $t->checked_in_at ? $t->checked_in_at->translatedFormat('H:i:s') : '-',
                    ]);

                // Get recent merchandise claims
                $recentMerch = OrderMerchandise::with(['merchandiseVariant.item', 'order.user'])
                    ->whereHas('merchandiseVariant.item', fn ($q) => $q->where('event_id', $activeEvent->id))
                    ->where('is_picked_up', true)
                    ->orderBy('picked_up_at', 'desc')
                    ->limit(10)
                    ->get()
                    ->map(fn ($m) => [
                        'type' => 'merchandise',
                        'name' => $m->order->user->name ?? 'Pembeli',
                        'detail' => $m->merchandiseVariant->item->name.' ('.$m->merchandiseVariant->name.') × '.$m->quantity,
                        'time' => $m->picked_up_at ? $m->picked_up_at->translatedFormat('H:i:s') : '-',
                    ]);

                // Combine and sort
                $recentScans = $recentTickets->concat($recentMerch)
                    ->toArray();

                // Sort by time descending (using array sorting)
                usort($recentScans, function ($a, $b) {
                    return strcmp($b['time'], $a['time']);
                });

                $recentScans = array_slice($recentScans, 0, 10);
            } else {
                session()->forget('active_event_id');
            }
        }

        return view('organizer.scanner.index', compact('events', 'activeEvent', 'stats', 'recentScans'));
    }

    /**
     * Store the active scanning event in the session.
     */
    public function selectEvent(Request $request)
    {
        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $organizer = Auth::user();
        $event = Event::forOrganizer($organizer->id)->findOrFail($request->event_id);

        session(['active_event_id' => $event->id]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('organizer.scanner.index')->with('success', 'Event aktif berhasil dipilih.');
    }

    /**
     * Validate the scanned QR code and process check-in or redemption.
     */
    public function validateScan(Request $request): JsonResponse
    {
        $organizer = Auth::user();
        $activeEventId = session('active_event_id');

        if (! $activeEventId) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan pilih event terlebih dahulu.',
            ], 400);
        }

        $event = Event::forOrganizer($organizer->id)->findOrFail($activeEventId);

        // Cancelled Event Check (Task 5.6 & 5.7)
        if ($event->status === EventStatus::Cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'Event ini telah dibatalkan. Pemindaian tidak diizinkan.',
            ], 400);
        }

        $request->validate([
            'mode' => 'required|in:gate,merchandise',
            'token' => 'required|string',
            'confirm' => 'nullable|boolean',
        ]);

        $mode = $request->input('mode');
        $token = $request->input('token');
        $confirm = filter_var($request->input('confirm'), FILTER_VALIDATE_BOOLEAN);

        if ($mode === 'gate') {
            // Find ticket
            $ticket = OrderTicket::with(['ticketCategory.event', 'order'])
                ->where('qr_token', $token)
                ->first();

            if (! $ticket) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode QR tiket tidak ditemukan atau tidak valid.',
                ], 404);
            }

            // Event context validation
            if ($ticket->ticketCategory->event_id !== $event->id) {
                $ticketEvent = $ticket->ticketCategory->event;
                if ($ticketEvent && $ticketEvent->organizer_id === $organizer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tiket ini terdaftar untuk event lain milik Anda.',
                        'wrong_event' => true,
                        'target_event_id' => $ticketEvent->id,
                        'target_event_name' => $ticketEvent->name,
                    ], 400);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Tiket ini terdaftar untuk event lain.',
                ], 400);
            }

            // Payment verification
            if ($ticket->order->status !== OrderStatus::Paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran order tiket ini belum lunas.',
                ], 400);
            }

            // Double scan check
            if ($ticket->is_checked_in) {
                $time = $ticket->checked_in_at ? $ticket->checked_in_at->translatedFormat('d M H:i') : '-';

                return response()->json([
                    'success' => false,
                    'message' => "Tiket sudah digunakan pada {$time} WIB.",
                ], 400);
            }

            // Two-step confirmation check
            if (! $confirm) {
                return response()->json([
                    'success' => true,
                    'status' => 'pending_confirmation',
                    'name' => $ticket->holder_name,
                    'detail' => $ticket->ticketCategory->name,
                ]);
            }

            // Atomically check-in
            $ticket->update([
                'is_checked_in' => true,
                'checked_in_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'confirmed',
                'message' => 'Check-in berhasil!',
                'name' => $ticket->holder_name,
                'detail' => $ticket->ticketCategory->name,
                'time' => now()->translatedFormat('H:i:s'),
            ]);
        } else {
            // Merchandise mode
            $merch = OrderMerchandise::with(['merchandiseVariant.item.event', 'order.user'])
                ->where('merch_token', $token)
                ->first();

            if (! $merch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode QR merchandise tidak ditemukan atau tidak valid.',
                ], 404);
            }

            // Event context validation
            if ($merch->merchandiseVariant->item->event_id !== $event->id) {
                $merchEvent = $merch->merchandiseVariant->item->event;
                if ($merchEvent && $merchEvent->organizer_id === $organizer->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Merchandise ini terdaftar untuk event lain milik Anda.',
                        'wrong_event' => true,
                        'target_event_id' => $merchEvent->id,
                        'target_event_name' => $merchEvent->name,
                    ], 400);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Merchandise ini terdaftar untuk event lain.',
                ], 400);
            }

            // Payment verification
            if ($merch->order->status !== OrderStatus::Paid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran order merchandise ini belum lunas.',
                ], 400);
            }

            // Double claim check
            if ($merch->is_picked_up) {
                $time = $merch->picked_up_at ? $merch->picked_up_at->translatedFormat('d M H:i') : '-';

                return response()->json([
                    'success' => false,
                    'message' => "Merchandise sudah diambil pada {$time} WIB.",
                ], 400);
            }

            // Two-step confirmation check
            if (! $confirm) {
                return response()->json([
                    'success' => true,
                    'status' => 'pending_confirmation',
                    'name' => $merch->order->user->name ?? 'Pembeli',
                    'detail' => $merch->merchandiseVariant->item->name.' ('.$merch->merchandiseVariant->name.') × '.$merch->quantity,
                ]);
            }

            // Atomically redeem
            $merch->update([
                'is_picked_up' => true,
                'picked_up_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'status' => 'confirmed',
                'message' => 'Merchandise berhasil diklaim!',
                'name' => $merch->order->user->name ?? 'Pembeli',
                'detail' => $merch->merchandiseVariant->item->name.' ('.$merch->merchandiseVariant->name.') × '.$merch->quantity,
                'time' => now()->translatedFormat('H:i:s'),
            ]);
        }
    }
}
