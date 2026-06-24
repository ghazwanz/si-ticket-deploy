@extends('layouts.organizer')
@section('title', 'Detail Acara - ' . $event->name)
@section('page-title', 'Detail Acara')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $event->name }}</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-1">
                {{ $event->event_date->translatedFormat('d F Y') }} • {{ $event->venue_name }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            @php
                $statusColors = [
                    'published' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                    'draft' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400',
                    'completed' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                    'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                    'awaiting_approval' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                ];
                $color = $statusColors[$event->status->value] ?? $statusColors['draft'];
            @endphp
            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-widest {{ $color }}">
                {{ $event->status->label() }}
            </span>
            
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-700 dark:hover:bg-slate-800 transition-all">
                    <x-heroicon-o-calendar class="h-4 w-4 text-slate-400" />
                    @if($filter === '7')
                        7 Hari Terakhir
                    @elseif($filter === 'all')
                        Semua Waktu
                    @else
                        30 Hari Terakhir
                    @endif
                    <x-heroicon-o-chevron-down class="h-4 w-4 text-slate-400" />
                </button>
                
                <div x-show="open" x-transition.opacity class="absolute right-0 z-10 mt-2 w-48 rounded-xl bg-white p-2 shadow-lg ring-1 ring-slate-900/5 dark:bg-slate-900 dark:ring-slate-800" style="display: none;">
                    <a href="{{ route('organizer.events.show', ['event' => $event->id, 'filter' => '7']) }}" data-link class="block rounded-lg px-4 py-2 text-sm font-semibold {{ $filter === '7' ? 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50' }}">
                        7 Hari Terakhir
                    </a>
                    <a href="{{ route('organizer.events.show', ['event' => $event->id, 'filter' => '30']) }}" data-link class="block rounded-lg px-4 py-2 text-sm font-semibold {{ $filter === '30' ? 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50' }}">
                        30 Hari Terakhir
                    </a>
                    <a href="{{ route('organizer.events.show', ['event' => $event->id, 'filter' => 'all']) }}" data-link class="block rounded-lg px-4 py-2 text-sm font-semibold {{ $filter === 'all' ? 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50' }}">
                        Semua Waktu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-organizer.stat-card label="Total Pendapatan" value="Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}" meta="Berdasarkan periode filter" icon="banknotes" tone="emerald" />
        <x-organizer.stat-card label="Tiket Terjual" value="{{ number_format($stats['ticket_sold'], 0, ',', '.') }}" meta="Total dari semua kategori" icon="ticket" tone="violet" />
        <x-organizer.stat-card label="Suvenir Terjual" value="{{ number_format($stats['merch_sold'], 0, ',', '.') }}" meta="Total dari semua item" icon="shopping-bag" tone="fuchsia" />
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Revenue Line Chart -->
        <div class="lg:col-span-2 glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10"
             x-data="{
                revenue: @js($analytics['revenue']),
                labels: @js($analytics['labels']),
                chart: null,
                init() {
                    this.$nextTick(() => this.renderChart());
                },
                renderChart() {
                    if (typeof ApexCharts === 'undefined' || this.chart) {
                        return;
                    }

                    this.chart = new ApexCharts(this.$refs.chart, {
                        series: [
                            { name: 'Pendapatan', type: 'area', data: this.revenue }
                        ],
                        chart: {
                            height: 256,
                            type: 'area',
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 },
                            fontFamily: 'Inter, ui-sans-serif, system-ui'
                        },
                        colors: ['#7c3aed'],
                        fill: {
                            type: 'gradient',
                            gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.05, stops: [0, 90, 100] }
                        },
                        stroke: { width: 3, curve: 'smooth' },
                        xaxis: {
                            categories: this.labels,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 700 } }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#94a3b8', fontSize: '10px' },
                                formatter: (value) => 'Rp ' + (value / 1000) + 'k'
                            }
                        },
                        grid: { borderColor: 'rgba(148, 163, 184, 0.14)', strokeDashArray: 4 },
                        legend: { show: false },
                        tooltip: {
                            theme: 'dark',
                            y: {
                                formatter: (value) => 'Rp ' + Number(value).toLocaleString('id-ID')
                            }
                        }
                    });

                    this.chart.render();
                }
             }">
            <h3 class="text-base font-extrabold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-6">Tren Pendapatan</h3>
            <div x-ref="chart" class="min-h-[256px]"></div>
        </div>
        
        <!-- Ticket & Merch Distribution -->
        <div class="glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10 flex flex-col gap-6">
            <div>
                <h3 class="text-base font-extrabold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-4">Distribusi Tiket</h3>
                @if(count($ticketDistribution) > 0)
                    <div class="space-y-3">
                        @foreach($ticketDistribution as $dist)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ $dist['color'] }}"></div>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $dist['label'] }}</span>
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $dist['percentage'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada data penjualan tiket.</p>
                @endif
            </div>
            
            <div class="border-t border-slate-100 dark:border-slate-800 pt-6">
                <h3 class="text-base font-extrabold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-4">Distribusi Suvenir</h3>
                @if(count($merchDistribution) > 0)
                    <div class="space-y-3">
                        @foreach($merchDistribution as $dist)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full {{ $dist['color'] }}"></div>
                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ $dist['label'] }}</span>
                                </div>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $dist['percentage'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500">Belum ada data penjualan merch.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Variant Sales Breakdown -->
    <div class="glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10">
        <h3 class="text-base font-extrabold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-4">Rincian Penjualan Varian Suvenir</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:bg-slate-900/60">
                    <tr>
                        <th class="px-4 py-3 font-bold">Pesanan</th>
                        <th class="px-4 py-3 font-bold">Varian</th>
                        <th class="px-4 py-3 font-bold text-right">Terjual</th>
                        <th class="px-4 py-3 font-bold text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($merchVariantsSold as $variant)
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $variant->item_name }}</td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $variant->variant_group }}: {{ $variant->variant_value }}</td>
                            <td class="px-4 py-3 text-right font-medium text-slate-700 dark:text-slate-300">{{ number_format($variant->total_sold, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($variant->total_revenue, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada data penjualan varian merch pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Transaction Activity -->
    <div class="glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10">
        <h3 class="text-base font-extrabold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-4">Aktivitas Transaksi</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:bg-slate-900/60">
                    <tr>
                        <th class="px-4 py-3 font-bold">Pesanan</th>
                        <th class="px-4 py-3 font-bold">Pembeli</th>
                        <th class="px-4 py-3 font-bold">Produk</th>
                        <th class="px-4 py-3 font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($transactions as $order)
                        <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 align-top">
                                <div class="font-bold text-slate-900 dark:text-white">#{{ $order->id }}</div>
                                <div class="text-sm text-slate-500">{{ $order->paid_at?->translatedFormat('d M Y, H:i') }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="font-medium text-slate-900 dark:text-white">{{ $order->user->name }}</div>
                                <div class="text-sm text-slate-500">{{ $order->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                @if($order->tickets->count() > 0)
                                    <div class="font-bold mb-1">Tiket:</div>
                                    <ul class="list-disc list-inside mb-2">
                                        @foreach($order->tickets->groupBy('ticketCategory.name') as $catName => $ticketsGroup)
                                            <li>{{ $ticketsGroup->count() }}x {{ $catName }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($order->merchandise->count() > 0)
                                    <div class="font-bold mb-1">Suvenir:</div>
                                    <ul class="list-disc list-inside">
                                        @foreach($order->merchandise as $merch)
                                            <li>{{ $merch->quantity }}x {{ $merch->merchandiseItem->name }} ({{ $merch->merchandiseVariant->variant_value }})</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                <div class="font-bold text-slate-900 dark:text-white">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
                                <div class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Berhasil</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada transaksi yang berhasil pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
