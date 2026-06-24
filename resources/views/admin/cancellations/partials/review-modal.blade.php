<x-admin.panel 
    name="review-cancellation-{{ $cancellation->id }}" 
    title="Tinjauan Pembatalan Acara" 
    description="Evaluasi permohonan pembatalan acara dan dampak terhadap pembeli tiket."
    width="4xl"
>
    @php
        $event = $cancellation->event;
        $cutoffPassed = now()->greaterThanOrEqualTo(\Illuminate\Support\Carbon::parse($event->event_date->format('Y-m-d') . ' ' . $event->start_time));
        $payout = $event->payout;
    @endphp

    <div class="space-y-8">
        {{-- Peringatan Batas Waktu (Hard Cutoff Warning) --}}
        @if($cutoffPassed)
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5.5 h-5.5 text-rose-500 shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-xs font-bold text-rose-600 dark:text-rose-400 uppercase tracking-widest">Batas Waktu Pembatalan Terlampaui</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Acara ini telah melewati waktu mulai pelaksanaan ({{ $event->event_date->format('d-m-Y') }} {{ substr($event->start_time, 0, 5) }}). Berdasarkan aturan sistem, persetujuan atau penolakan pembatalan tidak dapat diproses lagi.
                    </p>
                </div>
            </div>
        @endif

        {{-- Ringkasan Acara --}}
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Detail Acara</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nama Acara</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $event->name }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Waktu Pelaksanaan</p>
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                        <x-heroicon-o-calendar class="w-4 h-4 text-violet-500" />
                        {{ $event->event_date->format('d M Y') }} • {{ substr($event->start_time, 0, 5) }} WIB
                    </div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Lokasi / Tempat</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $event->venue_name }}, {{ $event->city }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Penyelenggara</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $event->organizer->name }}</p>
                </div>
            </div>
        </section>

        {{-- Penilaian Dampak --}}
        <section class="space-y-4">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Penilaian Dampak Pembatalan</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-rose-500/5 border border-rose-500/10">
                    <p class="text-[10px] font-bold text-rose-500 uppercase tracking-widest mb-1">Pembeli Tiket Terdampak</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">{{ $cancellation->ticket_holders_count }}</span>
                        <span class="text-xs text-slate-400 font-bold">Orang terdaftar</span>
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-2 font-medium">Semua pembeli tiket yang berstatus lunas akan menerima surat elektronik notifikasi pembatalan otomatis.</p>
                </div>
                
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 flex flex-col justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status Pencairan Dana (Payout)</p>
                        @if($payout)
                            <div class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase tracking-wide mt-1
                                {{ $payout->status === 'completed' ? 'bg-rose-500/10 text-rose-500' : 'bg-amber-500/10 text-amber-500' }}">
                                @if($payout->status === 'pending')
                                    Tertunda (Akan Dibatalkan Otomatis)
                                @elseif($payout->status === 'processing')
                                    Sedang Diproses (Akan Dibatalkan Otomatis)
                                @elseif($payout->status === 'completed')
                                    Sudah Dicairkan (Peringatan!)
                                @elseif($payout->status === 'voided')
                                    Telah Dibatalkan (Voided)
                                @else
                                    {{ $payout->status }}
                                @endif
                            </div>
                        @else
                            <span class="text-xs font-bold text-slate-400 mt-1 block">Belum Ada Pengajuan Pencairan</span>
                        @endif
                    </div>
                    @if($payout && $payout->status === 'completed')
                        <p class="text-[10px] text-rose-500 font-bold mt-2">
                            Peringatan: Dana acara telah dicairkan kepada penyelenggara! Harap hubungi pihak penyelenggara secara manual untuk pengembalian dana.
                        </p>
                    @endif
                </div>
            </div>
        </section>

        {{-- Alasan Penyelenggara --}}
        <section class="space-y-2">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Alasan Pengajuan Pembatalan</h3>
            </div>
            <div class="p-4 rounded-2xl glass-panel !bg-transparent text-sm text-slate-600 dark:text-slate-400 leading-relaxed font-medium">
                {{ $cancellation->reason }}
            </div>
        </section>

        {{-- Formulir Keputusan Admin --}}
        @if(!$cutoffPassed && $cancellation->status->value === 'pending')
            <section class="border-t border-slate-200 dark:border-slate-800 pt-6 space-y-6">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Formulir Keputusan Penolakan</h3>
                </div>

                <form method="POST" action="{{ route('admin.cancellations.reject', $cancellation) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="space-y-2">
                        <label for="rejection_reason_{{ $cancellation->id }}" class="block text-xs font-bold text-slate-500 uppercase tracking-widest">
                            Alasan Penolakan Permohonan (Wajib diisi jika menolak)
                        </label>
                        <textarea 
                            name="rejection_reason" 
                            id="rejection_reason_{{ $cancellation->id }}" 
                            rows="3" 
                            placeholder="Tulis alasan penolakan secara rinci agar dipahami oleh penyelenggara (minimal 10 karakter)..." 
                            class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl px-4 py-3 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white"
                        >{{ old('rejection_reason') }}</textarea>
                        @error('rejection_reason')
                            <p class="text-xs font-bold text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest bg-rose-600 text-white hover:bg-rose-700 transition-colors shadow-lg shadow-rose-600/10">
                            Tolak Pembatalan
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>

    <x-slot name="footer">
        <div class="flex items-center justify-between w-full">
            <div>
                <button type="button" x-on:click="close()" class="px-6 py-3 rounded-2xl text-sm font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors">
                    Tutup Tinjauan
                </button>
            </div>
            
            @if(!$cutoffPassed && $cancellation->status->value === 'pending')
                <div>
                    <form method="POST" action="{{ route('admin.cancellations.approve', $cancellation) }}">
                        @csrf
                        @method('PUT')
                        <button 
                            type="submit" 
                            x-on:click.prevent="if (confirm('Apakah Anda yakin ingin menyetujui permohonan pembatalan ini? Acara akan dibatalkan secara permanen, pencairan dana akan dibatalkan, dan semua pembeli tiket akan menerima notifikasi email.')) $el.closest('form').submit()"
                            class="px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-600/20"
                        >
                            Setujui Pembatalan Acara
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </x-slot>
</x-admin.panel>
