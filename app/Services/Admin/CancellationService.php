<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\PayoutStatus;
use App\Enums\PayoutType;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\OrderTicket;
use App\Models\Payout;
use App\Models\User;
use App\Notifications\CancellationApprovedNotification;
use App\Notifications\CancellationRejectedNotification;
use App\Notifications\EventCancelledNotification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CancellationService
{
    /**
     * Get a paginated list of all cancellation requests.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getAllRequests(array $filters = []): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return CancellationRequest::query()
            ->with(['event.organizer.organizerProfile', 'requestedBy', 'reviewedBy'])
            ->when($status && $status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('event', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%");
                });
            })
            ->when(in_array($sort, ['created_at', 'status']), function ($query) use ($sort, $order) {
                $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            }, function ($query) {
                $query->latest();
            })
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Get a paginated list of pending cancellation requests.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPendingRequests(array $filters = []): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        $paginator = CancellationRequest::query()
            ->where('status', 'pending')
            ->with(['event.organizer.organizerProfile', 'requestedBy'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('event', function ($eq) use ($search) {
                    $eq->where('name', 'like', "%{$search}%");
                });
            })
            ->when(in_array($sort, ['created_at']), function ($query) use ($sort, $order) {
                return $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(15)
            ->withQueryString();

        $paginator->getCollection()->each(function (CancellationRequest $request): void {
            $request->setAttribute(
                'ticket_holders_count',
                OrderTicket::query()->whereHas('order', function ($query) use ($request): void {
                    $query->where('event_id', $request->event_id)->where('status', 'paid');
                })->count()
            );
        });

        return $paginator;
    }

    /**
     * Approve a cancellation request, mark the event as cancelled, void payouts, and notify users.
     */
    public function approveCancellation(CancellationRequest $request, User $admin): CancellationRequest
    {
        $event = $request->event;

        if ($this->isHardCutoffPassed($event)) {
            throw new \RuntimeException('Batas waktu pembatalan acara telah terlampaui.');
        }

        DB::transaction(function () use ($request, $event, $admin): void {
            $request->update([
                'status' => 'approved',
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            $hasCompletedAdvances = Payout::query()
                ->where('event_id', $event->id)
                ->where('payout_type', PayoutType::Advance)
                ->where('status', PayoutStatus::Completed)
                ->exists();

            if ($hasCompletedAdvances) {
                $event->update([
                    'status' => 'cancelled',
                    'manual_settlement_required' => true,
                ]);

                Payout::query()
                    ->where('event_id', $event->id)
                    ->where('status', PayoutStatus::Completed)
                    ->update([
                        'manual_settlement_required' => true,
                    ]);
            } else {
                $event->update([
                    'status' => 'cancelled',
                ]);
            }

            Payout::query()
                ->where('event_id', $event->id)
                ->whereIn('status', [PayoutStatus::Pending, PayoutStatus::Processing])
                ->update([
                    'status' => PayoutStatus::Voided,
                ]);
        });

        $ticketHolders = User::query()->whereHas('orders', function ($query) use ($event): void {
            $query->where('event_id', $event->id)->where('status', 'paid');
        })->get();

        if ($ticketHolders->isNotEmpty()) {
            Notification::send(
                $ticketHolders,
                new EventCancelledNotification($event)
            );
        }

        $organizer = $event->organizer;
        $organizer->notify(new CancellationApprovedNotification($request));

        return $request;
    }

    /**
     * Reject a cancellation request, revert the event status to published, and notify the organizer.
     */
    public function rejectCancellation(CancellationRequest $request, User $admin, string $reason): CancellationRequest
    {
        $event = $request->event;

        if ($this->isHardCutoffPassed($event)) {
            throw new \RuntimeException('Batas waktu pembatalan acara telah terlampaui.');
        }

        DB::transaction(function () use ($request, $event, $admin, $reason): void {
            $request->update([
                'status' => 'rejected',
                'reviewed_by' => $admin->id,
                'rejection_reason' => $reason,
                'reviewed_at' => now(),
            ]);

            $event->update([
                'status' => 'published',
            ]);
        });

        $organizer = $event->organizer;
        $organizer->notify(new CancellationRejectedNotification($request));

        return $request;
    }

    /**
     * Check if the event's start date and time has passed.
     */
    public function isHardCutoffPassed(Event $event): bool
    {
        $eventDateTime = Carbon::parse($event->event_date->format('Y-m-d').' '.$event->start_time);

        return now()->greaterThanOrEqualTo($eventDateTime);
    }
}
