<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Order;
use App\Models\Payout;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Platform Core Statistics
        $stats = [
            'total_pengguna' => User::count(),
            'event_review' => Event::where('status', EventStatus::AwaitingApproval)->count(),
            'event_aktif' => Event::where('status', 'published')->count(),
            'eo_pending' => User::where('role', UserRole::Organizer)->where('is_active', false)->count(),
        ];

        // 1. Calculate Growth for Total Pengguna (last 30 days vs preceding 30 days)
        $usersThisMonth = User::where('created_at', '>=', now()->subDays(30))->count();
        $usersLastMonth = User::where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        if ($usersLastMonth > 0) {
            $userGrowth = (($usersThisMonth - $usersLastMonth) / $usersLastMonth) * 100;
        } else {
            $userGrowth = $usersThisMonth > 0 ? 100 : 0;
        }

        $stats['user_growth_text'] = $userGrowth >= 0
            ? '+'.round($userGrowth).'% vs bulan lalu'
            : round($userGrowth).'% vs bulan lalu';
        $stats['user_growth_color'] = $userGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500';

        // 2. Calculate Growth for Event Aktif (last 30 days vs preceding 30 days)
        $eventsThisMonth = Event::where('status', 'published')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $eventsLastMonth = Event::where('status', 'published')
            ->where('created_at', '>=', now()->subDays(60))
            ->where('created_at', '<', now()->subDays(30))
            ->count();

        if ($eventsLastMonth > 0) {
            $eventGrowth = (($eventsThisMonth - $eventsLastMonth) / $eventsLastMonth) * 100;
        } else {
            $eventGrowth = $eventsThisMonth > 0 ? 100 : 0;
        }

        $stats['event_growth_text'] = $eventGrowth >= 0
            ? '+'.round($eventGrowth).'% vs bulan lalu'
            : round($eventGrowth).'% vs bulan lalu';
        $stats['event_growth_color'] = $eventGrowth >= 0 ? 'text-emerald-500' : 'text-rose-500';

        // Analytics: Last 30 days transactions
        $days = collect(range(29, 0))->map(fn ($i) => now()->subDays($i)->format('Y-m-d'));

        $transactions = Order::query()
            ->where('status', 'paid')
            ->where('paid_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(paid_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $analytics = [
            'labels' => $days->map(fn ($d) => Carbon::parse($d)->format('d M'))->toArray(),
            'revenue' => $days->map(fn ($date) => $transactions->has($date) ? (int) $transactions[$date]->total : 0)->toArray(),
            'volume' => $days->map(fn ($date) => $transactions->has($date) ? (int) $transactions[$date]->count : 0)->toArray(),
        ];

        // Dynamic Activities
        $latestOrganizers = User::where('role', UserRole::Organizer)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($user) => [
                'icon' => 'user',
                'color' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
                'action' => 'Registrasi EO Baru: '.$user->name,
                'user' => 'System',
                'timestamp' => $user->created_at,
            ]);

        $latestEvents = Event::with('organizer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($event) => [
                'icon' => 'calendar',
                'color' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                'action' => 'Pembuatan Event Baru: "'.$event->name.'"',
                'user' => $event->organizer?->name ?? 'System',
                'timestamp' => $event->created_at,
            ]);

        $latestOrders = Order::with(['user', 'event'])
            ->where('status', OrderStatus::Paid)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($order) => [
                'icon' => 'shopping-cart',
                'color' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                'action' => 'Pesanan Tiket Lunas: "'.($order->event?->name ?? 'Event').'"',
                'user' => $order->user?->name ?? 'System',
                'timestamp' => $order->paid_at ?? $order->created_at,
            ]);

        $latestPayouts = Payout::with(['event', 'reviewer', 'disburser'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($payout) => [
                'icon' => 'banknotes',
                'color' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                'action' => 'Pembaruan Payout: "'.($payout->event?->name ?? 'Event').'" ('.$payout->status->label().')',
                'user' => $payout->reviewer?->name ?? $payout->disburser?->name ?? 'System',
                'timestamp' => $payout->updated_at ?? $payout->created_at,
            ]);

        $logs = collect()
            ->concat($latestOrganizers)
            ->concat($latestEvents)
            ->concat($latestOrders)
            ->concat($latestPayouts)
            ->sortByDesc('timestamp')
            ->take(5)
            ->map(function ($log) {
                $log['time'] = Carbon::parse($log['timestamp'])->diffForHumans();

                return $log;
            })
            ->toArray();

        // Category Distribution
        $categories = EventCategory::withCount('events')->get();
        $totalEvents = $categories->sum('events_count');
        $colors = ['bg-violet-600', 'bg-blue-500', 'bg-emerald-500'];

        $distribusi = $categories->sortByDesc('events_count')
            ->values()
            ->map(function ($cat, $index) use ($totalEvents, $colors) {
                return [
                    'label' => $cat->name,
                    'pct' => $totalEvents > 0 ? round(($cat->events_count / $totalEvents) * 100) : 0,
                    'color' => $colors[$index % count($colors)],
                ];
            })
            ->take(3)
            ->toArray();

        return view('admin.dashboard', compact('stats', 'logs', 'distribusi', 'analytics'));
    }
}
