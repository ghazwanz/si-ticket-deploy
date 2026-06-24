<x-admin-layout>
    <x-slot name="title">Registri Kategori - Admin JoinFest</x-slot>
    <x-slot name="header">TAKSONOMI PLATFORM</x-slot>

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Registri Kategori</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Kelola dan susun kategori acara untuk mendukung penemuan serta rekomendasi.</p>
            </div>
            <button x-on:click="$dispatch('open-panel', 'create-category')" 
                    class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all hover:bg-violet-700 active:scale-95">
                <x-heroicon-o-plus class="w-4 h-4" />
                Kategori Baru
            </button>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 p-4 rounded-3xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                @php $activeStatus = request()->query('status'); @endphp

                <a href="{{ route('admin.event-categories.index', request()->except(['page', 'status'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus !== 'deleted' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Kategori Aktif
                </a>
                <a href="{{ route('admin.event-categories.index', ['status' => 'deleted']) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'deleted' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/20' : 'glass-panel text-slate-500 hover:text-rose-500' }}">
                    Arsip Terhapus
                </a>
            </div>

            @if($activeStatus !== 'deleted')
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
                           x-model="search"
                           x-on:input.debounce.300ms="updateSearch()"
                           placeholder="Cari kategori..." 
                           class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white">
                </div>
            @endif
        </div>

        {{-- Notifications --}}
        @if(session('status'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="glass-panel border-rose-500/30 bg-rose-500/5 p-4 rounded-2xl flex items-center gap-3 text-rose-600 dark:text-rose-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-x-circle class="w-5 h-5" />
                {{ session('error') }}
            </div>
        @endif

        {{-- Main Table Card --}}
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <x-table-header label="Nama Kategori" sort="name" />
                            <x-table-header label="Identitas URL (Slug)" />
                            <x-table-header label="Jumlah Acara" />
                            <x-table-header label="Tanggal Registrasi" sort="created_at" />
                            <th class="px-8 py-5 text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($categories as $category)
                        @php
                            $colorClasses = [
                                'violet' => 'from-violet-500/20 to-fuchsia-500/20 text-violet-600 dark:text-violet-400',
                                'sky' => 'from-sky-500/20 to-blue-500/20 text-sky-600 dark:text-sky-400',
                                'emerald' => 'from-emerald-500/20 to-teal-500/20 text-emerald-600 dark:text-emerald-400',
                                'rose' => 'from-rose-500/20 to-red-500/20 text-rose-600 dark:text-rose-400',
                                'amber' => 'from-amber-500/20 to-orange-500/20 text-amber-600 dark:text-amber-400',
                                'fuchsia' => 'from-fuchsia-500/20 to-pink-500/20 text-fuchsia-600 dark:text-fuchsia-400',
                                'cyan' => 'from-cyan-500/20 to-teal-500/20 text-cyan-600 dark:text-cyan-400',
                                'indigo' => 'from-indigo-500/20 to-violet-500/20 text-indigo-600 dark:text-indigo-400',
                            ];
                            $activeColor = $colorClasses[$category->color] ?? $colorClasses['violet'];
                        @endphp
                        <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    @if($category->image)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-10 h-10 rounded-2xl object-cover border border-slate-200/50 dark:border-slate-800/50 group-hover:scale-110 transition-transform">
                                    @else
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br {{ $activeColor }} flex items-center justify-center font-bold group-hover:scale-110 transition-transform border border-slate-200/50 dark:border-slate-800/50">
                                            <x-heroicon-o-tag class="w-5 h-5" />
                                        </div>
                                    @endif
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <code class="text-[10px] px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-mono">
                                    {{ $category->slug }}
                                </code>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-400">
                                    {{ number_format($category->events_count ?? $category->events()->count()) }}
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-xs text-slate-500 dark:text-slate-500">
                                    {{ $category->created_at->format('M d, Y') }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($category->trashed())
                                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-500/10 text-rose-500 text-[10px] font-bold uppercase tracking-widest">
                                            <x-heroicon-o-archive-box-x-mark class="w-4 h-4" />
                                            Diarsipkan
                                        </span>
                                    @else
                                        <button x-on:click="$dispatch('open-panel', 'edit-category-{{ $category->id }}')" 
                                                class="p-2.5 rounded-xl glass-panel text-slate-400 hover:text-violet-500 hover:border-violet-500/30 transition-all">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        <button x-on:click="$dispatch('open-modal', 'delete-category-{{ $category->id }}')" 
                                                class="p-2.5 rounded-xl glass-panel text-slate-400 hover:text-rose-500 hover:border-rose-500/30 transition-all">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>

                                @unless($category->trashed())
                                    @push('modals')
                                        @include('admin.event-categories.partials.modals', ['category' => $category])
                                    @endpush
                                @endunless
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div class="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                        <x-heroicon-o-tag class="w-8 h-8" />
                                    </div>
                                    <div class="text-slate-500 dark:text-slate-400 font-medium">Tidak ada kategori yang sesuai dengan kriteria Anda.</div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="px-8 py-6 border-t border-slate-200 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-900/30">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    @push('modals')
        @include('admin.event-categories.partials.create-modal')
    @endpush
</x-admin-layout>
