<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Event;
use App\Models\OrderMerchandise;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EventAnalyticsService
{
    /**
     * Get comprehensive analytics for a specific event.
     */
    public function getEventIntelligence(Event $event): array
    {
        return [
            'revenue' => $this->getRevenueStats($event),
            'ticketing' => $this->getTicketingStats($event),
            'merchandise' => $this->getMerchandiseStats($event),
            'activity' => $this->getRecentActivity($event),
        ];
    }

    /**
     * Calculate revenue-related statistics.
     */
    public function getRevenueStats(Event $event): array
    {
        $totalRevenue = $event->orders()
            ->where('status', 'paid')
            ->sum('total_amount');

        // Assuming 10% platform fee for demonstration
        $platformFee = $totalRevenue * 0.10;
        $netPayout = $totalRevenue - $platformFee;

        return [
            'total_gross' => $totalRevenue,
            'platform_fee' => $platformFee,
            'payout_projection' => $netPayout,
            'formatted_gross' => 'Rp '.number_format((float) $totalRevenue, 0, ',', '.'),
        ];
    }

    /**
     * Aggregate ticketing performance.
     */
    public function getTicketingStats(Event $event): array
    {
        $categories = $event->ticketCategories;

        $totalQuota = $categories->sum('quota');
        $totalSold = $categories->sum('sold_count');
        $fillRate = $totalQuota > 0 ? ($totalSold / $totalQuota) * 100 : 0;

        return [
            'total_sold' => $totalSold,
            'total_quota' => $totalQuota,
            'fill_rate' => round($fillRate, 1),
            'categories' => $categories->map(fn ($cat) => [
                'name' => $cat->name,
                'price' => $cat->price,
                'sold' => $cat->sold_count,
                'quota' => $cat->quota,
                'is_sold_out' => $cat->sold_count >= $cat->quota,
            ]),
        ];
    }

    /**
     * Aggregate merchandise performance.
     */
    public function getMerchandiseStats(Event $event): array
    {
        $items = $event->merchandiseItems()->with('variants')->get();

        $paidOrdersIds = $event->orders()->where('status', 'paid')->pluck('id');

        $soldPerVariant = OrderMerchandise::whereIn('order_id', $paidOrdersIds)
            ->select('merchandise_variant_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('merchandise_variant_id')
            ->pluck('total_sold', 'merchandise_variant_id');

        $totalMerchSold = $soldPerVariant->sum();

        return [
            'total_items' => $items->count(),
            'total_sold' => (int) $totalMerchSold,
            'items' => $items->map(function ($item) use ($soldPerVariant) {
                $totalStock = $item->variants->sum('stock');
                $sold = $item->variants->sum(fn ($v) => $soldPerVariant[$v->id] ?? 0);

                return [
                    'name' => $item->name,
                    'base_price' => $item->base_price,
                    'total_stock' => $totalStock,
                    'sold' => (int) $sold,
                    'is_available' => $item->is_available,
                ];
            }),
        ];
    }

    /**
     * Get recent orders and events.
     */
    public function getRecentActivity(Event $event): Collection
    {
        return $event->orders()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
    }
}
