<?php

declare(strict_types=1);

namespace App\Services\Organizer;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class EventService
{
    /**
     * Get a paginated list of events for an organizer with filters, search, and sorting.
     */
    public function getPaginatedEventsForOrganizer(string $organizerId, array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $status = $filters['status'] ?? null;
        $category = $filters['category'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $sort = $filters['sort'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        $allowedSorts = ['name', 'event_date', 'created_at'];

        return Event::query()
            ->forOrganizer($organizerId)
            ->with('category')
            ->withSum('ticketCategories as total_sold', 'sold_count')
            ->withSum('ticketCategories as total_quota', 'quota')
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('venue_name', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when($status && $status !== 'all', function (Builder $query) use ($status) {
                $query->where('status', EventStatus::tryFrom($status));
            })
            ->when($category && $category !== 'all', function (Builder $query) use ($category) {
                $query->where('category_id', $category);
            })
            ->when($dateFrom, function (Builder $query) use ($dateFrom) {
                $query->where('event_date', '>=', $dateFrom);
            })
            ->when($dateTo, function (Builder $query) use ($dateTo) {
                $query->where('event_date', '<=', $dateTo);
            })
            ->when($sort === 'occupancy', function (Builder $query) use ($order) {
                $query->orderByRaw(
                    'CASE WHEN (SELECT SUM(quota) FROM ticket_categories WHERE ticket_categories.event_id = events.id) > 0
                     THEN (SELECT SUM(sold_count) FROM ticket_categories WHERE ticket_categories.event_id = events.id) * 1.0
                          / (SELECT SUM(quota) FROM ticket_categories WHERE ticket_categories.event_id = events.id)
                     ELSE 0 END '.($order === 'asc' ? 'asc' : 'desc')
                );
            })
            ->when($sort !== 'occupancy' && in_array($sort, $allowedSorts), function (Builder $query) use ($sort, $order) {
                $query->orderBy($sort, $order === 'asc' ? 'asc' : 'desc');
            })
            ->when(! $sort, function (Builder $query) {
                $query->latest();
            })
            ->paginate(10)
            ->withQueryString();
    }
}
