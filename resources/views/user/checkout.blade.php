<x-guest-layout width="max-w-7xl" :center="false" :card="false" :backUrl="route('events.show', $event->slug)" backText="Batal & Kembali ke Event">
    <div class="py-6" x-data="{
        namaPemesan: '{{ old('nama_lengkap', Auth::user()->name) }}',
        copyNameToHolders() {
            document.querySelectorAll('.holder-name-input').forEach(el => {
                el.value = this.namaPemesan;
                el.dispatchEvent(new Event('input'));
            });
        }
    }">
        <div class="mb-8">
            <span class="text-[10px] font-bold uppercase tracking-widest text-violet-500 dark:text-violet-400">Proses Pembayaran</span>
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white mt-1">Selesaikan Pesanan Anda</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Silakan isi data pemesan dan data pemegang tiket dengan benar.</p>
        </div>

        <div class="grid gap-6 sm:gap-8 lg:grid-cols-[minmax(0,1fr)_420px]">
            <!-- Left Column: Forms -->
            <div class="space-y-6">
                @if(session('error'))
                    <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-900/50 p-4 text-sm text-rose-800 dark:text-rose-400 shadow-sm">
                        <x-heroicon-o-exclamation-circle class="h-5 w-5 shrink-0 text-rose-500" />
                        <div>
                            <span class="font-semibold">Kesalahan:</span> {{ session('error') }}
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 dark:bg-rose-950/30 dark:border-rose-900/50 p-4 text-sm text-rose-800 dark:text-rose-400 shadow-sm">
                        <x-heroicon-o-x-circle class="h-5 w-5 shrink-0 text-rose-500" />
                        <div>
                            <span class="font-semibold">Mohon periksa kembali input Anda:</span>
                            <ul class="list-disc list-inside mt-1 text-xs space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Form Card -->
                <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}">
                    @csrf
                    <input type="hidden" name="event_id" value="{{ $event->id }}">

                    {{-- Hidden Ticket and Merchandise inputs --}}
                    @foreach($tikets as $tiket)
                        <input type="hidden" name="tickets[{{ $tiket->id }}]" value="{{ $tiket->qty }}">
                    @endforeach
                    @if(isset($merchandises))
                        @foreach($merchandises as $merch)
                            <input type="hidden" name="merchandise[{{ $merch->id }}]" value="{{ $merch->qty }}">
                        @endforeach
                    @endif

                    <div class="space-y-6">
                        <!-- Buyer Information (Glass Panel) -->
                        <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-lg transition-all duration-300">
                            <div class="mb-6 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600/10 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                                    <x-heroicon-o-user class="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Data Pemesan</h2>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Elektronik tiket akan dikirimkan ke surel ini.</p>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label for="nama_lengkap" class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-2 block">Nama Lengkap</label>
                                    <input id="nama_lengkap" type="text" name="nama_lengkap" x-model="namaPemesan" readonly class="w-full rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-slate-900/30 text-slate-500 dark:text-slate-400 px-4 py-3 text-sm outline-none cursor-not-allowed">
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <div>
                                        <label for="email" class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-2 block">Alamat Surel</label>
                                        <input id="email" type="email" name="email" value="{{ old('email', Auth::user()->email) }}" readonly class="w-full rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-100 dark:bg-slate-900/30 text-slate-500 dark:text-slate-400 px-4 py-3 text-sm outline-none cursor-not-allowed">
                                    </div>

                                    <div>
                                        <label for="no_telepon" class="text-xs font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400 mb-2 block">Nomor Telepon</label>
                                        <input id="no_telepon" type="tel" name="no_telepon" value="{{ old('no_telepon') }}" required placeholder="0812xxxx" class="w-full rounded-2xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-slate-900/60 px-4 py-3 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Holders (Glass Panel) -->
                        <div class="glass-panel rounded-3xl p-6 sm:p-8 shadow-lg transition-all duration-300">
                            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500">
                                        <x-heroicon-o-ticket class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Informasi Pemegang Tiket</h2>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">Tentukan nama pemegang untuk setiap tiket.</p>
                                    </div>
                                </div>

                                <div>
                                    <button type="button" @click="copyNameToHolders()" class="inline-flex items-center gap-1.5 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 px-3 py-1.5 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:bg-violet-600 hover:text-white dark:hover:bg-violet-500 dark:hover:text-slate-950 transition-all duration-250">
                                        <x-heroicon-o-document-duplicate class="w-3.5 h-3.5" />
                                        Salin Nama Pemesan
                                    </button>
                                </div>
                            </div>

                            <div class="space-y-6">
                                @foreach($tikets as $tiket)
                                    <div class="border-b border-slate-100 dark:border-white/5 pb-6 last:border-none last:pb-0">
                                        <div class="flex items-center justify-between mb-4">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-0.5 text-xs font-medium text-emerald-500">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Kategori: {{ $tiket->nama }}
                                            </span>
                                            <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">Batas: {{ $tiket->max_per_user }} tiket</span>
                                        </div>

                                        <div class="space-y-3">
                                            @for($i = 0; $i < $tiket->qty; $i++)
                                                <div>
                                                    <label class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1 block">Pemegang Tiket #{{ $i + 1 }}</label>
                                                    <input type="text"
                                                           name="holder_names[{{ $tiket->id }}][]"
                                                           value="{{ old('holder_names.'.$tiket->id.'.'.$i, ($i === 0 ? old('nama_lengkap', Auth::user()->name) : '')) }}"
                                                           required
                                                           placeholder="Nama Pemegang Tiket {{ $i + 1 }}"
                                                           class="holder-name-input w-full rounded-xl border border-slate-200 dark:border-white/5 bg-slate-50/55 dark:bg-slate-950/40 px-4 py-2.5 text-sm outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/5">
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Right Column: Sticky Summary -->
            <div class="lg:sticky lg:top-24 lg:self-start space-y-6">
                <div class="glass-panel rounded-3xl p-6 shadow-lg transition-all duration-300">
                    <div class="mb-5">
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ringkasan Pesanan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Rincian pembelian Anda.</p>
                    </div>

                    <!-- Event Quick Detail -->
                    <div class="flex gap-3 items-start bg-slate-100/50 dark:bg-slate-950/40 rounded-2xl p-3 border border-slate-200/50 dark:border-white/5 mb-5">
                        @if($event->image_path)
                            <img src="{{ Storage::url($event->image_path) }}" alt="{{ $event->name }}" class="w-16 h-16 rounded-xl object-cover">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-violet-600/10 text-violet-600 flex items-center justify-center font-bold text-lg">
                                JF
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $event->name }}</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 flex items-center gap-1">
                                <x-heroicon-o-calendar class="w-3.5 h-3.5 text-violet-500" />
                                {{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('d M Y') }}
                            </p>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                                <x-heroicon-o-map-pin class="w-3.5 h-3.5 text-violet-500" />
                                <span class="truncate">{{ $event->location }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Tickets Itemized -->
                    <div class="space-y-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 block">Tiket</span>
                        @foreach($tikets as $tiket)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-slate-200/50 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/20">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $tiket->nama }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">x{{ $tiket->qty }} Tiket</p>
                                </div>
                                <p class="text-sm font-semibold text-violet-600 dark:text-violet-400 shrink-0">Rp {{ number_format($tiket->harga * $tiket->qty, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    @if(isset($merchandises) && $merchandises->isNotEmpty())
                        <div class="my-5 h-px border-t border-dashed border-slate-200 dark:border-white/10"></div>

                        <!-- Merchandise Itemized -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400 block">Merchandise</span>
                            @foreach($merchandises as $merch)
                                <div class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-slate-200/50 dark:border-white/5 bg-slate-50/50 dark:bg-slate-950/20">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $merch->nama }}</p>
                                        <p class="text-xs text-slate-600 dark:text-slate-400">x{{ $merch->qty }} • Varian: {{ $merch->varian }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-violet-600 dark:text-violet-400 shrink-0">Rp {{ number_format($merch->harga * $merch->qty, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="my-5 h-px border-t border-dashed border-slate-200 dark:border-white/10"></div>

                    <!-- Pricing Breakdown -->
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between gap-3 text-slate-600 dark:text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-slate-600 dark:text-slate-400">
                            <span>Biaya Layanan</span>
                            <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($biaya_layanan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-slate-600 dark:text-slate-400">
                            <span>Pajak (10%)</span>
                            <span class="font-medium text-slate-900 dark:text-white">Rp {{ number_format($pajak, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-violet-600/5 dark:bg-violet-500/5 p-4 border border-violet-500/10">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-200">Total Bayar</span>
                            <span class="text-lg font-black text-violet-600 dark:text-violet-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <button type="submit" form="checkoutForm" class="mt-5 inline-flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-violet-600 text-white dark:bg-violet-500 dark:text-slate-950 px-4 text-sm font-bold hover:bg-violet-700 dark:hover:bg-violet-400 transition-all duration-200 shadow-lg shadow-violet-600/10 cursor-pointer">
                        Bayar Sekarang
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </button>

                    <p class="mt-4 flex items-center justify-center gap-2 text-center text-xs text-slate-600 dark:text-slate-400">
                        <x-heroicon-o-lock-closed class="h-3.5 w-3.5 text-violet-500" />
                        Pembayaran aman &amp; instan via Midtrans Snap
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
