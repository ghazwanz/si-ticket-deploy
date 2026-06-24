<x-public-layout title="Katalog Acara">
    <div class="w-full px-4 py-6 md:px-6 md:py-10">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 flex flex-col md:flex-row md:items-end justify-between gap-6" data-reveal data-reveal-delay="0">
                <div>
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="h-px w-8 bg-violet-500"></span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">Discover</span>
                    </div>
                    <h1 class="lg:text-5xl text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">Katalog Acara</h1>
                    <p class="mt-3 text-sm text-slate-600 dark:text-slate-400 max-w-xl">Temukan Acara pilihanmu dan lanjutkan pemesanan dalam alur yang cepat dan aman.</p>
                </div>

                <!-- Search Box -->
                <div class="w-full md:w-96">
                    <form action="{{ route('events.index') }}" method="GET" class="relative group">
                        <!-- Preserve existing filters -->
                        @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                        @if(request('city')) <input type="hidden" name="city" value="{{ request('city') }}"> @endif
                        @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                        @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                        @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                        
                        <x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400 dark:text-slate-550 group-focus-within:text-violet-600 dark:group-focus-within:text-violet-400 transition-colors" />
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari acara, atau penyelenggara..." aria-label="Cari acara, atau penyelenggara"
                               class="w-full rounded-2xl border border-slate-200 dark:border-white/10 bg-white/50 dark:bg-slate-900/40 py-3 pl-11 pr-4 text-sm font-medium text-slate-800 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 outline-none transition-all focus:border-violet-500 focus:bg-white focus:dark:bg-slate-950 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 shadow-sm">
                    </form>
                </div>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row items-start">
                <!-- Sidebar Filter -->
                <aside class="w-full shrink-0 rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl p-6 shadow-md lg:sticky lg:top-28 lg:w-72 opacity-0 blur-sm translate-y-6 scale-[0.98] transition-all duration-700 ease-out" data-reveal data-reveal-delay="50">
                    <form action="{{ route('events.index') }}" method="GET" class="space-y-6">
                        <!-- Preserve search -->
                        @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                        <!-- Kategori Filter (Dynamic) -->
                        <div>
                            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-3">Kategori</h3>
                            <div class="space-y-2.5">
                                @forelse ($eventCategories as $cat)
                                    <label class="group flex cursor-pointer items-center gap-3">
                                        <input type="radio" name="category" value="{{ $cat->slug }}" {{ request('category') == $cat->slug ? 'checked' : '' }} class="h-4 w-4 rounded-full border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-white/5 text-violet-600 dark:text-violet-400 transition focus:ring-violet-500/30 focus:ring-offset-slate-900">
                                        <span class="text-sm font-medium text-slate-650 dark:text-slate-405 transition group-hover:text-slate-900 dark:group-hover:text-white">{{ $cat->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Tidak ada kategori.</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Kota Filter (Dynamic) -->
                        <div class="border-t border-slate-200 dark:border-white/10 pt-5">
                            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-3">Kota</h3>
                            <select name="city" class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 py-2 px-3 text-sm text-slate-800 dark:text-slate-300 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 shadow-sm">
                                <option value="">Semua Kota</option>
                                @foreach ($cities as $city)
                                    <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div class="border-t border-slate-200 dark:border-white/10 pt-5">
                            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-3">Status Acara</h3>
                            <div class="space-y-2.5">
                                <label class="group flex cursor-pointer items-center gap-3">
                                    <input type="radio" name="status" value="" {{ !request('status') ? 'checked' : '' }} class="h-4 w-4 rounded-full border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-white/5 text-violet-600 dark:text-violet-400 transition focus:ring-violet-500/30 focus:ring-offset-slate-900">
                                    <span class="text-sm font-medium text-slate-650 dark:text-slate-405 transition group-hover:text-slate-900 dark:group-hover:text-white">Semua Status</span>
                                </label>
                                <label class="group flex cursor-pointer items-center gap-3">
                                    <input type="radio" name="status" value="upcoming" {{ request('status') == 'upcoming' ? 'checked' : '' }} class="h-4 w-4 rounded-full border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-white/5 text-violet-600 dark:text-violet-400 transition focus:ring-violet-500/30 focus:ring-offset-slate-900">
                                    <span class="text-sm font-medium text-slate-650 dark:text-slate-405 transition group-hover:text-slate-900 dark:group-hover:text-white">Mendatang</span>
                                </label>
                                <label class="group flex cursor-pointer items-center gap-3">
                                    <input type="radio" name="status" value="suspended" {{ request('status') == 'suspended' ? 'checked' : '' }} class="h-4 w-4 rounded-full border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-white/5 text-violet-600 dark:text-violet-400 transition focus:ring-violet-500/30 focus:ring-offset-slate-900">
                                    <span class="text-sm font-medium text-slate-650 dark:text-slate-405 transition group-hover:text-slate-900 dark:group-hover:text-white">Ditangguhkan</span>
                                </label>
                                <label class="group flex cursor-pointer items-center gap-3">
                                    <input type="radio" name="status" value="completed" {{ request('status') == 'completed' ? 'checked' : '' }} class="h-4 w-4 rounded-full border-slate-300 dark:border-white/20 bg-slate-50 dark:bg-white/5 text-violet-600 dark:text-violet-400 transition focus:ring-violet-500/30 focus:ring-offset-slate-900">
                                    <span class="text-sm font-medium text-slate-650 dark:text-slate-405 transition group-hover:text-slate-900 dark:group-hover:text-white">Selesai</span>
                                </label>
                            </div>
                        </div>

                        <!-- Rentang Tanggal Filter -->
                        <div class="border-t border-slate-200 dark:border-white/10 pt-5">
                            <h3 class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-3">Rentang Tanggal</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Mulai</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 py-2 px-3 text-sm text-slate-800 dark:text-slate-300 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Selesai</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 py-2 px-3 text-sm text-slate-800 dark:text-slate-300 outline-none transition focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 shadow-sm">
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-slate-200 dark:border-white/10 pt-5">
                            <button type="submit" class="w-full rounded-xl bg-violet-600 py-3 text-sm font-bold tracking-wide text-white shadow-lg shadow-violet-600/25 transition hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-violet-700/30">Terapkan Filter</button>
                            <a href="{{ route('events.index') }}" data-link class="mt-3 block text-center w-full py-2 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">Reset Semua</a>
                        </div>
                    </form>
                </aside>

                <!-- Event Grid -->
                <section class="w-full" data-reveal data-reveal-delay="100">
                    <div class="grid gap-6 sm:grid-cols-2">
                    @forelse ($events as $event)
                        <x-event-card :event="$event" :loop-index="$loop->index" />
                    @empty
                        <div class="col-span-full py-20 text-center bg-white dark:bg-slate-900/40 border border-slate-200 dark:border-white/10 rounded-[2rem] shadow-sm">
                            <x-heroicon-o-magnifying-glass class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-600 mb-4" />
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Acara tidak ditemukan</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Coba ubah filter atau kata kunci pencarian Anda.</p>
                        </div>
                    @endforelse
                    </div>

                    @if($events->hasPages())
                        <div class="mt-10">
                            {{ $events->links() }}
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</x-public-layout>
