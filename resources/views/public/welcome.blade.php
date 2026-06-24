<x-public-layout title="Pengalaman Acara Masa Depan">
    <div class="px-4 py-6 md:px-6 md:py-10">
        <div class="max-w-7xl w-full mx-auto lg:space-y-30 sm:space-y-23 space-y-16">
            <div class="flex flex-col gap-6 sm:gap-8">

                <!-- Hero Text Section -->
                <section data-reveal data-reveal-delay="0">
                    <div class="flex flex-col items-center text-center p-6 sm:p-12 lg:p-12 pb-0 sm:pb-0 lg:pb-0">
                        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-slate-900 dark:text-white sm:text-5xl lg:text-7xl opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98]" data-reveal data-reveal-delay="140">
                            Pengalaman Acara <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-400 via-violet-400 to-sky-400">
                                Masa Depan
                            </span>
                        </h1>
                        <p class="mt-6 max-w-xl text-sm leading-relaxed text-slate-500 dark:text-slate-300 sm:text-base opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98]" data-reveal data-reveal-delay="220">
                            Temukan konser, festival, hingga seminar premium dalam satu tempat dengan alur pemesanan yang cepat, rapi, dan nyaman.
                        </p>
                        <div class="mt-8 mb-8 flex flex-wrap justify-center items-center gap-4 opacity-0 blur-sm transition-all duration-700 ease-out translate-y-6 scale-[0.98]" data-reveal data-reveal-delay="300">
                            <a href="{{ route('events.index') }}" data-link class="inline-flex h-12 items-center justify-center rounded-xl bg-violet-600 px-6 text-sm font-bold text-white shadow-lg shadow-violet-600/25 transition-all hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-violet-700/30">
                                Jelajahi Acara
                            </a>
                            @auth
                                <a href="{{ route('pesanan.index') }}" data-link class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 hover:bg-slate-50 dark:hover:bg-white/10 px-6 text-sm font-bold text-slate-700 dark:text-white transition-all">
                                    Lihat Pesanan Saya
                                </a>
                            @else
                                <a href="{{ route('register') }}" data-link class="inline-flex h-12 items-center justify-center rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-white/5 hover:bg-slate-50 dark:hover:bg-white/10 px-6 text-sm font-bold text-slate-700 dark:text-white transition-all">
                                    Buat Akun
                                </a>
                            @endauth
                        </div>
                    </div>
                </section>

                <!-- Hero Banner Section -->
                <section class="overflow-hidden rounded-[2rem] border border-slate-800 dark:border-white/10 shadow-xl" data-reveal data-reveal-delay="50">
                    <img src="{{ asset('img/HeroBanner.png') }}" alt="JoinFest hero" class="w-full h-[400px] sm:h-[500px] object-cover transition-transform duration-300 will-change-transform" data-parallax="0.07">
                </section>

            </div>

            <!-- Popular Events Section -->
            <section data-reveal data-reveal-delay="110" class="lg:space-y-12 space-y-8">

                <!-- Judul -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center gap-2 mb-3">
                        <span class="h-px w-8 bg-violet-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">Populer</span>
                        <span class="h-px w-8 bg-violet-500"></span>
                    </div>
                    <h2 class="lg:text-4xl text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Acara Populer
                    </h2>
                </div>

                <!-- Katalog -->
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($popularEvents as $event)
                        <x-event-card :event="$event" :loop-index="$loop->index" />
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900/40 rounded-[2rem] border border-slate-200 dark:border-white/10 shadow-sm">
                            Belum ada acara publik yang tersedia saat ini.
                        </div>
                    @endforelse
                </div>

                <!-- Lihat Semua  -->
                <div class="flex justify-center">
                    <a href="{{ route('events.index') }}" data-link 
                    class="group inline-flex items-center gap-2 text-sm font-bold text-slate-500 dark:text-slate-400 transition hover:text-slate-800 dark:hover:text-white">
                        Lihat Semua
                        <x-heroicon-o-arrow-right class="h-4 w-4 transition-transform group-hover:translate-x-1" />
                    </a>
                </div>

            </section>

            <!-- Categories Section (Dynamic) -->
            <!-- Judul -->
                <div class="text-center">
                    <div class="inline-flex items-center justify-center gap-2 mb-3">
                        <span class="h-px w-8 bg-violet-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">Eksplorasi</span>
                        <span class="h-px w-8 bg-violet-500"></span>
                    </div>
                    <h2 class="lg:text-4xl text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Kategori Acara
                    </h2>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
                    @php
                        $colorMaps = [
                            'violet' => [
                                'gradient' => 'from-slate-950/90 via-violet-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(139,92,246,0.15)] hover:border-violet-500/30',
                            ],
                            'sky' => [
                                'gradient' => 'from-slate-950/90 via-sky-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(14,165,233,0.15)] hover:border-sky-500/30',
                            ],
                            'emerald' => [
                                'gradient' => 'from-slate-950/90 via-emerald-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(16,185,129,0.15)] hover:border-emerald-500/30',
                            ],
                            'rose' => [
                                'gradient' => 'from-slate-950/90 via-rose-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(244,63,94,0.15)] hover:border-rose-500/30',
                            ],
                            'amber' => [
                                'gradient' => 'from-slate-950/90 via-amber-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(245,158,11,0.15)] hover:border-amber-500/30',
                            ],
                            'fuchsia' => [
                                'gradient' => 'from-slate-950/90 via-fuchsia-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(217,70,239,0.15)] hover:border-fuchsia-500/30',
                            ],
                            'cyan' => [
                                'gradient' => 'from-slate-950/90 via-cyan-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(6,182,212,0.15)] hover:border-cyan-500/30',
                            ],
                            'indigo' => [
                                'gradient' => 'from-slate-950/90 via-indigo-950/20 to-transparent',
                                'glow' => 'hover:shadow-[0_0_20px_rgba(99,102,241,0.15)] hover:border-indigo-500/30',
                            ],
                        ];
                    @endphp

                    @forelse ($eventCategories as $category)
                        @php
                            $color = $colorMaps[$category->color] ?? $colorMaps['violet'];
                            $isLarge = $loop->first || $loop->last;
                        @endphp
                        <a href="{{ route('events.index', ['category' => $category->slug]) }}" data-link
                           class="group relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900 flex flex-col justify-end transition-all duration-300 hover:-translate-y-1 opacity-0 blur-sm translate-y-6 scale-[0.98] {{ $color['glow'] }} {{ $isLarge ? 'sm:col-span-4 col-span-2 min-h-80' : 'col-span-2 min-h-80' }}"
                           data-reveal
                           data-reveal-delay="{{ $loop->index * 70 + 120 }}">
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="absolute inset-0 h-full w-full object-cover opacity-80 transition duration-500 group-hover:scale-110 group-hover:opacity-95">
                            <div class="absolute inset-0 bg-gradient-to-t {{ $color['gradient'] }}"></div>
                            <h3 class="relative z-10 font-extrabold tracking-tight text-white transition-transform duration-300 group-hover:translate-y-0 translate-y-0.5 {{ $isLarge ? 'text-lg sm:text-2xl p-8' : 'text-sm sm:text-base p-5' }}">{{ $category->name }}</h3>
                        </a>
                    @empty
                        <div class="col-span-full py-12 text-center text-slate-500 dark:text-slate-400">
                            Belum ada kategori yang dikonfigurasi.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- How It Works Section -->
            <section data-reveal data-reveal-delay="100" class="lg:space-y-12 space-y-8">
                <div class="mb-5 flex flex-col items-center text-center">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="h-px w-8 bg-violet-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-violet-650 dark:text-violet-400">Proses</span>
                        <span class="h-px w-8 bg-violet-500"></span>
                    </div>
                    <h2 class="lg:text-4xl text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Cara Pemesanan Tiket</h2>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 p-8 shadow-sm transition-all duration-300 hover:border-violet-500/20">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-500/10 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 mb-6">
                            <x-heroicon-o-magnifying-glass class="w-6 h-6" />
                        </div>
                        <div class="absolute right-6 top-6 text-6xl font-black text-slate-100 dark:text-slate-800/20 select-none">01</div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">1. Pilih Acara</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">Cari konser, seminar, atau festival favoritmu dari daftar acara terkurasi.</p>
                    </div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 p-8 shadow-sm transition-all duration-300 hover:border-sky-500/20">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 mb-6">
                            <x-heroicon-o-credit-card class="w-6 h-6" />
                        </div>
                        <div class="absolute right-6 top-6 text-6xl font-black text-slate-100 dark:text-slate-800/20 select-none">02</div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">2. Bayar Tiket</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">Pilih kategori tiket, isi detail pesanan, dan bayar aman dengan Midtrans.</p>
                    </div>

                    <div class="relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 p-8 shadow-sm transition-all duration-300 hover:border-emerald-500/20">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 mb-6">
                            <x-heroicon-o-qr-code class="w-6 h-6" />
                        </div>
                        <div class="absolute right-6 top-6 text-6xl font-black text-slate-100 dark:text-slate-800/20 select-none">03</div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">3. Tunjukkan QR</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">Terima tiket dalam bentuk kode QR, lalu pindai di lokasi masuk.</p>
                    </div>
                </div>
            </section>

            <!-- Stats / Social Proof Bar Section -->
            <section data-reveal data-reveal-delay="110">
                <div class="rounded-[2.5rem] border border-slate-200 dark:border-white/10 bg-white/70 dark:bg-slate-900/30 backdrop-blur-xl p-8 lg:p-12 shadow-md">
                    <div class="grid grid-cols-2 gap-8 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-white/5">
                        <div class="text-center pt-4 md:pt-0">
                            <div class="text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-violet-600 to-indigo-650 dark:from-violet-400 dark:to-indigo-400">120+</div>
                            <div class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-550">Acara Tersedia</div>
                        </div>
                        <div class="text-center pt-4 md:pt-0 md:pl-4">
                            <div class="text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-teal-600 dark:from-sky-400 dark:to-teal-400">15.4K+</div>
                            <div class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-550">Tiket Terjual</div>
                        </div>
                        <div class="text-center pt-4 md:pt-0 md:pl-4">
                            <div class="text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-605 dark:from-emerald-400 dark:to-teal-400">45+</div>
                            <div class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-550">Penyelenggara Terdaftar</div>
                        </div>
                        <div class="text-center pt-4 md:pt-0 md:pl-4">
                            <div class="text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-rose-600 to-pink-600 dark:from-rose-400 dark:to-pink-400">99.8%</div>
                            <div class="mt-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-550">Tingkat Kepuasan</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="lg:space-y-12 space-y-8 rounded-[3rem] border border-slate-200 dark:border-white/10 bg-white/50 dark:bg-slate-900/30 backdrop-blur-xl p-6 lg:p-12 shadow-sm" data-reveal data-reveal-delay="130">
                <div class="grid gap-8 lg:grid-cols-[1.2fr_1fr]">
                    <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-violet-600 to-indigo-700 p-8 text-white shadow-2xl opacity-0 blur-sm translate-y-6 scale-[0.98]" data-reveal data-reveal-delay="170">
                        <div class="absolute -right-12 -top-12 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                        <h3 class="text-3xl font-extrabold tracking-tight">Pengalaman Tiket Modern</h3>
                        <p class="mt-4 max-w-md text-sm leading-relaxed text-violet-100">Semua fitur penting untuk transaksi acara modern: proses cepat, informasi jelas, dan dukungan pengguna yang responsif.</p>
                        <div class="mt-8 grid gap-4 text-sm font-medium text-white">
                            <p class="inline-flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"><x-heroicon-s-check class="h-4 w-4" /></span>Tiket elektronik langsung tersedia</p>
                            <p class="inline-flex items-center gap-3"><span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20"><x-heroicon-s-check class="h-4 w-4" /></span>Status pesanan <i>real-time</i></p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 content-center">
                        @php
                            $features = [
                                ['title' => 'Pemesanan Instan', 'desc' => 'Checkout singkat dengan alur yang jelas.', 'icon' => 'bolt'],
                                ['title' => 'Tiket Elektronik Aman', 'desc' => 'Tiket digital tersimpan rapi di akun.', 'icon' => 'shield-check'],
                                ['title' => 'Pembayaran Fleksibel', 'desc' => 'Metode pembayaran modern dan tepercaya.', 'icon' => 'credit-card'],
                                ['title' => 'Dukungan 24/7', 'desc' => 'Tim support siap membantu kapan saja.', 'icon' => 'chat-bubble-left-right'],
                            ];
                        @endphp

                        @foreach ($features as $feature)
                            <div class="rounded-[1.5rem] border border-slate-200 dark:border-white/5 bg-slate-50 dark:bg-white/5 p-5 shadow-sm opacity-0 blur-sm translate-y-6 scale-[0.98] transition-all duration-700 ease-out" data-reveal data-reveal-delay="{{ $loop->index * 80 + 230 }}">
                                <x-dynamic-component :component="'heroicon-o-' . $feature['icon']" class="h-6 w-6 text-violet-600 dark:text-violet-400" />
                                <h3 class="mt-4 text-sm font-bold text-slate-900 dark:text-white">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-xs leading-relaxed text-slate-550 dark:text-slate-400">{{ $feature['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <!-- CTA Banner Section -->
            <section data-reveal data-reveal-delay="120">
                <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-tr from-violet-700 via-violet-600 to-indigo-800 text-white shadow-2xl p-8 sm:p-12 lg:p-16">
                    <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-white/10 blur-3xl"></div>
                    <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-sky-500/20 blur-3xl"></div>

                    <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                        <div class="max-w-2xl text-center lg:text-left">
                            <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Ingin Menyelenggarakan Acara Sendiri?</h2>
                            <p class="mt-4 text-violet-100 text-sm sm:text-base leading-relaxed">
                                Buat acara pertamamu sekarang! Kelola penjualan tiket, suvenir, <i>scanner gate</i>, hingga pencairan dana penjualan dengan mudah dan transparan.
                            </p>
                        </div>
                        <div class="shrink-0">
                            <a href="{{ route('register') }}" data-link class="inline-flex h-14 items-center justify-center rounded-2xl bg-white px-8 text-sm font-bold text-violet-700 shadow-xl transition-all hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-white/20">
                                Daftar Sebagai Penyelenggara
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-public-layout>
