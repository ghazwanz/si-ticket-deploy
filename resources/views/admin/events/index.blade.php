<x-admin-layout>
    <x-slot name="title">Pengawasan Acara - Admin JoinFest</x-slot>
    <x-slot name="header">QUALITY CONTROL</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Persetujuan Acara</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Verifikasi rincian acara, periksa kategori, dan validasi penyelenggara.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-4 py-2 rounded-2xl glass-panel text-xs font-bold text-slate-600 dark:text-slate-300">
                    <x-heroicon-o-clock class="w-4 h-4 mr-2 text-violet-500" />
                    Menunggu: {{ $events->where('status', 'awaiting_approval')->count() }}
                </span>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 p-4 rounded-3xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                @php $activeStatus = request()->query('status', 'all'); @endphp
                
                <a href="{{ route('admin.events.index', array_merge(request()->except('page'), ['status' => 'all'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'all' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Semua Riwayat
                </a>
                <a href="{{ route('admin.events.index', array_merge(request()->except('page'), ['status' => 'awaiting_approval'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'awaiting_approval' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Menunggu Tinjauan
                </a>
                <a href="{{ route('admin.events.index', array_merge(request()->except('page'), ['status' => 'awaiting_cancellation'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'awaiting_cancellation' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Menunggu Pembatalan
                </a>
                <a href="{{ route('admin.events.index', array_merge(request()->except('page'), ['status' => 'published'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'published' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Acara Disetujui
                </a>

                <a href="{{ route('admin.events.index', array_merge(request()->except('page'), ['status' => 'cancelled'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'cancelled' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Dibatalkan
                </a>
            </div>

            <div class="relative w-full lg:w-72" x-data="{ 
                search: '{{ request()->query('search') }}',
                updateSearch() {
                    const url = new URL(window.location.href);
                    url.searchParams.set('search', this.search);
                    url.searchParams.delete('page');
                    window.loadPage(url.toString(), true);
                }
            }">
                <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                </div>
                <input type="text" 
                       id="event-search"
                       x-model="search"
                       x-on:input.debounce.300ms="updateSearch()"
                       placeholder="Cari acara..." 
                       class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white">
            </div>
        </div>

        @if(session('status'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('status') }}
            </div>
        @endif

        {{-- Events Grid/Table Card --}}
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <x-table-header label="Konten Acara" sort="name" />
                            <x-table-header label="Penyelenggara" />
                            <x-table-header label="Kategori" />
                            <x-table-header label="Status" sort="status" />
                            <th class="px-8 py-5 text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase text-right">Tinjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($events as $event)
                        <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-16 h-10 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0 group-hover:scale-105 transition-transform">
                                        @if($event->banner_image)
                                            <img src="{{ asset('storage/' . $event->banner_image) }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-400">
                                                <x-heroicon-o-photo class="w-5 h-5" />
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-slate-900 dark:text-white">{{ $event->name }}</div>
                                        <div class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ $event->city }} • {{ $event->event_date->translatedFormat('d M Y') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <a href="{{ route('admin.users.show', $event->organizer) }}" class="flex flex-col group/org" data-link>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-300 group-hover/org:text-violet-500 transition-colors">{{ $event->organizer->name }}</span>
                                    <span class="text-xs text-slate-600 dark:text-slate-400 font-medium tracking-tight">Verified EO</span>
                                </a>
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    {{ $event->category->name }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $statusMap = [
                                        'draft' => [
                                            'class' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800/30 dark:text-slate-400 dark:border-slate-700/50',
                                            'label' => 'Draf'
                                        ],
                                        'awaiting_approval' => [
                                            'class' => 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                            'label' => 'Menunggu Tinjauan'
                                        ],
                                        'pending' => [
                                            'class' => 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                            'label' => 'Menunggu Tinjauan'
                                        ],
                                        'published' => [
                                            'class' => 'bg-emerald-100 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                            'label' => 'Disetujui'
                                        ],
                                        'awaiting_cancellation' => [
                                            'class' => 'bg-orange-100 text-orange-600 border-orange-200 dark:bg-orange-500/10 dark:text-orange-400 dark:border-orange-500/20',
                                            'label' => 'Menunggu Pembatalan'
                                        ],
                                        'cancelled' => [
                                            'class' => 'bg-rose-100 text-rose-600 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
                                            'label' => 'Dibatalkan'
                                        ],
                                        'completed' => [
                                            'class' => 'bg-blue-100 text-blue-600 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20',
                                            'label' => 'Selesai'
                                        ],
                                    ];
                                    $statusData = $statusMap[$event->status->value] ?? [
                                        'class' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                                        'label' => $event->status->label()
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-[11px] font-bold uppercase tracking-wider border {{ $statusData['class'] }}">
                                    {{ $statusData['label'] }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($event->status->value === 'published')
                                    <form method="POST" action="{{ route('admin.events.toggle-featured', $event) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="p-2 rounded-xl glass-panel {{ $event->is_featured ? 'text-amber-400 hover:text-amber-500' : 'text-slate-400 hover:text-amber-400' }} transition-all"
                                                title="{{ $event->is_featured ? 'Hapus dari Unggulan' : 'Jadikan Unggulan' }}">
                                            @if($event->is_featured)
                                                <x-heroicon-s-star class="w-4 h-4" />
                                            @else
                                                <x-heroicon-o-star class="w-4 h-4" />
                                            @endif
                                        </button>
                                    </form>
                                    @endif
                                    <a href="{{ route('admin.events.show', $event) }}" data-link
                                       class="p-2 rounded-xl glass-panel text-slate-500 hover:text-violet-500 transition-all"
                                       title="Lihat Intelijen">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </a>
                                    @if($event->status->value === 'cancelled')
                                        <a href="{{ route('admin.events.show', $event) }}" data-link
                                           class="px-4 py-2 rounded-xl glass-panel text-[11px] font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-600 hover:text-white transition-all">
                                            Lihat Rincian
                                        </a>
                                    @elseif(!in_array($event->status->value, ['completed', 'awaiting_cancellation']))
                                        <button x-data="" x-on:click.prevent="$dispatch('open-panel', 'review-event-{{ $event->id }}')" 
                                                class="px-4 py-2 rounded-xl glass-panel text-[11px] font-bold text-violet-600 dark:text-violet-400 hover:bg-violet-600 hover:text-white transition-all">
                                            Audit Acara
                                        </button>
                                    @endif
                                </div>

                                {{-- Approval Modal --}}
                            @push('modals')
                                @include('admin.events.partials.review-modal', ['event' => $event])
                            @endpush
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center opacity-40">
                                    <span class="text-4xl mb-2">🔭</span>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Tidak ada acara dalam antrean</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800">
                {{ $events->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
