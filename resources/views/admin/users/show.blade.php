<x-admin-layout>
    <x-slot name="title">Profil Pengguna - {{ $user->name }}</x-slot>
    <x-slot name="header">PROFIL PENGGUNA</x-slot>

    <div class="space-y-8 animate-fade-in">
        {{-- Navigation & Quick Tindakan --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors group" data-link>
                <div class="p-2 rounded-xl glass-panel group-hover:scale-110 transition-transform">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </div>
                <span class="text-xs font-bold uppercase">Kembali ke Direktori</span>
            </a>
            
            <div class="flex items-center gap-3">
                <button x-on:click="$dispatch('open-panel', 'edit-user-{{ $user->id }}')" 
                        class="px-5 py-2.5 rounded-2xl glass-panel text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-violet-500/50 hover:text-violet-500 transition-all">
                    Ubah Akun
                </button>
                @php
                    $isBlocked = $user->role->value === 'organizer' && (
                        $user->hasPublishedEvents() || 
                        $user->hasPendingPayouts() || 
                        $user->events()->whereHas('orders')->exists()
                    );
                @endphp
                <button x-on:click="$dispatch('open-modal', 'delete-user-{{ $user->id }}')"
                        class="px-5 py-2.5 rounded-2xl glass-panel text-xs font-bold transition-all
                        {{ !$user->is_active ? 'text-emerald-500 hover:bg-emerald-500 hover:text-white' : ($isBlocked ? 'text-amber-500 hover:bg-amber-500 hover:text-white' : 'text-rose-500 hover:bg-rose-500 hover:text-white') }}">
                    {{ !$user->is_active ? 'Pulihkan Akses' : ($isBlocked ? 'Tangguhkan Akses' : 'Arsipkan Akses') }}
                </button>
            </div>
        </div>

        {{-- Profile Hero --}}
        <div class="glass-panel p-8 space-y-6 rounded-[2.5rem] relative overflow-hidden">
            {{-- Decorative Background --}}
            <div class="absolute top-0 right-0 w-64 h-64 bg-violet-600/5 blur-[100px] -mr-32 -mt-32"></div>
            
            <div class="flex flex-col md:flex-row items-center md:items-start gap-8 relative">
                <div class="w-32 h-32 rounded-[2.5rem] bg-gradient-to-br from-violet-500 to-fuchsia-600 flex items-center justify-center text-4xl font-black text-white shadow-2xl shadow-violet-500/20">
                    {{ substr($user->name, 0, 1) }}
                </div>
                
                <div class="flex-1 text-center md:text-left space-y-2">
                    <div class="flex flex-col md:flex-row md:items-center gap-3">
                        <h1 class="text-4xl font-black tracking-tight text-slate-900 dark:text-white">{{ $user->name }}</h1>
                        <span class="inline-flex items-center px-4 py-1.5 rounded-2xl text-xs font-black uppercase border
                            {{ $user->role->value === 'admin' ? 'bg-violet-100 text-violet-600 border-violet-200 dark:bg-violet-500/10 dark:text-violet-400 dark:border-violet-500/20' : 
                               ($user->role->value === 'organizer' ? 'bg-blue-100 text-blue-600 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/20' : 
                               'bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700') }}">
                            {{ $user->role->label() }}
                        </span>
                    </div>
                    <p class="text-neutral-700 dark:text-slate-400 font-medium">{{ $user->email }}</p>
                    
                    <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-6">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-2xl glass-panel !bg-transparent">
                            <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-tighter">{{ $user->is_active ? 'Aktif' : 'Ditangguhkan' }}</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-2xl glass-panel !bg-transparent">
                            <x-heroicon-o-calendar class="w-4 h-4 text-slate-400" />
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-tighter">Bergabung {{ $user->created_at->locale('id')->translatedFormat('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Stats Grid --}}
                <div class="grid grid-cols-2 gap-4 w-full md:w-auto">
                    <div class="glass-panel p-6 rounded-3xl text-center md:text-left min-w-[140px]">
                        <div class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase mb-1">{{ $user->role->value === 'organizer' ? 'Diselenggarakan' : 'Dibeli' }}</div>
                        <div class="text-2xl font-black text-slate-900 dark:text-white">{{ $user->role->value === 'organizer' ? $user->events->count() : $user->orders->count() }}</div>
                        <div class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase mt-1">{{ $user->role->value === 'organizer' ? 'Acara' : 'Pesanan' }}</div>
                    </div>
                    <div class="glass-panel p-6 rounded-3xl text-center md:text-left min-w-[140px]">
                        <div class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase mb-1">Status</div>
                        <div class="text-2xl font-black text-emerald-500">Terverifikasi</div>
                        <div class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase mt-1">Identitas</div>
                    </div>
                </div>
        </div>
        
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

        {{-- Primary Intelligence Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            {{-- Registri Akun Card --}}
            <section class="lg:col-span-1 glass-panel p-8 rounded-[2rem] space-y-6 flex flex-col">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-400 uppercase">Informasi Akun</h3>
                </div>

                <div class="space-y-4 flex-1">
                    <div>
                        <label class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase block mb-1">Identitas Unik</label>
                        <div class="text-xs font-mono text-slate-600 dark:text-slate-400 break-all bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                            {{ $user->id }}
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase block mb-1">Verifikasi Pos-el</label>
                        <div class="flex items-center gap-2 text-sm font-bold {{ $user->email_verified_at ? 'text-emerald-500' : 'text-amber-500' }}">
                            <x-heroicon-o-check-circle class="w-4 h-4" />
                            {{ $user->email_verified_at ? 'Terverifikasi pada ' . $user->email_verified_at->locale('id')->translatedFormat('d M Y') : 'Menunggu Verifikasi' }}
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase block mb-1">Pembaruan Terakhir</label>
                        <div class="text-sm font-bold text-slate-700 dark:text-slate-300">
                            {{ $user->updated_at->locale('id')->diffForHumans() }}
                        </div>
                    </div>
                </div>
            </section>

            {{-- Profile Details Card (Penyelenggara or Purchase) --}}
            <div class="lg:col-span-2">
                @if($user->role->value === 'organizer')
                    <section class="glass-panel p-8 rounded-[2rem] space-y-6 h-full flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-1.5 h-4 bg-blue-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-400 uppercase">Profil Penyelenggara</h3>
                        </div>

                        <div class="flex-1">
                            @if($user->organizerProfile)
                                <div class="grid md:grid-cols-2 gap-8 h-full">
                                    <div class="space-y-6">
                                        <div>
                                            <label class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase block mb-1">Lembaga Penyelenggara</label>
                                            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->organizerProfile->organization_name }}</div>
                                        </div>
                                        <div>
                                            <label class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase block mb-1">Informasi Kontak</label>
                                            <div class="text-lg font-bold text-slate-900 dark:text-white">{{ $user->organizerProfile->phone }}</div>
                                        </div>
                                    </div>
                                    <div class="space-y-4 p-6 rounded-3xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-2 mb-2">
                                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                            <span class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase">Informasi Rekening</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-black text-slate-900 dark:text-white uppercase">{{ $user->organizerProfile->bank_name }}</div>
                                            <div class="text-lg font-mono font-bold text-slate-900 dark:text-white tracking-tighter">{{ $user->organizerProfile->bank_account_number }}</div>
                                            <div class="text-xs font-bold text-slate-500 dark:text-slate-400 mt-1 uppercase">{{ $user->organizerProfile->bank_account_name }}</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-full opacity-40">
                                    <span class="text-4xl mb-2">🏢</span>
                                    <p class="text-sm font-bold text-slate-400 uppercase">Belum ada profil tertaut</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @else
                    <section class="glass-panel p-8 rounded-[2rem] space-y-6 h-full flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-1.5 h-4 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-xs font-bold text-slate-700 dark:text-slate-400 uppercase">Portofolio Pembelian</h3>
                        </div>
                        
                        <div class="flex-1">
                            @if($user->orders->count() > 0)
                                <div class="grid md:grid-cols-2 gap-4">
                                    @foreach($user->orders->take(4) as $order)
                                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                            <div>
                                                <div class="text-sm font-bold text-slate-900 dark:text-white">Order #{{ substr($order->id, 0, 8) }}</div>
                                                <div class="text-xs text-neutral-700 dark:text-slate-400 uppercase font-bold">{{ $order->created_at->locale('id')->translatedFormat('d M Y') }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm font-black text-slate-900 dark:text-white">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                                <span class="text-xs font-bold uppercase text-emerald-500">Dibayar</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="flex flex-col items-center justify-center h-full opacity-40">
                                    <span class="text-4xl mb-2">🎟️</span>
                                    <p class="text-sm font-bold text-slate-400 uppercase">Belum ada tiket yang diperoleh</p>
                                </div>
                            @endif
                        </div>
                    </section>
                @endif
            </div>
        </div>

        {{-- Bottom Row: Full Width Lists --}}
        <div class="grid grid-cols-1 gap-8">
            <div class="space-y-8">
                @if($user->role->value === 'organizer')
                    {{-- Hosted Events --}}
                    <section class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-400 uppercase">Daftar Acara</h3>
                            </div>
                            <span class="text-xs font-black text-neutral-700 dark:text-slate-400 uppercase">{{ $user->events->count() }} Acara Dikelola</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($user->events as $event)
                                <a href="{{ route('admin.events.show', $event) }}" class="glass-panel p-4 rounded-3xl hover:border-violet-500/30 transition-all group flex items-center gap-4" data-link>
                                    <div class="w-16 h-16 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-900 shrink-0">
                                        @if($event->banner_image)
                                            <img src="{{ asset('storage/' . $event->banner_image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-slate-900 dark:text-white truncate group-hover:text-violet-500 transition-colors">{{ $event->name }}</div>
                                        <div class="text-xs font-bold text-neutral-700 dark:text-slate-400 uppercase mt-0.5">{{ $event->category->name }}</div>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-bold uppercase border
                                                {{ $event->status->value === 'published' ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20' }}">
                                                {{ $event->status->label() }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
        @include('admin.users.partials.modals', ['user' => $user])
    @endpush
</x-admin-layout>
