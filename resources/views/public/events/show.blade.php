@php
    $isSuspended = $event->status->value === 'awaiting_cancellation';
    $isCancelled = $event->status->value === 'cancelled';
    $isCompleted = $event->status->value === 'completed';

    $merchVariantsJson = $event->merchandiseItems->flatMap(fn($item) => 
        $item->variants->map(fn($v) => [
            'id' => $v->id,
            'merchandise_item_id' => $item->id,
            'name' => $item->name . ' (' . $v->variant_value . ')',
            'price' => (int) ($item->base_price + $v->price_adjustment)
        ])
    )->values()->toJson();
@endphp

<x-public-layout :title="$event->name">
    <div class="px-4 py-8 md:px-6 lg:py-12 relative">
        @if($isCancelled)
            <!-- Cancelled Watermark -->
            <div class="absolute inset-0 pointer-events-none z-50 flex items-center justify-center overflow-hidden">
                <div class="text-[12rem] font-black text-rose-500/10 -rotate-12 select-none border-y-8 border-rose-500/10 py-8 whitespace-nowrap">
                    CANCELLED
                </div>
            </div>
        @endif

        <div class="grid gap-8 mx-auto w-full max-w-7xl lg:grid-cols-[1fr_380px] xl:gap-12 relative z-10 {{ $isCancelled ? 'ring-4 ring-rose-500/50 rounded-[2rem] p-2 bg-rose-500/5' : '' }}"
             x-data='checkoutApp({{ $event->ticketCategories->toJson() }}, {{ $merchVariantsJson }}, {{ $isSuspended ? "true" : "false" }}, {{ $isCancelled ? "true" : "false" }})'
        >

            <!-- Kiri: Info Event & Hero -->
            <div class="space-y-8">
                <!-- Image Banner with View Transition -->
                <section class="opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98]" data-reveal data-reveal-delay="0">
                    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl shadow-md">
                        <div class="relative isolate h-[320px] w-full md:h-[400px]">
                            <!-- The view-transition-name matches the catalog card -->
                            <img src="{{ $event->image_path ? Storage::url($event->image_path) : Storage::url('img/eobanner.png') }}" alt="{{ $event->name }}" class="absolute inset-0 h-full w-full object-cover opacity-60 {{ $isSuspended ? 'grayscale' : '' }}" style="view-transition-name: event-img-{{ $event->id }};" />
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/40 to-transparent"></div>

                            <div class="absolute inset-x-0 bottom-0 p-6 md:p-8">
                                <div class="flex flex-wrap items-center gap-2 mb-3">
                                    <span class="inline-flex rounded-xl border border-white/10 bg-black/30 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-white backdrop-blur-md">
                                        {{ $event->category?->name ?? 'Event' }}
                                    </span>
                                    @if($isNearlySoldOut)
                                        <span class="inline-flex rounded-xl border border-amber-500/30 bg-amber-500/20 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-amber-300 backdrop-blur-md animate-pulse">
                                            Nearly Sold Out
                                        </span>
                                    @endif
                                    @if($isSuspended)
                                        <span class="inline-flex rounded-xl border border-amber-500/30 bg-amber-500/20 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-amber-300 backdrop-blur-md">
                                            Sales Paused
                                        </span>
                                    @endif
                                    @if($isCancelled)
                                        <span class="inline-flex rounded-xl border border-rose-500/30 bg-rose-500/20 px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-rose-300 backdrop-blur-md">
                                            Event Cancelled
                                        </span>
                                    @endif
                                </div>
                                <h1 class="text-3xl font-extrabold tracking-tight text-white md:text-5xl lg:text-6xl">{{ $event->name }}</h1>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98] grid gap-6 rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl p-6 lg:p-8 shadow-md" data-reveal data-reveal-delay="100">
                    <div class="flex items-center gap-3">
                        <span class="h-6 w-1 rounded-full bg-violet-500"></span>
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Informasi Acara</h2>
                    </div>

                    <div class="max-w-3xl text-sm leading-relaxed text-slate-650 dark:text-slate-400 prose dark:prose-invert">
                        {!! nl2br(e($event->description)) !!}
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3 mt-4">
                        <div class="rounded-2xl bg-slate-50 dark:bg-white/5 p-5 border border-slate-200 dark:border-white/10 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">Tanggal</p>
                            <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">{{ $event->event_date->translatedFormat('d M Y') }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-450">{{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('H:i') }} - {{ \Carbon\Carbon::parse($event->end_time)->translatedFormat('H:i') }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-white/5 p-5 border border-slate-200 dark:border-white/10 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">Lokasi</p>
                            <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">{{ $event->venue_name }}</p>
                            <p class="text-sm text-slate-500 dark:text-slate-450">{{ $event->city }}</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 dark:bg-white/5 p-5 border border-slate-200 dark:border-white/10 shadow-sm">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-fuchsia-605 dark:text-fuchsia-400">Penyelenggara</p>
                            <p class="mt-2 text-sm font-bold text-slate-900 dark:text-white">{{ $event->organizer->name }}</p>
                        </div>
                    </div>
                </section>

                @if($event->merchandiseItems->count() > 0)
                <section class="opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98] space-y-6" data-reveal data-reveal-delay="150">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="h-6 w-1 rounded-full bg-fuchsia-500"></span>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Suvenir Resmi</h2>
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        @foreach ($event->merchandiseItems as $item)
                            @php
                                $prices = $item->variants->map(fn($v) => $item->base_price + $v->price_adjustment);
                                $minPrice = $prices->min();
                            @endphp
                            <article class="relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl shadow-md transition-all duration-300 hover:-translate-y-1 hover:border-fuchsia-500/30 flex flex-col h-full">
                                <div class="relative h-48 w-full bg-slate-100 dark:bg-white/5">
                                    @if($item->image)
                                        <img src="{{ asset($item->image) }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full items-center justify-center text-slate-400 dark:text-slate-600">
                                            <x-heroicon-o-shopping-bag class="h-12 w-12" />
                                        </div>
                                    @endif
                                    
                                    {{-- Selection summary pill --}}
                                    <div x-show="getMerchItemTotal('{{ $item->id }}') > 0"
                                         x-cloak
                                         class="absolute right-3 top-3 rounded-full border border-fuchsia-500/30 bg-fuchsia-500/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-fuchsia-600 dark:text-fuchsia-300 shadow-sm backdrop-blur-md animate-pulse">
                                         Terpilih: <span x-text="getMerchItemTotal('{{ $item->id }}')"></span>
                                    </div>
                                </div>
                                <div class="p-5 flex flex-col flex-1 justify-between">
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white leading-tight min-h-[2.5rem] line-clamp-2">{{ $item->name }}</h3>
                                    
                                    <div class="mt-4 pt-4 flex items-center justify-between border-t border-slate-100 dark:border-white/5">
                                        <div class="flex flex-col">
                                            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Mulai dari</span>
                                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-450">Rp {{ number_format($minPrice, 0, ',', '.') }}</span>
                                        </div>
                                        <button type="button" @click="activeMerchModalId = '{{ $item->id }}'"
                                                class="rounded-xl border border-fuchsia-500/30 bg-fuchsia-500/10 px-4 py-2 text-xs font-bold text-fuchsia-600 dark:text-fuchsia-400 hover:bg-fuchsia-600 hover:text-white transition shadow-sm cursor-pointer">
                                            Lihat Rincian
                                        </button>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            <!-- Kanan: Checkout Card Sticky -->
            <aside class="opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98] lg:sticky lg:top-28 h-max" data-reveal data-reveal-delay="200">
                <div class="rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl p-6 lg:p-8 shadow-md relative overflow-hidden">
                    <!-- Glow effect inside card -->
                    <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-violet-500/20 blur-3xl pointer-events-none"></div>

                    @if($isSuspended || $isCancelled || $isCompleted)
                        <div class="absolute inset-0 z-10 bg-white/80 dark:bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center">
                            <x-heroicon-o-no-symbol class="h-12 w-12 text-rose-500 mb-4" />
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Penjualan Ditutup</h3>
                            <p class="text-sm text-slate-650 dark:text-slate-300 mt-2">
                                {{ $isCancelled ? 'Event ini telah dibatalkan.' : ($isCompleted ? 'Acara ini telah selesai.' : 'Penjualan Tiket Ditunda') }}
                            </p>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Pilih Tiket</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Pilih kategori dan jumlah tiket per transaksi.</p>
                    </div>

                    <form action="{{ route('checkout.index') }}" method="GET">
                        <!-- Hidden inputs for selected tickets -->
                        <template x-for="item in getActiveTickets()" :key="'ticket-' + item.id">
                            <input type="hidden" :name="'tickets[' + item.id + ']'" :value="item.qty">
                        </template>

                        <!-- Hidden inputs for selected merchandise -->
                        <template x-for="item in getActiveMerch()" :key="'merch-' + item.id">
                            <input type="hidden" :name="'merchandise[' + item.id + ']'" :value="item.qty">
                        </template>

                        <div class="space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            @foreach($event->ticketCategories as $cat)
                                @php
                                    $stock = $cat->quota - $cat->sold_count;
                                    $isOos = $stock <= 0;
                                @endphp
                                <div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4 transition-colors hover:border-slate-300 dark:hover:border-white/20">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $cat->name }}</h4>
                                            {{-- <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $cat->description }}</p> --}}
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-extrabold text-emerald-600 dark:text-emerald-450">Rp {{ number_format($cat->price, 0, ',', '.') }}</div>
                                            @if($isOos)
                                                <div class="text-xs font-bold text-rose-500 uppercase tracking-widest mt-1">Habis</div>
                                            @else
                                                <div class="text-xs text-slate-500 mt-1">Sisa {{ $stock }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    @if(!$isOos)
                                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-white/10 pt-3">
                                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-455">Jumlah</span>
                                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-black/20 p-1">
                                            <button type="button" @click="updateQty('{{ $cat->id }}', -1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-white/5 border border-slate-205 dark:border-transparent text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 disabled:opacity-50 transition" :disabled="getQty('{{ $cat->id }}') <= 0 || isSuspended || isCancelled">−</button>
                                            <span class="text-sm font-bold text-slate-900 dark:text-white min-w-[20px] text-center" x-text="getQty('{{ $cat->id }}')">0</span>
                                            <button type="button" @click="updateQty('{{ $cat->id }}', 1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-white/5 border border-slate-205 dark:border-transparent text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 disabled:opacity-50 transition" :disabled="getQty('{{ $cat->id }}') >= Math.min({{ $cat->max_per_user ?? 5 }}, {{ $stock }}) || totalTickets >= 5 || isSuspended || isCancelled">+</button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Selected Summary -->
                        <div x-show="totalTickets > 0 || totalMerch > 0" class="mt-6 space-y-2 rounded-2xl bg-slate-50 dark:bg-white/5 p-4 border border-slate-200 dark:border-white/5 text-xs">
                            <span class="font-bold text-slate-900 dark:text-white block mb-2 uppercase tracking-wider text-[10px] text-slate-400">Item Terpilih</span>
                            
                            <!-- Tickets -->
                            <template x-for="item in getActiveTickets()" :key="'summary-ticket-' + item.id">
                                <div class="flex items-center justify-between gap-4 text-slate-650 dark:text-slate-400 py-1 border-b border-dashed border-slate-100 dark:border-white/5 last:border-0 last:pb-0">
                                    <span x-text="categories.find(c => c.id === item.id)?.name">Tiket</span>
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.qty + 'x'"></span>
                                </div>
                            </template>

                            <!-- Merchandise -->
                            <template x-for="item in getActiveMerch()" :key="'summary-merch-' + item.id">
                                <div class="flex items-center justify-between gap-4 text-slate-650 dark:text-slate-400 py-1 border-b border-dashed border-slate-100 dark:border-white/5 last:border-0 last:pb-0">
                                    <span x-text="merchVariants.find(mv => mv.id === item.id)?.name">Varian Suvenir</span>
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="item.qty + 'x'"></span>
                                </div>
                            </template>
                        </div>

                        <div class="mt-6 space-y-3 rounded-2xl bg-slate-100 dark:bg-black/20 p-5 text-sm text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-white/5">
                            <div class="flex items-center justify-between gap-4">
                                <span>Total Tiket</span>
                                <span class="font-bold text-slate-900 dark:text-white" x-text="totalTickets">0</span>
                            </div>
                            <div class="border-t border-slate-200 dark:border-white/10 pt-3 flex items-center justify-between gap-4">
                                <span class="font-bold text-slate-900 dark:text-white">Estimasi Subtotal</span>
                                <span class="text-lg font-extrabold tracking-tight text-violet-600 dark:text-violet-400" x-text="'Rp ' + format(totalPrice)">Rp 0</span>
                            </div>
                        </div>

                        <button type="submit" class="mt-6 inline-flex h-12 w-full items-center justify-center rounded-xl bg-violet-600 px-4 text-sm font-bold text-white shadow-lg shadow-violet-600/25 transition-all hover:-translate-y-0.5 hover:bg-violet-750 focus:outline-none focus:ring-4 focus:ring-violet-500/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0" :disabled="totalTickets === 0">
                            Lanjut ke Pembayaran
                        </button>

                    </form>
                </div>
            </aside>

            @foreach ($event->merchandiseItems as $item)
                <template x-teleport="#spa-modals">
                    <div x-show="activeMerchModalId === '{{ $item->id }}'"
                         x-cloak
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm"
                         @keydown.escape.window="if(activeMerchModalId === '{{ $item->id }}') activeMerchModalId = null"
                    >
                        <div @click.away="activeMerchModalId = null"
                             x-show="activeMerchModalId === '{{ $item->id }}'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                             class="relative w-full max-w-7xl overflow-hidden rounded-[2rem] border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 shadow-2xl transition-all duration-300 max-h-[90vh] flex flex-col md:flex-row"
                        >
                            <button @click="activeMerchModalId = null" class="absolute right-4 top-4 z-50 p-2 rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-900 transition shadow-sm" aria-label="Close modal">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                            <div class="w-full md:w-3/4 bg-slate-50 dark:bg-slate-900/50 flex items-center justify-center h-48 md:h-auto md:min-h-[300px] border-b md:border-b-0 md:border-r border-slate-200 dark:border-slate-800 relative shrink-0">
                                @if($item->image)
                                    @php
                                        $merchImage = str_starts_with($item->image, 'img/') ? asset($item->image) : Storage::url($item->image);
                                    @endphp
                                    <img src="{{ $merchImage }}" alt="{{ $item->name }}" class="absolute inset-0 h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-slate-400 dark:text-slate-600">
                                        <x-heroicon-o-shopping-bag class="h-20 w-20" />
                                    </div>
                                @endif
                            </div>
                            <div class="w-full md:w-1/4 p-6 md:p-8 flex flex-col justify-between overflow-y-auto max-h-[60vh] md:max-h-[80vh] custom-scrollbar bg-white dark:bg-slate-950">
                                <div>
                                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $item->name }}</h2>
                                    <div class="mt-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400 prose dark:prose-invert">
                                        {!! nl2br(e($item->description)) !!}
                                    </div>
                                </div>
                                <div class="mt-8 border-t border-slate-200 dark:border-slate-800 pt-6 bg-white dark:bg-slate-950">
                                    <h4 class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-4">Pilih Varian & Jumlah</h4>
                                    <div class="space-y-4">
                                        @foreach($item->variants as $variant)
                                            @php
                                                $stock = $variant->stock - $variant->sold_count;
                                                $isOos = $stock <= 0;
                                                $price = $item->base_price + $variant->price_adjustment;
                                            @endphp
                                            <div class="flex items-center justify-between rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-white/5 p-4 transition-colors hover:border-slate-200 dark:hover:border-slate-700">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $variant->variant_group }}: {{ $variant->variant_value }}</span>
                                                    @if($isOos)
                                                        <span class="text-xs font-bold text-rose-500 uppercase tracking-widest mt-1">Habis</span>
                                                    @else
                                                        <span class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sisa {{ $stock }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    @if(!$isOos)
                                                        <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-450 mr-2">Rp {{ number_format($price, 0, ',', '.') }}</span>
                                                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-black/20 p-1">
                                                            <button type="button" @click="updateMerchQty('{{ $variant->id }}', -1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-transparent text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 disabled:opacity-50 transition" :disabled="getMerchQty('{{ $variant->id }}') <= 0 || isSuspended || isCancelled">−</button>
                                                            <span class="text-sm font-bold text-slate-900 dark:text-white min-w-[20px] text-center" x-text="getMerchQty('{{ $variant->id }}')">0</span>
                                                            <button type="button" @click="updateMerchQty('{{ $variant->id }}', 1)" class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-white/5 border border-slate-200 dark:border-transparent text-slate-800 dark:text-white hover:bg-slate-50 dark:hover:bg-white/20 disabled:opacity-50 transition" :disabled="getMerchQty('{{ $variant->id }}') >= {{ $stock }} || isSuspended || isCancelled">+</button>
                                                        </div>
                                                    @else
                                                        <span class="text-sm font-bold text-rose-500">Habis</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            @endforeach
        </div>
    </div>
</x-public-layout>

