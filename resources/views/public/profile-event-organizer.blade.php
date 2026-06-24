<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organizer->nama }} - JoinFest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 font-[Plus_Jakarta_Sans,sans-serif] text-slate-900">
    <nav data-site-header data-scrolled="false" class="sticky top-0 z-50 flex h-14 items-center justify-between border-b border-slate-200 bg-white px-4 shadow-sm transition-all duration-300 md:px-8 data-[scrolled=true]:border-violet-200 data-[scrolled=true]:bg-white/95 data-[scrolled=true]:shadow-[0_12px_30px_rgba(15,23,42,0.08)]">
        <div class="flex items-center gap-6">
            <a href="{{ url('/') }}" class="text-lg font-extrabold tracking-tight text-violet-600">JoinFest</a>
            <div class="hidden items-center gap-5 md:flex">
                <a href="{{ url('/discover') }}" class="text-sm font-medium text-slate-500 transition hover:text-violet-600">Discover</a>
                <a href="{{ url('/calendar') }}" class="text-sm font-medium text-slate-500 transition hover:text-violet-600">Calendar</a>
                <a href="{{ route('pesanan.index') }}" class="text-sm font-medium text-slate-500 transition hover:text-violet-600">Orders</a>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/notifications') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </a>
            <a href="{{ url('/profile') }}" class="inline-flex h-9 w-9 items-center justify-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </a>
        </div>
    </nav>

    <section class="relative h-64 overflow-hidden bg-slate-900 opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="0">
        <img src="{{ asset('img/eobanner.png') }}" alt="Organizer banner" class="h-full w-full object-cover opacity-80">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/50 via-transparent to-transparent"></div>
    </section>

    <section class="border-b border-slate-200 bg-white opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="80">
        <div class="mx-auto max-w-5xl px-4 pb-6 md:px-8">
            <div class="-mt-12 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <div class="inline-flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-4 border-white bg-slate-100 shadow-lg">
                        <img src="{{ asset('img/EOLogo.png') }}" alt="{{ $organizer->nama }}" class="h-full w-full object-cover">
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $organizer->nama }}</h1>
                        @if($organizer->terverifikasi)
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </span>
                        @endif
                    </div>
                    <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-600">{{ $organizer->bio }}</p>
                </div>

                <div class="flex gap-3">
                    <form action="{{ route('organizer.ikuti', $organizer->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white transition hover:bg-violet-700">Ikuti</button>
                    </form>
                    <a href="{{ route('organizer.hubungi', $organizer->id) }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:border-violet-600 hover:text-violet-600">Hubungi</a>
                </div>
            </div>
        </div>
    </section>

    @php $activeTab = request('tab', 'aktif'); @endphp

    <main class="mx-auto max-w-5xl space-y-6 px-4 py-8 md:px-8" data-reveal data-reveal-delay="120">
        <div class="grid gap-4 sm:grid-cols-3 opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="160">
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-3xl font-extrabold text-violet-600">{{ $organizer->event_aktif }}</div>
                <div class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Event Aktif</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="text-3xl font-extrabold text-slate-900">{{ number_format($organizer->pengikut / 1000, 1) }}k</div>
                <div class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Pengikut</div>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-2 text-3xl font-extrabold text-slate-900">
                    {{ number_format($organizer->rating, 1) }}
                    <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </div>
                <div class="mt-1 text-xs font-semibold uppercase tracking-[0.08em] text-slate-500">Rating Rata-rata</div>
            </div>
        </div>

        <div class="flex overflow-hidden rounded-2xl border border-slate-200 bg-white p-1 shadow-sm opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="220">
            <a href="{{ route('organizer.show', [$organizer->id, 'tab' => 'aktif']) }}" class="flex-1 rounded-xl px-4 py-3 text-center text-sm font-semibold transition {{ $activeTab === 'aktif' ? 'bg-violet-600 text-white' : 'text-slate-500 hover:text-violet-600' }}">Event Aktif</a>
            <a href="{{ route('organizer.show', [$organizer->id, 'tab' => 'lampau']) }}" class="flex-1 rounded-xl px-4 py-3 text-center text-sm font-semibold transition {{ $activeTab === 'lampau' ? 'bg-violet-600 text-white' : 'text-slate-500 hover:text-violet-600' }}">Event Lampau</a>
        </div>

        <section class="opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="280">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Event</h2>
                <p class="text-sm text-slate-500">Jelajahi event yang tersedia dari organizer ini.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse(($events ?? []) as $event)
                    <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-[0_10px_30px_rgba(15,23,42,0.08)]">
                        <div class="h-44 bg-slate-900">
                            <img src="{{ $event->gambar ? asset('images/' . $event->gambar) : asset('img/eobanner.png') }}" alt="{{ $event->nama }}" class="h-full w-full object-cover">
                        </div>
                        <div class="p-5">
                            <div class="mb-3 inline-flex rounded-full bg-violet-500/10 px-3 py-1 text-xs font-semibold text-violet-600">{{ strtoupper($event->kategori ?? 'EVENT') }}</div>
                            <h3 class="text-lg font-bold text-slate-900">{{ $event->nama }}</h3>
                            <div class="mt-3 space-y-2 text-sm text-slate-500">
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>{{ $event->tanggal ?? '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <span>{{ $event->lokasi ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-10 text-center md:col-span-2 xl:col-span-3">
                        <svg class="mx-auto mb-4 h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p class="font-semibold text-slate-900">Belum ada event</p>
                        <p class="mt-1 text-sm text-slate-500">Tidak ada event yang tersedia saat ini.</p>
                    </div>
                @endforelse
            </div>
        </section>

        @if(($testimonials ?? collect())->count())
            <section class="space-y-4 opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out" data-reveal data-reveal-delay="340">
                <h2 class="text-xl font-extrabold tracking-tight text-slate-900">Apa Kata Mereka tentang {{ $organizer->nama }}</h2>
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($testimonials as $t)
                        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex items-center gap-1 text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="h-4 w-4" fill="{{ $i <= $t->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                @endfor
                            </div>
                            <p class="mt-4 text-sm leading-7 text-slate-700">"{{ $t->komentar }}"</p>
                            <div class="mt-4 flex items-center gap-3">
                                @if($t->avatar)
                                    <img src="{{ asset('images/' . $t->avatar) }}" alt="{{ $t->nama }}" class="h-10 w-10 rounded-full object-cover">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-600">{{ strtoupper(substr($t->nama, 0, 1)) }}</div>
                                @endif
                                <div>
                                    <div class="font-semibold text-slate-900">{{ $t->nama }}</div>
                                    <div class="text-xs text-slate-500">{{ $t->jabatan }}</div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    @include('partials.footer')
</body>
</html>
