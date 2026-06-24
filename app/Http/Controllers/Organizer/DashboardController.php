<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderTicket;
use App\Models\TicketCategory;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $organizerId = auth()->id();
        $eventIds = Event::query()
            ->where('organizer_id', $organizerId)
            ->pluck('id');

        $paidOrders = Order::query()
            ->whereIn('event_id', $eventIds)
            ->where('status', 'paid');

        $totalRevenue = (clone $paidOrders)->sum('total_amount');
        $soldTickets = TicketCategory::query()
            ->whereIn('event_id', $eventIds)
            ->sum('sold_count');

        $stats = [
            'total_penjualan' => $totalRevenue,
            'tiket_terjual' => $soldTickets,
            'acara_aktif' => Event::query()
                ->where('organizer_id', $organizerId)
                ->where('status', 'published')
                ->count(),
            'perlu_ditinjau' => Event::query()
                ->where('organizer_id', $organizerId)
                ->whereIn('status', ['draft', 'awaiting_approval'])
                ->count(),
            'total_checkin' => OrderTicket::query()
                ->whereHas('order', function ($query) use ($eventIds): void {
                    $query->whereIn('event_id', $eventIds);
                })
                ->where('is_checked_in', true)
                ->count(),
            'siap_cair' => Event::query()
                ->where('organizer_id', $organizerId)
                ->where('status', 'completed')
                ->count(),
        ];

        $days = collect(range(29, 0))->map(fn (int $day): string => now()->subDays($day)->format('Y-m-d'));

        $transactions = (clone $paidOrders)
            ->where('paid_at', '>=', now()->subDays(30)->startOfDay())
            ->selectRaw('DATE(paid_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $tickets = OrderTicket::query()
            ->whereHas('order', function ($query) use ($eventIds): void {
                $query->whereIn('event_id', $eventIds)
                    ->where('status', 'paid')
                    ->where('paid_at', '>=', now()->subDays(30)->startOfDay());
            })
            ->join('orders', 'order_tickets.order_id', '=', 'orders.id')
            ->selectRaw('DATE(orders.paid_at) as date, COUNT(order_tickets.id) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $analytics = [
            'labels' => $days->map(fn (string $date): string => Carbon::parse($date)->translatedFormat('d M'))->toArray(),
            'revenue' => $days->map(fn (string $date): int => $transactions->has($date) ? (int) $transactions[$date]->total : 0)->toArray(),
            'volume' => $days->map(fn (string $date): int => $transactions->has($date) ? (int) $transactions[$date]->count : 0)->toArray(),
            'tickets' => $days->map(fn (string $date): int => $tickets->has($date) ? (int) $tickets[$date]->total : 0)->toArray(),
        ];

        $ticketTotal = max($soldTickets, 1);
        $tones = ['bg-violet-600', 'bg-fuchsia-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500'];

        $ticketDistribution = TicketCategory::query()
            ->whereIn('event_id', $eventIds)
            ->where('sold_count', '>', 0)
            ->orderByDesc('sold_count')
            ->limit(5)
            ->get()
            ->values()
            ->map(fn (TicketCategory $category, int $index): array => [
                'label' => $category->name,
                'sold' => $category->sold_count,
                'percentage' => round(($category->sold_count / $ticketTotal) * 100),
                'color' => $tones[$index] ?? 'bg-slate-500',
            ])
            ->toArray();

        $recentEvents = Event::query()
            ->with(['category', 'ticketCategories'])
            ->where('organizer_id', $organizerId)
            ->latest()
            ->limit(5)
            ->get();

        return view('organizer.dashboard', compact('analytics', 'recentEvents', 'stats', 'ticketDistribution'));
    }
}
