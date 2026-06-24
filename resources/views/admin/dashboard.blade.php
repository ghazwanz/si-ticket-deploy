<x-admin-layout>
    <x-slot name="title">Panel Kontrol - JoinFest Admin</x-slot>
    <x-slot name="header">PANEL KONTROL</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Selamat Datang, Admin</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Ringkasan performa platform JoinFest hari ini.</p>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Pengguna --}}
            <div class="glass-panel p-6 rounded-3xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m12-10a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Total Pengguna</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums">{{ number_format($stats['total_pengguna']) }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-[10px] font-bold {{ $stats['user_growth_color'] }}">{{ $stats['user_growth_text'] }}</span>
                </div>
            </div>

            {{-- Event Tinjau --}}
            <div class="glass-panel p-6 rounded-3xl relative overflow-hidden group border-orange-500/20">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-12 h-12 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 00 2 2h10a2 2 0 00 2-2V7a2 2 0 00-2-2h-2"/></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Tinjau Acara</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums text-orange-500">{{ $stats['event_review'] }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    @if($stats['event_review'] > 0)
                        <a href="{{ route('admin.events.index') }}" data-link class="text-[10px] font-bold text-orange-600 dark:text-orange-400 hover:underline">
                            {{ $stats['event_review'] }} acara membutuhkan persetujuan &rarr;
                        </a>
                    @else
                        <span class="text-[10px] font-bold text-slate-400">Semua acara telah ditinjau</span>
                    @endif
                </div>
            </div>

            {{-- Acara Aktif --}}
            <div class="glass-panel p-6 rounded-3xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">Acara Aktif</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums text-violet-500">{{ $stats['event_aktif'] }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-[10px] font-bold {{ $stats['event_growth_color'] }}">{{ $stats['event_growth_text'] }}</span>
                </div>
            </div>

            {{-- Pending EO --}}
            <div class="glass-panel p-6 rounded-3xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-1">EO Tertunda</p>
                <h3 class="text-3xl font-bold text-slate-900 dark:text-white tabular-nums text-rose-500">{{ $stats['eo_pending'] }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    @if($stats['eo_pending'] > 0)
                        <span class="text-[10px] font-bold text-rose-500">{{ $stats['eo_pending'] }} EO menunggu verifikasi</span>
                    @else
                        <span class="text-[10px] font-bold text-slate-400">Tidak ada EO tertunda</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Analitik Transaksi --}}
        <div class="glass-panel rounded-[2rem] p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white">Wawasan Transaksi</h4>
                    <p class="text-xs text-slate-500 font-medium">Histori volume dan nilai transaksi 30 hari terakhir.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-violet-600 uppercase tracking-widest bg-violet-600/5 px-3 py-1.5 rounded-xl border border-violet-600/10">
                        <span class="w-1.5 h-1.5 rounded-full bg-violet-600"></span>
                        Pendapatan (Rp)
                    </span>
                    <span class="flex items-center gap-1.5 text-[10px] font-bold text-blue-500 uppercase tracking-widest bg-blue-500/5 px-3 py-1.5 rounded-xl border border-blue-500/10">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        Pesanan
                    </span>
                </div>
            </div>
            
            <div x-data="{ 
                revenue: @js($analytics['revenue']),
                volume: @js($analytics['volume']),
                labels: @js($analytics['labels']),
                init() {
                    const options = {
                        series: [{
                            name: 'Total Pendapatan',
                            type: 'area',
                            data: this.revenue
                        }, {
                            name: 'Order Volume',
                            type: 'line',
                            data: this.volume
                        }],
                        chart: {
                            height: 350,
                            type: 'line',
                            toolbar: { show: false },
                            animations: { enabled: true, easing: 'easeinout', speed: 800 }
                        },
                        colors: ['#7c3aed', '#3b82f6'],
                        fill: {
                            type: ['gradient', 'solid'],
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        stroke: { width: [3, 3], curve: 'smooth' },
                        xaxis: {
                            categories: this.labels,
                            axisBorder: { show: false },
                            axisTicks: { show: false },
                            labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
                        },
                        yaxis: [
                            { 
                                title: { text: 'Pendapatan', style: { color: '#7c3aed', fontWeight: 700 } },
                                labels: { 
                                    style: { colors: '#94a3b8', fontSize: '10px' },
                                    formatter: (val) => 'Rp ' + val.toLocaleString()
                                } 
                            },
                            {
                                opposite: true,
                                title: { text: 'Pesanan', style: { color: '#3b82f6', fontWeight: 700 } },
                                labels: { style: { colors: '#94a3b8', fontSize: '10px' } }
                            }
                        ],
                        grid: { borderColor: 'rgba(148, 163, 184, 0.1)', strokeDashArray: 4 },
                        tooltip: { theme: 'dark', x: { show: true } },
                        legend: { show: false }
                    };
                    
                    new ApexCharts(this.$refs.chart, options).render();
                }
            }" class="w-full">
                <div x-ref="chart"></div>
            </div>
        </div>

        {{-- Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Activity Logs --}}
            <div class="lg:col-span-2 glass-panel rounded-[2rem] p-8">
                <div class="flex items-center justify-between mb-8">
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white">Aktivitas Terbaru</h4>
                    <button class="text-xs font-bold text-violet-600 dark:text-violet-400 hover:underline">Lihat Semua</button>
                </div>
                <div class="space-y-6">
                    @forelse($logs as $log)
                    <div class="flex items-start gap-4 group">
                        <div class="w-10 h-10 rounded-2xl {{ $log['color'] }} flex items-center justify-center font-bold text-sm shrink-0 group-hover:scale-110 transition-transform" data-icon="{{ $log['icon'] }}">
                            <x-dynamic-component :component="'heroicon-o-' . $log['icon']" class="w-5 h-5" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $log['action'] }}</p>
                                <span class="text-[10px] font-medium text-slate-400">{{ $log['time'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Oleh: <span class="text-slate-700 dark:text-slate-300">{{ $log['user'] }}</span></p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-xs text-slate-500">Tidak ada aktivitas terbaru.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Category Distribution --}}
            <div class="glass-panel rounded-[2rem] p-8">
                <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-8">Distribusi Kategori</h4>
                <div class="space-y-6">
                    @foreach($distribusi as $item)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-400">{{ $item['label'] }}</span>
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $item['pct'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full {{ $item['color'] }} rounded-full" style="width: {{ $item['pct'] }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-8 pt-8 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Data <i>real-time</i> disinkronkan</p>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>