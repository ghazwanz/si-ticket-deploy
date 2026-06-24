<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    /**
     * Get a paginated list of events with filters, search, and sorting.
     */
    public function getPaginatedEvents(array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? null;
        $search = $filters['search'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return Event::with(['organizer', 'category', 'latestCancellationRequest'])
            ->when($status && $status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($search, function ($query) use ($search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($sort, ['name', 'event_date', 'status', 'city']), function ($query) use ($sort, $order) {
                return $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            }, function ($query) {
                return $query->latest();
            })
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Update the status of an event.
     */
    public function updateEventStatus(Event $event, string $status, ?string $rejectionMessage = null): Event
    {
        // Guard against invalid transitions from published status
        if ($event->status === EventStatus::Published && ! in_array($status, ['completed', 'cancelled'])) {
            throw new \InvalidArgumentException('Published events can only be moved to completed or cancelled.');
        }

        // Guard against cancelling a published event that has active sales/transactions
        if ($status === 'cancelled' && $event->status === EventStatus::Published && $event->hasSales()) {
            throw new \InvalidArgumentException('Tidak dapat membatalkan acara yang sudah memiliki transaksi penjualan.');
        }

        if ($status === 'reject') {
            $event->update([
                'status' => EventStatus::Draft,
                'rejection_message' => $rejectionMessage,
                'is_featured' => false,
            ]);
        } else {
            $updateData = ['status' => $status];
            if (in_array($status, ['published', 'completed', 'awaiting_approval'])) {
                $updateData['rejection_message'] = null;
            } elseif ($status === 'cancelled') {
                $updateData['rejection_message'] = $rejectionMessage;
            }
            if ($status !== 'published') {
                $updateData['is_featured'] = false;
            }
            $event->update($updateData);
        }

        return $event;
    }
}
