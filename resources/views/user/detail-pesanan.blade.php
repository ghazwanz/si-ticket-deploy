<x-guest-layout width="max-w-7xl" :center="false" :card="false" :backUrl="route('pesanan.index')" backText="Kembali ke Pesanan Saya">
    <div class="py-6">
        @if(session('success'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-250 bg-emerald-50 dark:bg-emerald-950/30 dark:border-emerald-900/50 p-4 text-sm text-emerald-800 dark:text-emerald-400 shadow-sm">
                <x-heroicon-o-check-circle class="h-5 w-5 shrink-0 text-emerald-500" />
                <div>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 flex items-start gap-3 rounded-2xl border border-rose-250 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-900/50 p-4 text-sm text-rose-800 dark:text-rose-400 shadow-sm">
                <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
                <div>
                    {{ session('error') }}
                </div>
            </div>
        @endif

        @php
            $statusColorMap = match($pesanan->status->value) {
                'paid' => [
                    'bg' => 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-400',
                    'titleColor' => 'text-emerald-900 dark:text-white',
                    'iconColor' => 'text-emerald-500 dark:text-emerald-400',
                    'title' => 'Pembayaran Berhasil',
                    'desc' => 'Terima kasih! Pembayaran Anda telah diterima pada ' . ($pesanan->paid_at ? \Carbon\Carbon::parse($pesanan->paid_at)->translatedFormat('d F Y H:i') : '-') . ' WIB.'
                ],
                'pending' => [
                    'bg' => 'bg-amber-50 dark:bg-amber-950/20 border-amber-200 dark:border-amber-900/50 text-amber-800 dark:text-amber-400',
                    'titleColor' => 'text-amber-900 dark:text-white',
                    'iconColor' => 'text-amber-500 dark:text-amber-400',
                    'title' => 'Menunggu Pembayaran',
                    'desc' => 'Silakan selesaikan pembayaran Anda sebelum ' . ($pesanan->stock_reserved_until ? \Carbon\Carbon::parse($pesanan->stock_reserved_until)->translatedFormat('d F Y H:i') : '-') . ' WIB agar pesanan tidak dibatalkan.'
                ],
                'failed' => [
                    'bg' => 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-400',
                    'titleColor' => 'text-rose-900 dark:text-white',
                    'iconColor' => 'text-rose-500 dark:text-rose-400',
                    'title' => 'Pembayaran Gagal',
                    'desc' => 'Transaksi Anda gagal atau kedaluwarsa pada ' . ($pesanan->failed_at ? \Carbon\Carbon::parse($pesanan->failed_at)->translatedFormat('d F Y H:i') : '-') . ' WIB.'
                ],
                'cancelled' => [
                    'bg' => 'bg-slate-100 dark:bg-slate-900/20 border-slate-200 dark:border-slate-800/80 text-slate-800 dark:text-slate-400',
                    'titleColor' => 'text-slate-900 dark:text-white',
                    'iconColor' => 'text-slate-500 dark:text-slate-400',
                    'title' => 'Pesanan Dibatalkan',
                    'desc' => 'Pesanan Anda telah dibatalkan pada ' . ($pesanan->cancelled_at ? \Carbon\Carbon::parse($pesanan->cancelled_at)->translatedFormat('d F Y H:i') : '-') . ' WIB.'
                ],
                default => [
                    'bg' => 'bg-slate-100 dark:bg-slate-900/20 border-slate-200 dark:border-slate-800/80 text-slate-800 dark:text-slate-400',
                    'titleColor' => 'text-slate-900 dark:text-white',
                    'iconColor' => 'text-slate-500 dark:text-slate-400',
                    'title' => 'Status Tidak Diketahui',
                    'desc' => 'Status pesanan tidak dapat ditentukan.'
                ],
            };
        @endphp

        <!-- Status Alert Banner -->
        <div class="rounded-3xl border p-5 flex items-start gap-4 mb-8 shadow-sm {{ $statusColorMap['bg'] }}">
            <div class="shrink-0 mt-0.5">
                @if($pesanan->status === \App\Enums\OrderStatus::Paid)
                    <x-heroicon-o-check-circle class="h-6 w-6 {{ $statusColorMap['iconColor'] }}" />
                @elseif($pesanan->status === \App\Enums\OrderStatus::Pending)
                    <x-heroicon-o-exclamation-circle class="h-6 w-6 {{ $statusColorMap['iconColor'] }}" />
                @elseif($pesanan->status === \App\Enums\OrderStatus::Failed)
                    <x-heroicon-o-x-circle class="h-6 w-6 {{ $statusColorMap['iconColor'] }}" />
                @else
                    <x-heroicon-o-minus-circle class="h-6 w-6 {{ $statusColorMap['iconColor'] }}" />
                @endif
            </div>
            <div class="flex-1">
                <h3 class="font-extrabold text-sm tracking-tight mb-1 {{ $statusColorMap['titleColor'] }}">{{ $statusColorMap['title'] }}</h3>
                @if($pesanan->status === \App\Enums\OrderStatus::Pending && $pesanan->stock_reserved_until)
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 mt-1.5" x-data="{
                        expiry: {{ $pesanan->stock_reserved_until->timestamp * 1000 }},
                        remaining: '',
                        timer: null,
                        wasActive: false,
                        updateTimer() {
                            const now = new Date().getTime();
                            const diff = this.expiry - now;
                            if (diff <= 0) {
                                this.remaining = '00:00';
                                clearInterval(this.timer);
                                if (this.wasActive) {
                                    window.location.reload();
                                }
                                return;
                            }
                            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                            this.remaining = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                        },
                        init() {
                            const diff = this.expiry - new Date().getTime();
                            if (diff > 0) {
                                this.wasActive = true;
                            }
                            this.updateTimer();
                            this.timer = setInterval(() => this.updateTimer(), 1000);
                        }
                    }">
                        <div class="flex items-center gap-1.5 bg-amber-500/10 px-2.5 py-1 rounded-xl border border-amber-500/20 text-[10px] font-bold text-amber-600 dark:text-amber-400 w-fit shrink-0">
                            <x-heroicon-o-clock class="w-3.5 h-3.5 animate-pulse text-amber-500" />
                            <span>Sisa Waktu: <span x-text="remaining" class="font-mono text-xs">--:--</span></span>
                        </div>
                        <p class="text-xs leading-relaxed font-medium">Selesaikan pembayaran sebelum {{ \Carbon\Carbon::parse($pesanan->stock_reserved_until)->translatedFormat('d M Y H:i') }} WIB agar pesanan tidak dibatalkan otomatis.</p>
                    </div>
                @else
                    <p class="text-xs leading-relaxed font-medium">{{ $statusColorMap['desc'] }}</p>
                @endif
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <!-- Left Column: Details, Tickets, Merch -->
            <div class="space-y-6">
                <div class="glass-panel rounded-3xl overflow-hidden shadow-lg border border-slate-200 dark:border-white/10">
                    <!-- Event Info Header -->
                    <div class="flex flex-col sm:flex-row gap-5 border-b border-slate-200 dark:border-white/5 p-6">
                        <img src="{{ $pesanan->event->image_path ? Storage::url($pesanan->event->image_path) : asset('img/eobanner.png') }}" alt="{{ $pesanan->event->name }}" class="h-36 w-full sm:w-56 shrink-0 rounded-2xl object-cover border border-slate-200/50 dark:border-white/5 shadow-md">

                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-bold uppercase tracking-widest text-violet-500 dark:text-violet-400">Rincian Acara</span>
                            <h2 class="mb-3 text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight sm:text-2xl mt-1">{{ $pesanan->event->name }}</h2>
                            <div class="space-y-1.5 text-sm text-slate-550 dark:text-slate-400">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-calendar class="h-4 w-4 shrink-0 text-violet-500" />
                                    <span>{{ \Carbon\Carbon::parse($pesanan->event->event_date)->translatedFormat('l, d F Y') }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-clock class="h-4 w-4 shrink-0 text-violet-500" />
                                    <span>{{ \Carbon\Carbon::parse($pesanan->event->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($pesanan->event->end_time)->format('H:i') }} WIB</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-map-pin class="h-4 w-4 shrink-0 text-violet-500" />
                                    <span class="truncate">{{ $pesanan->event->location }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets Section -->
                    @if($pesanan->tickets && $pesanan->tickets->isNotEmpty())
                        <div class="p-6 border-b border-slate-200 dark:border-white/5">
                            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400">
                                <x-heroicon-o-ticket class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                                Tiket Acara ({{ $pesanan->tickets->count() }})
                            </h2>
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach($pesanan->tickets as $tiket)
                                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/20 p-4 transition hover:border-violet-500/30">
                                        <div class="mb-4 flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-extrabold text-slate-900 dark:text-white text-sm leading-snug">{{ $tiket->ticketCategory->name }}</p>
                                                <p class="text-xs text-slate-555 dark:text-slate-400 mt-1">Pemegang: {{ $tiket->holder_name }}</p>
                                            </div>
                                            @if($tiket->is_checked_in)
                                                <span class="inline-flex rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-[9px] font-bold">USED</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/25 px-2 py-0.5 text-[9px] font-bold">VALID</span>
                                            @endif
                                        </div>

                                        <!-- Inline QR Code Toggle for Paid, Unused Tickets -->
                                        @if($pesanan->status === \App\Enums\OrderStatus::Paid)
                                            @if($tiket->is_checked_in)
                                                <div class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-900/30 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-400">
                                                    <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" />
                                                    <span>Sudah dipindai pada {{ \Carbon\Carbon::parse($tiket->checked_in_at)->translatedFormat('d M H:i') }} WIB</span>
                                                </div>
                                            @else
                                                <div x-data="{ showQr: false }" class="mt-4 border-t border-slate-200 dark:border-white/5 border-dashed pt-4">
                                                    <button @click="showQr = !showQr" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-500 dark:hover:text-slate-950 px-3 py-2 text-xs font-bold text-violet-600 dark:text-violet-400 transition duration-200 cursor-pointer">
                                                        <x-heroicon-o-qr-code class="h-4 w-4" />
                                                        <span x-text="showQr ? 'Sembunyikan QR Code' : 'Tampilkan QR Code'"></span>
                                                    </button>
                                                    <div x-show="showQr" x-cloak class="mt-3 flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-955 rounded-xl border border-slate-200 dark:border-white/5">
                                                        <div class="w-40 h-40 bg-white p-2 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center">
                                                            <div id="qr-ticket-{{ $tiket->id }}" class="w-full h-full [&>svg]:w-full [&>svg]:h-full">
                                                                {!! $ticketQrs[$tiket->id] ?? '' !!}
                                                            </div>
                                                        </div>
                                                        <button type="button" onclick="downloadQrCode('qr-ticket-{{ $tiket->id }}', 'ticket-{{ Str::slug($tiket->ticketCategory->name) }}-{{ substr($tiket->id, 0, 8) }}')" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-500 hover:bg-emerald-500 hover:text-white transition duration-200 cursor-pointer">
                                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                                            Unduh Kode QR
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Merchandise Section -->
                    @if($pesanan->merchandise && $pesanan->merchandise->isNotEmpty())
                        <div class="p-6">
                            <h2 class="mb-4 flex items-center gap-2 text-sm font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                <x-heroicon-o-shopping-bag class="h-5 w-5 text-violet-600 dark:text-violet-400" />
                                Suvenir ({{ $pesanan->merchandise->count() }})
                            </h2>
                            <div class="space-y-4">
                                @foreach($pesanan->merchandise as $merch)
                                    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/20 p-4 transition hover:border-violet-500/20">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-600/10 text-violet-600 dark:text-violet-400 border border-violet-500/20">
                                                <x-heroicon-o-shopping-bag class="h-5 w-5" />
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="truncate font-bold text-slate-900 dark:text-white text-sm leading-snug">{{ $merch->merchandiseVariant->item->name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                    Varian: {{ $merch->merchandiseVariant->variant_value }}
                                                    <span class="mx-1.5 text-slate-300 dark:text-white/10">•</span>
                                                    Jumlah: {{ $merch->quantity }} pcs
                                                </p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                @if($merch->is_picked_up)
                                                    <span class="inline-flex rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700 px-2 py-0.5 text-[9px] font-bold">PICKED UP</span>
                                                @else
                                                    <span class="inline-flex rounded-full bg-amber-500/10 text-amber-500 border border-amber-500/20 px-2 py-0.5 text-[9px] font-bold">READY</span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Inline QR Code Toggle for Paid, Uncollected Merchandise -->
                                        @if($pesanan->status === \App\Enums\OrderStatus::Paid)
                                            @if($merch->is_picked_up)
                                                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-900/30 flex items-center gap-2 text-xs text-emerald-800 dark:text-emerald-400">
                                                    <x-heroicon-o-check-circle class="h-4 w-4 shrink-0" />
                                                    <span>Suvenir diambil pada {{ \Carbon\Carbon::parse($merch->picked_up_at)->translatedFormat('d M H:i') }} WIB</span>
                                                </div>
                                            @else
                                                <div x-data="{ showQr: false }" class="border-t border-slate-200 dark:border-white/5 border-dashed pt-4">
                                                    <button @click="showQr = !showQr" class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-500 dark:hover:text-slate-955 px-3 py-2 text-xs font-bold text-violet-600 dark:text-violet-400 transition duration-200 cursor-pointer">
                                                        <x-heroicon-o-qr-code class="h-4 w-4" />
                                                        <span x-text="showQr ? 'Sembunyikan QR Code Pengambilan' : 'Tampilkan QR Code Pengambilan'"></span>
                                                    </button>
                                                    <div x-show="showQr" x-cloak class="mt-3 flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-white/5">
                                                        <div class="w-40 h-40 bg-white p-2 rounded-lg border border-slate-200 dark:border-slate-800 flex items-center justify-center">
                                                            <div id="qr-merch-{{ $merch->id }}" class="w-full h-full [&>svg]:w-full [&>svg]:h-full">
                                                                {!! $merchQrs[$merch->id] ?? '' !!}
                                                            </div>
                                                        </div>
                                                        <button type="button" onclick="downloadQrCode('qr-merch-{{ $merch->id }}', 'merch-{{ Str::slug($merch->merchandiseVariant->item->name) }}-{{ substr($merch->id, 0, 8) }}')" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-500 hover:bg-emerald-500 hover:text-white transition duration-200 cursor-pointer">
                                                            <x-heroicon-o-arrow-down-tray class="w-3.5 h-3.5" />
                                                            Unduh Kode QR
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Sidebar (Summary & Payments) -->
            <div class="space-y-6">
                <div class="glass-panel rounded-3xl p-6 shadow-lg border border-slate-200 dark:border-white/10">
                    <h3 class="mb-4 text-sm font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400">Ringkasan Pembayaran</h3>

                    @php
                        $subtotalTiket = $pesanan->tickets->sum('unit_price');
                        $subtotalMerch = $pesanan->merchandise->sum(fn($m) => $m->unit_price * $m->quantity);
                        $subtotal = $subtotalTiket + $subtotalMerch;
                        $biayaLayanan = $subtotal > 0 ? 15000 : 0;
                        $pajak = (int) round($subtotal * 0.1);
                    @endphp

                    <div class="space-y-3 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                        <div class="flex justify-between items-center">
                            <span>Status</span>
                            @if($pesanan->status === \App\Enums\OrderStatus::Pending && $pesanan->stock_reserved_until)
                                <div class="flex items-center gap-1.5" x-data="{
                                    expiry: {{ $pesanan->stock_reserved_until->timestamp * 1000 }},
                                    remaining: '',
                                    timer: null,
                                    wasActive: false,
                                    updateTimer() {
                                        const now = new Date().getTime();
                                        const diff = this.expiry - now;
                                        if (diff <= 0) {
                                            this.remaining = '00:00';
                                            clearInterval(this.timer);
                                            if (this.wasActive) {
                                                window.location.reload();
                                            }
                                            return;
                                        }
                                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                                        this.remaining = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                                    },
                                    init() {
                                        const diff = this.expiry - new Date().getTime();
                                        if (diff > 0) {
                                            this.wasActive = true;
                                        }
                                        this.updateTimer();
                                        this.timer = setInterval(() => this.updateTimer(), 1000);
                                    }
                                }">
                                    <span class="font-bold text-amber-500 uppercase tracking-wider text-[11px]">{{ $pesanan->status->label() }}</span>
                                    <span class="px-1.5 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-[11px] font-mono font-bold text-amber-600 dark:text-amber-400" x-text="remaining">--:--</span>
                                </div>
                            @else
                                <span class="font-bold text-slate-955 dark:text-white uppercase tracking-wider text-[10px]">{{ $pesanan->status->label() }}</span>
                            @endif
                        </div>
                        @if($pesanan->payment_type)
                            <div class="flex justify-between">
                                <span>Metode</span>
                                <span class="font-bold text-slate-955 dark:text-white uppercase">{{ str_replace('_', ' ', $pesanan->payment_type) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>ID Pesanan</span>
                            <span class="font-mono text-[11px] text-slate-955 dark:text-white select-all">{{ $pesanan->midtrans_order_id ?? $pesanan->id }}</span>
                        </div>
                        @if($pesanan->midtrans_transaction_id)
                            <div class="flex justify-between">
                                <span>ID Transaksi</span>
                                <span class="font-mono text-[11px] text-slate-955 dark:text-white select-all">{{ $pesanan->midtrans_transaction_id }}</span>
                            </div>
                        @endif
                    </div>

                    @if($pesanan->status->value === 'paid')
                        @php
                            $ticketsCount = $pesanan->tickets->count();
                            $ticketsCheckedIn = $pesanan->tickets->where('is_checked_in', true)->count();
                            $ticketsPercent = $ticketsCount > 0 ? ($ticketsCheckedIn / $ticketsCount) * 100 : 0;

                            $merchCount = $pesanan->merchandise->sum('quantity');
                            $merchPickedUp = $pesanan->merchandise->where('is_picked_up', true)->sum('quantity');
                            $merchPercent = $merchCount > 0 ? ($merchPickedUp / $merchCount) * 100 : 0;
                        @endphp

                        @if($ticketsCount > 0 || $merchCount > 0)
                            <div class="mt-5 pt-5 border-t border-dashed border-slate-200 dark:border-white/10 space-y-4">
                                <h4 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Status Pemindaian</h4>
                                
                                @if($ticketsCount > 0)
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-medium text-slate-500 dark:text-slate-400">Tiket Masuk</span>
                                            <span class="font-extrabold text-slate-900 dark:text-white">{{ $ticketsCheckedIn }}/{{ $ticketsCount }} Tiket</span>
                                        </div>
                                        <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-violet-500 to-indigo-600 rounded-full transition-all duration-500" style="width: {{ $ticketsPercent }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                @if($merchCount > 0)
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="font-medium text-slate-500 dark:text-slate-400">Pengambilan Suvenir</span>
                                            <span class="font-extrabold text-slate-900 dark:text-white">{{ $merchPickedUp }}/{{ $merchCount }} Produk</span>
                                        </div>
                                        <div class="h-2 w-full bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-amber-400 to-orange-500 rounded-full transition-all duration-500" style="width: {{ $merchPercent }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    <div class="my-5 h-px border-t border-dashed border-slate-200 dark:border-white/10"></div>

                    <div class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
                        @if($subtotalTiket > 0)
                            <div class="flex justify-between">
                                <span>Total Tiket</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($subtotalTiket, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($subtotalMerch > 0)
                            <div class="flex justify-between">
                                <span>Total Suvenir</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($subtotalMerch, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($subtotal > 0)
                            <div class="flex justify-between">
                                <span>Pajak (10%)</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Biaya Layanan</span>
                                <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($biayaLayanan, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 rounded-2xl bg-violet-600/5 dark:bg-violet-500/5 p-4 border border-violet-500/10">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Total Bayar</span>
                            <span class="text-base font-extrabold text-violet-600 dark:text-violet-400">Rp {{ number_format($pesanan->total_amount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Snap Payment Retry Modal Integration -->
                    @if($canRetryPayment)
                        <div class="mt-5" x-data="{
                            isLoading: false,
                            pay() {
                                if (this.isLoading) return;
                                this.isLoading = true;
                                const snapToken = '{{ $pesanan->snap_token }}';
                                const clientKey = '{{ config('services.midtrans.client_key') }}';
                                const snapSrc = '{{ config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}';
                                
                                const openSnap = () => {
                                    if (typeof snap !== 'undefined') {
                                        snap.pay(snapToken, {
                                            onSuccess: (result) => window.location.reload(),
                                            onPending: (result) => window.location.reload(),
                                            onError: (result) => window.location.reload()
                                        });
                                        this.isLoading = false;
                                    }
                                };

                                if (typeof snap !== 'undefined') {
                                    openSnap();
                                } else {
                                    let script = document.querySelector('script[src*=\'/snap/snap.js\']');
                                    if (!script) {
                                        script = document.createElement('script');
                                        script.src = snapSrc;
                                        script.setAttribute('data-client-key', clientKey);
                                        script.onload = () => openSnap();
                                        script.onerror = () => {
                                            this.isLoading = false;
                                            alert('Gagal memuat skrip pembayaran Midtrans. Silakan muat ulang halaman.');
                                        };
                                        document.head.appendChild(script);
                                    } else {
                                        const interval = setInterval(() => {
                                            if (typeof snap !== 'undefined') {
                                                clearInterval(interval);
                                                openSnap();
                                            }
                                        }, 100);
                                    }
                                }
                            }
                        }">
                            <button @click="pay()" :disabled="isLoading" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-violet-600 hover:bg-violet-750 px-6 py-3 text-sm font-bold text-white transition duration-200 shadow-lg shadow-violet-600/30 cursor-pointer disabled:opacity-50">
                                <x-heroicon-o-credit-card class="h-4 w-4" />
                                <span x-text="isLoading ? 'Memuat Pembayaran...' : 'Bayar Sekarang'">Bayar Sekarang</span>
                            </button>
                        </div>
                    @endif

                    <!-- Retry Payment Token Request button if user can retry (limit 3 attempts) -->
                    @if($pesanan->canRetryPayment())
                        <div class="mt-3">
                            <form action="{{ route('pesanan.retry', $pesanan->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl border border-violet-600/40 bg-transparent hover:bg-violet-600/5 px-6 py-3 text-xs font-bold text-violet-600 dark:text-violet-400 transition duration-200 cursor-pointer">
                                    <x-heroicon-o-arrow-path class="h-3.5 w-3.5" />
                                    Minta Token Pembayaran Baru (Sisa: {{ 3 - $pesanan->snap_retry_count }})
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($pesanan->status === \App\Enums\OrderStatus::Paid)
                        <a href="{{ route('pesanan.invoice', $pesanan->id) }}" class="mt-5 flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 dark:border-white/5 bg-white dark:bg-slate-900 py-3 text-xs font-bold text-slate-700 dark:text-slate-350 transition hover:bg-slate-50 dark:hover:bg-slate-800 hover:border-violet-600 hover:text-violet-600">
                            <x-heroicon-o-document-arrow-down class="h-4 w-4 text-slate-500 dark:text-slate-400" />
                            Unduh Invoice
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
