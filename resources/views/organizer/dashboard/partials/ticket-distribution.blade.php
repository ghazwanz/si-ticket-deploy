<div class="glass-panel rounded-[2rem] p-6 border border-white/60 dark:border-white/10">
    <div class="mb-6">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Komposisi Penjualan</p>
        <h3 class="text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">Distribusi Tiket</h3>
    </div>

    @forelse($ticketDistribution as $item)
        <div class="mb-5 last:mb-0">
            <div class="mb-2 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ $item['label'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($item['sold'], 0, ',', '.') }} tiket terjual</p>
                </div>
                <span class="text-sm font-extrabold text-slate-950 dark:text-white">{{ $item['percentage'] }}%</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                <div class="h-full {{ $item['color'] }} rounded-full transition-all duration-500" style="width: {{ $item['percentage'] }}%"></div>
            </div>
        </div>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-200 bg-white/50 p-6 text-center dark:border-slate-800 dark:bg-slate-900/30">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600/10 text-violet-600 dark:text-violet-400">
                <x-heroicon-o-ticket class="h-6 w-6" />
            </div>
            <h4 class="font-extrabold tracking-tight text-slate-950 dark:text-white">Belum ada tiket terjual</h4>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Distribusi tiket akan tampil setelah pesanan berhasil dibayar.</p>
        </div>
    @endforelse
</div>
