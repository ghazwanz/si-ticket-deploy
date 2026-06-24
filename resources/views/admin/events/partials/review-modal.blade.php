<x-admin.panel 
    name="review-event-{{ $event->id }}" 
    title="Audit Kualitas Acara" 
    description="Lakukan verifikasi mendalam terhadap konten acara dan legitimasi penyelenggara."
    width="4xl"
>

    <div class="space-y-12">
        {{-- Bagian umum --}}
        <section id="summary-{{ $event->id }}" class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-450 uppercase tracking-widest">Informasi Umum</h3>
                </div>
                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-400 uppercase border border-slate-200 dark:border-slate-700">
                    {{ $event->category->name }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-bold text-neutral-700 dark:text-slate-400 uppercase tracking-widest mb-1">Data Waktu</p>
                        <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-violet-500" />
                            {{ $event->event_date->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                        <p class="text-[10px] font-bold text-neutral-700 dark:text-slate-400 uppercase tracking-widest mb-1">Data Geografis</p>
                        <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                            <x-heroicon-o-map-pin class="w-4 h-4 text-rose-500" />
                            {{ $event->city }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <p class="text-[10px] font-bold text-neutral-700 dark:text-slate-400 uppercase tracking-widest ml-1">Deskripsi Acara</p>
                <div class="p-6 rounded-2xl glass-panel !bg-transparent text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                    {!! $event->description !!}
                </div>
            </div>
        </section>

        {{-- Bagian media --}}
        <section id="media-{{ $event->id }}" class="space-y-6">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-450 uppercase tracking-widest">Aset Media</h3>
            </div>
            <div class="aspect-video rounded-3xl overflow-hidden bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 relative group">
                @if($event->banner_image)
                    <img src="{{ asset('storage/' . $event->banner_image) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-300">
                        <x-heroicon-o-photo class="w-16 h-16" />
                    </div>
                @endif
                <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                    <button class="px-4 py-2 rounded-xl bg-white/20 backdrop-blur-md text-white text-xs font-bold uppercase tracking-widest border border-white/30">Lihat Resolusi Penuh</button>
                </div>
            </div>
        </section>

        {{-- Bagian penyelenggara --}}
        <section id="organizer-{{ $event->id }}" class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-slate-700 dark:text-slate-450 uppercase tracking-widest">Detail Penyelenggara</h3>
                </div>
                <a href="{{ route('admin.users.show', $event->organizer) }}" data-link class="text-xs font-black text-violet-500 uppercase tracking-widest hover:underline transition-all">Lihat Profil Lengkap</a>
            </div>
            <div class="flex items-center gap-4 p-6 rounded-3xl glass-panel !bg-transparent border border-slate-200 dark:border-slate-800">
                <div class="w-12 h-12 rounded-2xl bg-violet-500 flex items-center justify-center font-bold text-white text-lg">
                    {{ substr($event->organizer->name, 0, 1) }}
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">{{ $event->organizer->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">{{ $event->organizer->email }}</p>
                </div>
                <div class="ml-auto">
                    <span class="inline-flex items-center px-3 py-1 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-bold uppercase tracking-wider border border-emerald-500/20">
                        Anggota Terverifikasi
                    </span>
                </div>
            </div>
        </section>
    </div>

    <x-slot name="footer">
        <form method="POST" action="{{ route('admin.events.update-status', $event) }}" x-data="{ status: '{{ $event->status->value }}' }" class="w-full flex flex-col gap-4">
            @csrf
            @method('PUT')
            
            <div class="flex items-center gap-4 w-full">
                <div class="flex-1 max-w-xs">
                    @if($event->status->value === 'published')
                        <div class="mb-2 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-start gap-2">
                            <x-heroicon-o-information-circle class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" />
                            <p class="text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-widest leading-tight">
                                @if($event->hasSales())
                                    Kunci Status: Acara memiliki transaksi aktif. Pembatalan dinonaktifkan dari modal ini dan harus melalui pengajuan pembatalan resmi.
                                @else
                                    Kunci Status: Acara telah dipublikasikan. Perubahan status dibatasi hanya ke selesai atau batalkan.
                                @endif
                            </p>
                        </div>
                    @endif

                    <select name="status" x-model="status" class="block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-sm font-bold focus:ring-violet-500/20 py-3">
                        @if($event->status->value !== 'published')
                            <option value="awaiting_approval" {{ $event->status->value === 'awaiting_approval' ? 'selected' : '' }}>Menunggu Tinjauan</option>
                            <option value="published" {{ $event->status->value === 'published' ? 'selected' : '' }}>Setujui dan Publikasikan</option>
                            <option value="draft" {{ $event->status->value === 'draft' ? 'selected' : '' }}>Kembalikan ke Draf</option>
                            <option value="reject" {{ $event->status->value === 'reject' ? 'selected' : '' }}>Tolak Acara</option>
                        @else
                            <option value="completed">Tandai Selesai</option>
                            @if(!$event->hasSales())
                                <option value="cancelled">Batalkan Acara</option>
                            @endif
                        @endif
                    </select>
                </div>
                
                <div class="ml-auto flex items-center gap-3">
                    <button type="button" x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                        Batal Audit
                    </button>
                    <x-primary-button class="rounded-2xl bg-violet-600 px-8 py-3 text-xs font-bold uppercase tracking-widest shadow-lg shadow-violet-600/20">
                        {{ __('Finalisasi Keputusan') }}
                    </x-primary-button>
                </div>
            </div>

            <div x-show="status === 'reject' || status === 'cancelled'" x-transition class="w-full">
                <label for="rejection_message" class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">
                    <span x-text="status === 'cancelled' ? 'Alasan Pembatalan' : 'Alasan Penolakan'">Alasan Keputusan</span>
                </label>
                <textarea 
                    name="rejection_message" 
                    id="rejection_message" 
                    rows="3" 
                    :required="status === 'reject' || status === 'cancelled'"
                    class="block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-transparent text-sm text-slate-900 dark:text-white focus:ring-violet-500/20 p-4"
                    :placeholder="status === 'cancelled' ? 'Masukkan alasan pembatalan acara secara detail...' : 'Masukkan alasan penolakan secara detail agar dapat diperbaiki oleh penyelenggara...'"
                ></textarea>
            </div>
        </form>
    </x-slot>
</x-admin.panel>
