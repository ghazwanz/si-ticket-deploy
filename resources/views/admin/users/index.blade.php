<x-admin-layout>
    <x-slot name="title">Manajemen Pengguna - Admin JoinFest</x-slot>
    <x-slot name="header">Manajemen Pengguna</x-slot>

    <div class="space-y-6" x-data="{ 
        showCreateModal: false
    }">
        {{-- Header halaman --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Manajemen Akun</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Kelola peran pengguna, verifikasi penyelenggara, dan pantau status akun.</p>
            </div>
            <button x-on:click="$dispatch('open-panel', 'create-user')" 
                    class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-violet-600/20 transition-all hover:bg-violet-700 active:scale-95">
                <x-heroicon-o-plus class="w-4 h-4" />
                Tambah Pengguna Baru
            </button>
        </div>

        {{-- Filter dan pencarian --}}
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white/50 dark:bg-slate-900/50 p-4 rounded-3xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                @php
                    $activePeran = request()->query('role', 'all');
                    $activeStatus = request()->query('status');
                @endphp
                
                <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'status']), ['role' => 'all'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus !== 'deleted' && $activePeran === 'all' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Semua Akun
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'status']), ['role' => 'organizer'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus !== 'deleted' && $activePeran === 'organizer' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Penyelenggara Acara
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'status']), ['role' => 'admin'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus !== 'deleted' && $activePeran === 'admin' ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Administrator
                </a>
                <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['status' => 'pending'])) }}" data-link
                   class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $activeStatus === 'pending' ? 'bg-amber-605 bg-amber-600 text-white shadow-lg shadow-amber-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                    Menunggu Verifikasi
                </a>
                <a href="{{ route('admin.users.index', ['status' => 'deleted']) }}" data-link
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
                           id="user-search"
                           x-model="search"
                           x-on:input.debounce.300ms="updateSearch()"
                           placeholder="Cari pengguna..." 
                           class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white">
                </div>
            @endif
        </div>

        @if(session('status'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="glass-panel border-rose-500/30 bg-rose-500/5 p-4 rounded-2xl flex flex-col gap-2 text-rose-600 dark:text-rose-400 text-sm font-bold animate-fade-in">
                @foreach ($errors->all() as $error)
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                        {{ $error }}
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Kartu tabel utama --}}
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <x-table-header label="Identitas Pengguna" sort="name" />
                            @if($activeStatus === 'pending')
                                <x-table-header label="Alamat & Kontak" />
                                <x-table-header label="Dokumen Legalitas" />
                                <x-table-header label="Tanggal Registrasi" sort="created_at" />
                            @else
                                <x-table-header label="Peran Akun" sort="role" />
                                <x-table-header label="Tanggal Registrasi" sort="created_at" />
                            @endif
                            <x-table-header label="Status Akses" />
                            <th class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-400 uppercase text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($users as $user)
                        <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-700 dark:to-slate-800 flex items-center justify-center font-bold text-slate-600 dark:text-slate-300 group-hover:scale-110 transition-transform">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $user->name }}</div>
                                        <div class="text-[11px] text-neutral-700 dark:text-slate-400 font-medium">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            @if($activeStatus === 'pending')
                                <td class="px-8 py-5">
                                    <div class="text-xs text-slate-900 dark:text-white font-medium">{{ $user->organizerProfile?->official_contact ?? '-' }}</div>
                                    <div class="text-[10px] text-neutral-700 dark:text-slate-400 mt-0.5 truncate max-w-[200px]" title="{{ $user->organizerProfile?->organization_address }}">{{ $user->organizerProfile?->organization_address ?? '-' }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    @if($user->organizerProfile?->legality_document)
                                        <a href="{{ asset('storage/' . $user->organizerProfile->legality_document) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1 bg-violet-500/10 hover:bg-violet-500/20 text-violet-600 dark:text-violet-400 text-xs font-bold uppercase tracking-wider rounded-xl border border-violet-500/20 transition-colors">
                                            <x-heroicon-o-document-text class="w-4 h-4" />
                                            Lihat Dokumen
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">-</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-neutral-700 dark:text-slate-400 whitespace-nowrap">
                                    {{ $user->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                            @else
                                <td class="px-8 py-5">
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider border
                                        {{ $user->role->value === 'admin' ? 'bg-violet-100 text-violet-600 border-violet-200 dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20' : 
                                           ($user->role->value === 'organizer' ? 'bg-blue-100 text-blue-600 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20' : 
                                           'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700') }}">
                                        {{ $user->role->label() }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-neutral-700 dark:text-slate-400 whitespace-nowrap">
                                    {{ $user->created_at->locale('id')->translatedFormat('d M Y, H:i') }} WIB
                                </td>
                            @endif
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-rose-500' }}"></span>
                                    <span class="text-xs font-bold {{ $user->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Ditangguhkan' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if($user->trashed())
                                        <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-rose-500/10 text-rose-500 text-[10px] font-bold uppercase tracking-widest">
                                            <x-heroicon-o-archive-box-x-mark class="w-4 h-4" />
                                            Diarsipkan
                                        </span>
                                    @elseif($activeStatus === 'pending')
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'approve-organizer-{{ $user->id }}')"
                                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-md shadow-emerald-500/10">
                                            Setujui
                                        </button>
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'reject-organizer-{{ $user->id }}')"
                                                class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition-all shadow-md shadow-rose-500/10">
                                            Tolak
                                        </button>
                                    @else
                                        <a href="{{ route('admin.users.show', $user) }}" data-link
                                           class="p-2.5 rounded-xl glass-panel text-slate-400 hover:text-emerald-500 hover:border-emerald-500/30 transition-all">
                                            <x-heroicon-o-eye class="w-4 h-4" />
                                        </a>
                                        <button x-data="" x-on:click.prevent="$dispatch('open-panel', 'edit-user-{{ $user->id }}')" 
                                                class="p-2.5 rounded-xl glass-panel text-slate-400 hover:text-violet-500 hover:border-violet-500/30 transition-all">
                                            <x-heroicon-o-pencil-square class="w-4 h-4" />
                                        </button>
                                        @php
                                            $isBlocked = $user->role->value === 'organizer' && (
                                                $user->hasPublishedEvents() || 
                                                $user->hasPendingPayouts() || 
                                                $user->events()->whereHas('orders')->exists()
                                            );
                                        @endphp
                                        <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'delete-user-{{ $user->id }}')" 
                                                class="p-2.5 rounded-xl glass-panel text-slate-400 transition-all
                                                {{ !$user->is_active ? 'hover:text-emerald-500 hover:border-emerald-500/30' : ($isBlocked ? 'hover:text-amber-500 hover:border-amber-500/30' : 'hover:text-rose-500 hover:border-rose-500/30') }}">
                                            @if(!$user->is_active)
                                                <x-heroicon-o-play class="w-4 h-4" />
                                            @elseif($isBlocked)
                                                <x-heroicon-o-pause class="w-4 h-4" />
                                            @else
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                            @endif
                                        </button>
                                    @endif
                                </div>

                                {{-- Dynamic Modals --}}
                            </td>
                            @unless($user->trashed())
                                @push('modals')
                                    @include('admin.users.partials.modals', ['user' => $user])
                                    
                                    @if($user->role->value === 'organizer' && $user->organizerProfile && $user->organizerProfile->status->value === 'pending')
                                        <!-- Approval Confirmation Modal -->
                                        <div x-data="{ open: false }" 
                                             @open-modal.window="if ($event.detail === 'approve-organizer-{{ $user->id }}') open = true"
                                             @keydown.escape.window="open = false"
                                             x-show="open" 
                                             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
                                             
                                             <div x-show="open" 
                                                  x-transition.opacity 
                                                  class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" 
                                                  @click="open = false"></div>
                                             
                                             <div x-show="open" 
                                                  x-transition:enter="transition ease-out duration-300"
                                                  x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                                  x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                  class="glass-panel w-full max-w-lg rounded-[2rem] shadow-2xl relative overflow-hidden z-10 border border-slate-200 dark:border-slate-800">
                                                  
                                                 <div class="p-6 sm:p-8">
                                                     <div class="flex items-start justify-between mb-6">
                                                         <div>
                                                             <h2 class="text-xl font-bold text-slate-900 dark:text-white">Konfirmasi Persetujuan</h2>
                                                             <p class="text-sm text-neutral-700 dark:text-slate-400 mt-1">Harap tinjau informasi penyelenggara sebelum menyetujui pendaftaran.</p>
                                                         </div>
                                                         <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors">
                                                             <x-heroicon-o-x-mark class="w-5 h-5" />
                                                         </button>
                                                     </div>

                                                     <!-- Organizer Detail Info -->
                                                     <div class="space-y-4 mb-6 p-5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 max-h-[24rem] overflow-y-auto">
                                                         <div>
                                                             <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Nama Penyelenggara</span>
                                                             <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->name }}</span>
                                                         </div>
                                                         <div>
                                                             <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Nama Organisasi</span>
                                                             <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->organization_name ?? '-' }}</span>
                                                         </div>
                                                         <div>
                                                             <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Kontak Resmi (Pos-el / Telepon)</span>
                                                             <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                                                 {{ $user->organizerProfile?->official_contact ?? '-' }} / {{ $user->organizerProfile?->phone ?? '-' }}
                                                             </span>
                                                         </div>
                                                         <div>
                                                             <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Alamat Organisasi</span>
                                                             <span class="text-sm font-semibold text-slate-900 dark:text-white whitespace-pre-line">{{ $user->organizerProfile?->organization_address ?? '-' }}</span>
                                                         </div>
                                                         <div class="grid grid-cols-2 gap-4">
                                                             <div>
                                                                 <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Bank & Nomor Rekening</span>
                                                                 <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                                                     {{ $user->organizerProfile?->bank_name ?? '-' }} - {{ $user->organizerProfile?->bank_account_number ?? '-' }}
                                                                 </span>
                                                             </div>
                                                             <div>
                                                                 <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400">Nama Pemilik Rekening</span>
                                                                 <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->bank_account_name ?? '-' }}</span>
                                                             </div>
                                                         </div>
                                                         @if($user->organizerProfile?->legality_document)
                                                             <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
                                                                 <span class="block text-[10px] font-bold uppercase tracking-widest text-neutral-700 dark:text-slate-400 mb-1.5">Dokumen Legalitas</span>
                                                                 <div class="flex flex-col gap-1 mb-3">
                                                                     <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                                                                         Nama Berkas: <span class="font-mono text-slate-800 dark:text-slate-200">{{ basename($user->organizerProfile->legality_document) }}</span>
                                                                     </span>
                                                                     <span class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                                                                         Tipe Berkas: <span class="font-bold text-violet-600 dark:text-violet-400">{{ strtoupper(pathinfo($user->organizerProfile->legality_document, PATHINFO_EXTENSION)) }}</span>
                                                                     </span>
                                                                 </div>
                                                                 <a href="{{ Storage::url($user->organizerProfile->legality_document) }}" target="_blank" 
                                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-500/10 text-violet-600 dark:text-violet-400 border border-violet-500/20 text-xs font-semibold hover:bg-violet-500/20 transition-all">
                                                                     <x-heroicon-o-document-arrow-down class="w-4 h-4" />
                                                                     Unduh / Lihat Dokumen Legalitas
                                                                 </a>
                                                             </div>
                                                         @endif
                                                     </div>

                                                     <form method="POST" action="{{ route('admin.users.approve-organizer', $user) }}">
                                                         @csrf
                                                         
                                                         <div class="flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 pt-6">
                                                             <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                                                                 Batal
                                                             </button>
                                                             <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold uppercase tracking-widest transition-all">
                                                                 Setujui & Aktifkan
                                                             </button>
                                                         </div>
                                                     </form>
                                                 </div>
                                             </div>
                                         </div>
                                    @endif

                                    @if($user->role->value === 'organizer' && $user->organizerProfile && $user->organizerProfile->status->value === 'pending')
                                        <div x-data="{ open: false }" 
                                             @open-modal.window="if ($event.detail === 'reject-organizer-{{ $user->id }}') open = true"
                                             @keydown.escape.window="open = false"
                                             x-show="open" 
                                             class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
                                            
                                            <div x-show="open" 
                                                 x-transition.opacity 
                                                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" 
                                                 @click="open = false"></div>
                                            
                                            <div x-show="open" 
                                                 x-transition:enter="transition ease-out duration-300"
                                                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                 class="glass-panel w-full max-w-md rounded-[2rem] shadow-2xl relative overflow-hidden z-10 border border-slate-200 dark:border-slate-800">
                                                 
                                                <div class="p-6 sm:p-8">
                                                    <div class="flex items-start justify-between mb-6">
                                                        <div>
                                                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Tolak Pendaftaran</h2>
                                                            <p class="text-sm text-neutral-700 dark:text-slate-400 mt-1">Berikan alasan penolakan pendaftaran untuk {{ $user->name }}.</p>
                                                        </div>
                                                        <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors">
                                                            <x-heroicon-o-x-mark class="w-5 h-5" />
                                                        </button>
                                                    </div>

                                                    <form method="POST" action="{{ route('admin.users.reject-organizer', $user) }}" class="space-y-6">
                                                        @csrf
                                                        
                                                        <div class="space-y-2">
                                                            <x-input-label for="rejection_reason_{{ $user->id }}" :value="__('Alasan Penolakan')" class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase tracking-widest ml-1" />
                                                            <textarea id="rejection_reason_{{ $user->id }}" name="rejection_reason" required rows="4"
                                                                      class="block w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent text-sm focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white"
                                                                      placeholder="Tulis alasan penolakan secara rinci... (min. 10 karakter)"></textarea>
                                                        </div>

                                                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                                                            <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors">
                                                                Batal
                                                            </button>
                                                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold uppercase tracking-widest">
                                                                Kirim Penolakan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endpush
                            @endunless
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-8 py-12 text-center">
                                <div class="flex flex-col items-center opacity-40">
                                    <span class="text-4xl mb-2">📂</span>
                                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Tidak ada pengguna ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-8 py-6 bg-slate-50/50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        </div>

        {{-- Create User Panel --}}
        @push('modals')
            @include('admin.users.partials.create-modal')
        @endpush
    </div>
</x-admin-layout>
