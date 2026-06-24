@php
    $statusLabels = [
        'draft' => 'Draf',
        'awaiting_approval' => 'Menunggu Persetujuan',
        'published' => 'Terbit',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    $statusTones = [
        'draft' => 'bg-slate-500/10 text-slate-600 dark:text-slate-300',
        'awaiting_approval' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        'published' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        'completed' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
        'cancelled' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
    ];
@endphp

<div class="glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Operasional</p>
            <h3 class="text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">Acara Terbaru</h3>
        </div>
        <a href="{{ route('organizer.events.index') }}" data-link class="inline-flex items-center gap-1 text-sm font-bold text-violet-600 transition-colors hover:text-violet-800 dark:text-violet-400 dark:hover:text-violet-300">
            Lihat Semua
            <x-heroicon-o-arrow-right class="h-4 w-4" />
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-widest text-slate-500 dark:bg-slate-900/60">
                <tr>
                    <th class="px-4 py-3 font-bold">Acara</th>
                    <th class="px-4 py-3 font-bold">Tanggal</th>
                    <th class="px-4 py-3 font-bold">Status</th>
                    <th class="px-4 py-3 font-bold">Penjualan</th>
                    <th class="px-4 py-3 font-bold">Pencairan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentEvents as $event)
                    @php
                        $sold = $event->ticketCategories->sum('sold_count');
                        $quota = $event->ticketCategories->sum('quota');
                        $isPayoutEligible = $event->status->value === 'completed';
                    @endphp
                    <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-900 dark:text-white">{{ $event->name }}</div>
                            <div class="text-xs text-slate-500">{{ $event->venue_name }} · {{ $event->city }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">{{ $event->event_date->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-bold {{ $statusTones[$event->status->value] ?? $statusTones['draft'] }}">
                                {{ $statusLabels[$event->status->value] ?? $event->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ number_format($sold, 0, ',', '.') }}/{{ number_format($quota, 0, ',', '.') }} Tiket</td>
                        <td class="px-4 py-3">
                            @if($isPayoutEligible)
                                <span class="rounded-full px-2 py-1 text-xs font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Siap Cair</span>
                            @else
                                <span class="rounded-full px-2 py-1 text-xs font-bold bg-slate-500/10 text-slate-600 dark:text-slate-300">Belum Siap</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center">
                            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600/10 text-violet-600 dark:text-violet-400">
                                <x-heroicon-o-calendar-days class="h-6 w-6" />
                            </div>
                            <p class="font-extrabold tracking-tight text-slate-950 dark:text-white">Belum ada acara</p>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Buat acara pertama untuk mulai melihat statistik operasional.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
