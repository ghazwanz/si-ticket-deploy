<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
            {{ __('Pesanan Saya') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="px-4 sm:px-6 lg:px-8 space-y-6 mx-auto">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Kelola dan pantau semua tiket acara Anda dalam satu tempat.</p>
            </div>

            <!-- Search and Filters Form -->
            <form method="GET" action="{{ route('pesanan.index') }}" class="flex flex-col sm:flex-row gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama acara atau ID pesanan..." class="w-full pl-11 pr-4 py-2.5 bg-white/50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800 rounded-2xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500/20 placeholder-slate-450 dark:placeholder-slate-500 text-sm transition-all">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="w-full sm:w-auto rounded-2xl bg-violet-600 hover:bg-violet-700 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-violet-600/10 transition duration-200 cursor-pointer">
                        Cari
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('pesanan.index') }}" data-link class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-350 flex items-center justify-center transition duration-200">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            @php
                $filters = ['semua' => 'Semua', 'pending' => 'Menunggu Pembayaran', 'paid' => 'Berhasil', 'cancelled' => 'Dibatalkan', 'failed' => 'Gagal'];
                $activeFilter = request('status', 'semua');
            @endphp

            <div class="flex flex-wrap gap-2">
                @foreach($filters as $value => $label)
                    @php
                        $targetUrl = route('pesanan.index', array_merge(request()->only('search'), $value !== 'semua' ? ['status' => $value] : ['status' => null]));
                    @endphp
                    <a href="{{ $targetUrl }}" data-link class="inline-flex items-center justify-center rounded-full border px-4 py-1.5 text-xs font-bold transition-all {{ $activeFilter === $value ? 'border-violet-600 bg-violet-600 text-white shadow-lg shadow-violet-600/25 dark:shadow-none' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400 hover:border-violet-500 hover:text-slate-900 dark:hover:text-white' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Orders Grid -->
            <div class="grid gap-6 md:grid-cols-2">
                @forelse($pesanan as $item)
                    @php
                        $badgeClass = match($item->status->value) {
                            'paid' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20',
                            'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20',
                            'cancelled' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20',
                            'failed' => 'bg-slate-50 text-slate-700 dark:bg-slate-850 dark:text-slate-400 border border-slate-200 dark:border-slate-750',
                            default => 'bg-slate-50 text-slate-700 dark:bg-slate-850 dark:text-slate-400 border border-slate-200 dark:border-slate-750',
                        };
                    @endphp

                    <article class="flex flex-col gap-4 rounded-3xl border border-slate-200/80 dark:border-slate-850 bg-white dark:bg-slate-900/40 backdrop-blur-xl p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg hover:shadow-violet-600/5 hover:border-violet-600/30">
                        <div class="flex flex-col sm:items-start gap-4">
                            <img src="{{ $item->event->banner_image ? Storage::url($item->event->banner_image) : asset('img/eobanner.png') }}" alt="{{ $item->event->name }}" class="h-32 w-full sm:h-20 sm:w-20 shrink-0 rounded-2xl object-cover border border-slate-100 dark:border-slate-800 shadow-sm">

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <h2 class="truncate text-base font-extrabold text-slate-900 dark:text-white leading-tight">{{ $item->event->name }}</h2>
                                    <span class="inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">{{ $item->status->label() }}</span>
                                </div>

                                <div class="mt-3 space-y-1.5 text-sm text-slate-500 dark:text-slate-400">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-calendar class="h-5 w-5 shrink-0 text-slate-400 dark:text-slate-500" />
                                        <span>{{ \Carbon\Carbon::parse($item->event->event_date)->translatedFormat('d F Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-ticket class="h-5 w-5 shrink-0 text-slate-400 dark:text-slate-500" />
                                        <span>ID: #{{ $item->midtrans_order_id ?? $item->id }}</span>
                                    </div>
                                </div>

                                @if($item->status->value === 'paid')
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if($item->tickets_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-500/20">
                                                <x-heroicon-o-ticket class="h-5 w-5 shrink-0" />
                                                {{ $item->tickets_checked_in_count }}/{{ $item->tickets_count }} Dipindai
                                            </span>
                                        @endif
                                        @if($item->merchandise_count > 0)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-[11px] font-bold text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                                                <x-heroicon-o-shopping-bag class="h-5 w-5 shrink-0" />
                                                {{ $item->merchandise_picked_up_count }}/{{ $item->merchandise_count }} Diclaim
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-auto">
                            <div>
                                <span class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400 block mb-0.5">Total Bayar</span>
                                <div class="text-lg font-black text-slate-900 dark:text-white">Rp {{ number_format($item->total_amount, 0, ',', '.') }}</div>
                            </div>
                            <a href="{{ route('pesanan.show', $item->id) }}" data-link class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-2 text-xs font-bold text-slate-700 dark:text-slate-350 transition hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-violet-600 hover:text-violet-650 dark:hover:text-violet-400">
                                Lihat Detail
                                <x-heroicon-o-chevron-right class="h-3.5 w-3.5 text-slate-400" />
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/40 p-12 text-center md:col-span-2 shadow-sm">
                        <x-heroicon-o-inbox class="mx-auto mb-4 h-12 w-12 text-slate-400 dark:text-slate-500" />
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Belum ada pesanan</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">Pesanan yang Anda cari tidak dapat ditemukan.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $pesanan->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
