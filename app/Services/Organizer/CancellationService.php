<?php

declare(strict_types=1);

namespace App\Services\Organizer;

use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\User;
use App\Notifications\CancellationRequestedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CancellationService
{
    /**
     * Check if the event's start date and time has passed.
     */
    public function isHardCutoffPassed(Event $event): bool
    {
        $eventDateTime = Carbon::parse($event->event_date->format('Y-m-d').' '.$event->start_time);

        return now()->greaterThanOrEqualTo($eventDateTime);
    }

    /**
     * Organizer self-cancels an event (Tier 1). Only allowed when no tickets are sold.
     */
    public function cancelEvent(Event $event): void
    {
        if ($this->isHardCutoffPassed($event)) {
            throw new \RuntimeException('Pembatalan tidak tersedia setelah acara dimulai.');
        }

        if ($event->hasSales()) {
            throw new \RuntimeException('Acara tidak dapat dibatalkan secara langsung karena sudah ada transaksi tiket atau merchandise.');
        }

        $event->update([
            'status' => 'cancelled',
        ]);
    }

    /**
     * Organizer requests cancellation of an event (Tier 2). Used when tickets are sold.
     */
    public function requestCancellation(Event $event, string $reason, User $organizer): CancellationRequest
    {
        if ($this->isHardCutoffPassed($event)) {
            throw new \RuntimeException('Pembatalan tidak tersedia setelah acara dimulai.');
        }

        if (! $event->hasSales()) {
            throw new \RuntimeException('Gunakan pembatalan langsung karena belum ada transaksi tiket atau merchandise.');
        }

        $request = null;
        DB::transaction(function () use ($event, $reason, $organizer, &$request): void {
            $request = CancellationRequest::create([
                'id' => Str::uuid(),
                'event_id' => $event->id,
                'requested_by' => $organizer->id,
                'reason' => $reason,
                'status' => 'pending',
                'affected_tickets_count' => $event->ticketCategories->sum('sold_count'),
            ]);

            $event->update([
                'status' => 'awaiting_cancellation',
            ]);
        });

        // Notify admins
        $admins = User::where('role', 'admin')->get();
        Notification::send($admins, new CancellationRequestedNotification($request));

        return $request;
    }
}
