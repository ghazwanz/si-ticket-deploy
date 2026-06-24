<x-admin-layout>
    <x-slot name="title">Riwayat Pembatalan - Admin JoinFest</x-slot>
    <x-slot name="header">PENGAWASAN PEMBATALAN</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Pembatalan Acara</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Tinjau, setujui, atau tolak permohonan pembatalan acara.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-4 py-2 rounded-2xl glass-panel text-xs font-bold text-slate-600 dark:text-slate-300">
                    <x-heroicon-o-clock class="w-4 h-4 mr-2 text-rose-500" />
                    Menunggu: {{ \App\Models\CancellationRequest::where('status', 'pending')->count() }}
                </span>
            </div>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 p-4 rounded-3xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                @php $activeStatus = request()->query('status', 'all'); @endphp
                
                <a href="{{ route('admin.cancellations.index', array_merge(request()->except('page'), ['status' => 'all'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'all' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Semua Riwayat
                </a>
                <a href="{{ route('admin.cancellations.index', array_merge(request()->except('page'), ['status' => 'pending'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Menunggu Tinjauan
                </a>
                <a href="{{ route('admin.cancellations.index', array_merge(request()->except('page'), ['status' => 'approved'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'approved' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Disetujui
                </a>
                <a href="{{ route('admin.cancellations.index', array_merge(request()->except('page'), ['status' => 'rejected'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'rejected' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Ditolak
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
                       id="cancellation-search"
                       x-model="search"
                       x-on:input.debounce.300ms="updateSearch()"
                       placeholder="Cari acara..." 
                       class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white">
            </div>
        </div>

        @if(session('success'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="glass-panel border-rose-500/30 bg-rose-500/5 p-4 rounded-2xl flex items-center gap-3 text-rose-600 dark:text-rose-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                {{ session('error') }}
            </div>
        @endif

        {{-- Events Grid/Table Card --}}
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <x-table-header label="Konten Acara" sort="created_at" />
                            <x-table-header label="Penyelenggara" />
                            <x-table-header label="Alasan" />
                            <x-table-header label="Status" sort="status" />
                            <th class="px-8 py-5 text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase text-right">Tinjau</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($cancellations as $cancellation)
                        @php $event = $cancellation->event; @endphp
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
                                        <div class="text-sm text-slate-400 font-medium">Diajukan: {{ $cancellation->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <a href="{{ route('admin.users.show', $event->organizer) }}" class="flex flex-col group/org" data-link>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 group-hover/org:text-violet-500 transition-colors">{{ $event->organizer->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-medium tracking-tight">Verified EO</span>
                                </a>
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-xs text-slate-600 dark:text-slate-400 max-w-[200px] truncate" title="{{ $cancellation->reason }}">
                                    {{ Str::limit($cancellation->reason, 40) }}
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                @php
                                    $statusMap = [
                                        'pending' => [
                                            'class' => 'bg-amber-100 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20',
                                            'label' => 'Menunggu'
                                        ],
                                        'approved' => [
                                            'class' => 'bg-emerald-100 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20',
                                            'label' => 'Disetujui'
                                        ],
                                        'rejected' => [
                                            'class' => 'bg-rose-100 text-rose-600 border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20',
                                            'label' => 'Ditolak'
                                        ],
                                    ];
                                    $statusData = $statusMap[$cancellation->status->value] ?? [
                                        'class' => 'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700',
                                        'label' => $cancellation->status->label()
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider border {{ $statusData['class'] }}">
                                    {{ $statusData['label'] }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button x-data="" x-on:click.prevent="$dispatch('open-panel', 'review-cancellation-{{ $cancellation->id }}')" 
                                            class="px-4 py-2 rounded-xl glass-panel text-[11px] font-bold {{ $cancellation->status->value === 'pending' ? 'text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-600 hover:text-white' }} transition-all">
                                        {{ $cancellation->status->value === 'pending' ? 'Tinjau' : 'Rincian' }}
                                    </button>
                                </div>

                                {{-- Approval Modal --}}
                                @push('modals')
                                    @include('admin.cancellations.partials.review-modal', ['cancellation' => $cancellation])
                                @endpush
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center opacity-40">
                                    <span class="text-4xl mb-2">📋</span>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Tidak ada riwayat pembatalan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800">
                {{ $cancellations->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
