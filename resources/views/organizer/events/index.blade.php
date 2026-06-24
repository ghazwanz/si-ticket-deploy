@extends('layouts.organizer')
@section('title', 'Manajemen Acara')
@section('page-title', 'Manajemen Acara')

@section('content')
<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-organizer.stat-card label="Total Acara" value="{{ $events->total() }}" meta="Semua status acara Anda" icon="calendar-days" tone="violet" />
        <x-organizer.stat-card label="Tiket Terjual" value="{{ number_format($ticketsSold, 0, ',', '.') }}" meta="Total tiket berhasil dipesan" icon="ticket" tone="emerald" />
        <x-organizer.stat-card label="Pendapatan Kotor" value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}" meta="Estimasi omzet seluruh acara" icon="banknotes" tone="sky" />
        <x-organizer.stat-card label="Acara Mendatang" value="{{ $upcomingEvents }}" meta="Dalam status terbit" icon="clock" tone="amber" />
    </div>

    <div class="glass-panel rounded-2xl shadow-sm border border-white/60 dark:border-white/10 overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400">Katalog Acara</p>
                <h3 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Daftar Acara</h3>
            </div>
            <a href="{{ route('organizer.events.create') }}" data-link class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-sm font-bold rounded-xl hover:from-violet-700 hover:to-indigo-700 transition-all shadow-sm">
                <x-heroicon-o-plus class="w-4 h-4" />
                Buat Acara Baru
            </a>
        </div>

        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20"
            x-data="{
                filters: @js(request()->query()),
                submit() {
                    const params = new URLSearchParams();
                    for (const [key, value] of Object.entries(this.filters)) {
                        if (value && key !== 'page') {
                            params.append(key, value);
                        }
                    }
                    window.location.search = params.toString();
                },
                reset() {
                    window.location.href = '{{ route('organizer.events.index') }}';
                }
            }">
            <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 items-stretch sm:items-center w-full lg:w-auto">
                    <!-- Search -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-slate-400" />
                        </div>
                        <input type="text" x-model="filters.search" @keydown.enter="submit" placeholder="Cari nama, lokasi..." class="w-full pl-10 pr-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white transition-all shadow-sm" />
                    </div>

                    <!-- Status -->
                    <select x-model="filters.status" @change="submit" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white shadow-sm appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <!-- Category -->
                    <select x-model="filters.category" @change="submit" class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white shadow-sm appearance-none cursor-pointer">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>

                    <!-- Date Range -->
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                        <input type="date" x-model="filters.date_from" @change="submit" class="w-full sm:w-36 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white shadow-sm text-slate-500 cursor-pointer" title="Dari Tanggal" />
                        <span class="text-slate-400 text-sm hidden sm:block">-</span>
                        <input type="date" x-model="filters.date_to" @change="submit" class="w-full sm:w-36 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white shadow-sm text-slate-500 cursor-pointer" title="Sampai Tanggal" />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto lg:border-l border-t lg:border-t-0 border-slate-200 dark:border-slate-700 lg:pl-4 pt-4 lg:pt-0 mt-2 lg:mt-0">
                    <!-- Sort -->
                    <div class="flex items-center gap-2">
                        <select x-model="filters.sort" @change="submit" class="w-full sm:w-40 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-violet-500 focus:border-violet-500 dark:text-white shadow-sm appearance-none cursor-pointer">
                            <option value="created_at">Dibuat</option>
                            <option value="name">Nama Acara</option>
                            <option value="event_date">Tanggal Acara</option>
                            <option value="occupancy">Okupansi</option>
                        </select>
                        <button @click="filters.order = (filters.order === 'asc' ? 'desc' : 'asc'); submit()" class="p-2.5 shrink-0 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-600 dark:text-slate-400 hover:text-violet-600 hover:border-violet-300 transition-colors shadow-sm" title="Ubah urutan">
                            <x-heroicon-o-arrows-up-down class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Reset -->
                    <button @click="reset" x-show="Object.keys(filters).length > 0" class="text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors px-2 py-2 sm:py-0">
                        Reset
                    </button>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="mx-6 mt-4 p-4 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-500/20">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 text-xs uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-3 font-bold">Acara</th>
                        <th class="px-6 py-3 font-bold">Tanggal & Waktu</th>
                        <th class="px-6 py-3 font-bold">Lokasi</th>
                        <th class="px-6 py-3 font-bold">Okupansi</th>
                        <th class="px-6 py-3 font-bold">Status</th>
                        <th class="px-6 py-3 text-right font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($events as $event)
                    <tr class="hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $event->name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">Dibuat: {{ $event->created_at->translatedFormat('d M Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-600 dark:text-slate-300">
                            {{ \Carbon\Carbon::parse($event->event_date)->translatedFormat('d M Y') }}<br>
                            <span class="text-sm text-slate-600">{{ substr($event->start_time, 0, 5) }} - {{ substr($event->end_time, 0, 5) }}</span>
                        </td>
                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                            <div>{{ $event->venue_name }}</div>
                            <div class="text-sm text-slate-500">{{ $event->city }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $quota = $event->total_quota ?? 0;
                                $sold = $event->total_sold ?? 0;
                                $occupancy = $quota > 0 ? round(($sold / $quota) * 100) : 0;
                            @endphp
                            <div class="flex items-center gap-2">
                                <div class="h-1.5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden" title="{{ $sold }} / {{ $quota }} Tiket">
                                    <div class="h-full bg-violet-600 rounded-full" style="width: {{ $occupancy }}%"></div>
                                </div>
                                <span class="text-xs text-slate-500">{{ $sold }}/{{ $quota }} ({{ $occupancy }}%)</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $badgeClasses = [
                                    'published' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                    'draft' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300',
                                    'awaiting_approval' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                    'completed' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
                                    'awaiting_cancellation' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400',
                                    'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                ];
                                $statusLabel = [
                                    'published' => 'Terbit',
                                    'draft' => 'Draf',
                                    'awaiting_approval' => 'Ditinjau',
                                    'completed' => 'Selesai',
                                    'awaiting_cancellation' => 'Proses Batal',
                                    'cancelled' => 'Dibatalkan',
                                ];
                                $badge = $badgeClasses[$event->status->value] ?? 'bg-slate-500/10 text-slate-600 dark:text-slate-300';
                                $label = $statusLabel[$event->status->value] ?? $event->status->label();
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badge }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="inline-flex items-center justify-end gap-3">
                                <a href="{{ route('organizer.events.show', $event) }}" data-link class="inline-flex items-center justify-end gap-1 text-sky-600 hover:text-sky-800 dark:text-sky-400 dark:hover:text-sky-300 font-bold">
                                    <x-heroicon-o-chart-bar class="w-4 h-4" />
                                    Rincian
                                </a>
                                
                                @if(in_array($event->status->value, ['completed', 'cancelled', 'awaiting_cancellation']))
                                    @if($event->status->value !== 'completed')
                                        <a href="{{ route('organizer.events.edit', $event) }}" data-link class="inline-flex items-center justify-end gap-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300 font-bold">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                            Lihat
                                        </a>
                                    @endif
                                    @can('delete', $event)
                                        <x-organizer.confirm-delete
                                            :id="$event->id"
                                            :action="route('organizer.events.destroy', $event)"
                                            :name="$event->name"
                                        />
                                    @endcan
                                @else
                                    <a href="{{ route('organizer.events.edit', $event) }}" data-link class="inline-flex items-center justify-end gap-1 text-violet-600 hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300 font-bold">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        Edit
                                    </a>
                                    @can('delete', $event)
                                        <x-organizer.confirm-delete
                                            :id="$event->id"
                                            :action="route('organizer.events.destroy', $event)"
                                            :name="$event->name"
                                        />
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <x-heroicon-o-calendar-days class="mx-auto h-12 w-12 text-slate-300" />
                            <p class="mt-3 text-sm font-bold text-slate-500">Belum ada acara terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection