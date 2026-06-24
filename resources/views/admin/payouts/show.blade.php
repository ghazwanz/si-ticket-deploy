<x-admin-layout>
    <x-slot name="title">Rincian Pencairan Dana - {{ $payout->event->name }}</x-slot>
    <x-slot name="header">RINCIAN PENCAIRAN DANA</x-slot>

    <div class="space-y-8 animate-fade-in">
        {{-- Navigation --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('admin.payouts.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors group" data-link>
                <div class="p-2 rounded-xl glass-panel group-hover:scale-110 transition-transform">
                    <x-heroicon-o-chevron-left class="w-4 h-4" />
                </div>
                <span class="text-xs font-bold uppercase tracking-widest">Kembali ke Pencairan Dana</span>
            </a>
        </div>

        @if(session('success'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="glass-panel border-rose-500/30 bg-rose-500/5 p-4 rounded-2xl flex items-center gap-3 text-rose-600 dark:text-rose-400 text-sm font-bold">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
            {{-- Detail Utama --}}
            <section class="lg:col-span-2 space-y-8">
                {{-- Detail Utama --}}
                <div class="glass-panel p-8 rounded-[2rem] space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $payout->event->name }}</h2>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-semibold border {{ $payout->payout_type?->color() ?? 'text-emerald-600 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/20' }}">
                                    {{ $payout->payout_type?->label() ?? 'Pelunasan (Final)' }}
                                </span>
                            </div>
                            <div class="text-sm text-slate-500 mt-1">Penyelenggara: {{ $payout->organizer->organizerProfile->organization_name ?? $payout->organizer->name }}</div>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider border {{ $payout->statusColor() }}">
                            {{ $payout->statusLabel() }}
                        </span>
                    </div>

                    @if($payout->manual_settlement_required || ($payout->event && $payout->event->manual_settlement_required))
                        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-bold flex items-center gap-3">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 flex-shrink-0" />
                            <div>
                                <div class="font-extrabold uppercase tracking-wide">Penyelesaian Manual Diperlukan</div>
                                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Acara dibatalkan setelah pembayaran awal selesai disalurkan. Segera selesaikan keuangan dengan penyelenggara secara manual.</div>
                            </div>
                        </div>
                    @endif

                    @if($payout->isAdvance())
                        {{-- Advance Payout Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-y border-slate-100 dark:border-slate-800 py-6">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jumlah Diajukan</div>
                                <div class="text-xl font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($payout->requested_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Persetujuan Admin</div>
                                <div class="text-xl font-bold text-violet-600 dark:text-violet-400">
                                    @if($payout->approved_amount)
                                        Rp {{ number_format($payout->approved_amount, 0, ',', '.') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Batas Maks. Pengajuan</div>
                                @php
                                    $summary = app(\App\Services\Admin\PayoutService::class)->getAdvanceSummary($payout->event);
                                @endphp
                                <div class="text-xl font-bold text-emerald-500">Rp {{ number_format($summary['available_advance_amount'] + ($payout->status === \App\Enums\PayoutStatus::Pending ? 0 : ($payout->approved_amount ?? $payout->requested_amount)), 0, ',', '.') }}</div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Alasan Pengajuan Uang Muka</h3>
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-sm text-slate-700 dark:text-slate-300 leading-relaxed font-medium">
                                "{{ $payout->reason }}"
                            </div>
                        </div>

                        @if($payout->rejection_reason)
                            <div class="space-y-2">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest text-rose-500">Alasan Penolakan</h3>
                                <div class="p-4 rounded-xl bg-rose-500/5 border border-rose-500/20 text-sm text-rose-600 dark:text-rose-400 leading-relaxed font-semibold">
                                    "{{ $payout->rejection_reason }}"
                                </div>
                            </div>
                        @endif
                    @else
                        {{-- Final Payout Details --}}
                        @php
                            $completedAdvanceTotal = $payout->event->payouts()
                                ->where('payout_type', \App\Enums\PayoutType::Advance)
                                ->where('status', \App\Enums\PayoutStatus::Completed)
                                ->sum('approved_amount');
                            $estimatedNetSales = $payout->gross_amount - $payout->platform_fee;
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 border-y border-slate-100 dark:border-slate-800 py-6">
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gross Revenue</div>
                                <div class="text-lg font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($payout->gross_amount, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Fee Platform ({{ $payout->fee_percentage }}%)</div>
                                <div class="text-lg font-bold text-rose-500">-Rp {{ number_format($payout->platform_fee, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dipotong Uang Muka</div>
                                <div class="text-lg font-bold text-amber-500">-Rp {{ number_format($completedAdvanceTotal, 0, ',', '.') }}</div>
                            </div>
                            <div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pelunasan Bersih</div>
                                <div class="text-xl font-black text-emerald-500">Rp {{ number_format($payout->net_amount, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Rincian Bank</h3>
                        
                        @if($payout->missing_bank_details)
                            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-medium">
                                <x-heroicon-o-exclamation-circle class="w-5 h-5 inline mr-2" />
                                Informasi rekening bank belum lengkap. Penyelenggara harus memperbarui profil mereka terlebih dahulu.
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nama Bank</div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $payout->payout_bank_name }}</div>
                                </div>
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pemilik Rekening</div>
                                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $payout->payout_account_holder }}</div>
                                </div>
                                <div class="col-span-2 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nomor Rekening</div>
                                    <div class="text-lg font-mono font-bold text-slate-900 dark:text-white tracking-widest">{{ $payout->payout_account_number }}</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if($payout->transfer_reference)
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 space-y-4">
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Referensi Transfer</div>
                            <div class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $payout->transfer_reference }}</div>
                        </div>
                        @if($payout->proof_photo_url)
                        <div>
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Bukti Transfer</div>
                            <a href="{{ $payout->proof_photo_url }}" target="_blank" class="inline-flex items-center gap-2 text-xs text-violet-600 hover:text-violet-500 font-bold">
                                <x-heroicon-o-document-magnifying-glass class="w-4 h-4" />
                                Lihat Bukti Transfer (Private)
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Payout History Table --}}
                <div class="glass-panel p-8 rounded-[2rem] space-y-4">
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Riwayat Pencairan Dana Acara Ini</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-800">
                                    <th class="py-3 font-bold text-slate-400 uppercase tracking-wider">Tipe</th>
                                    <th class="py-3 font-bold text-slate-400 uppercase tracking-wider">Nominal Diajukan</th>
                                    <th class="py-3 font-bold text-slate-400 uppercase tracking-wider">Nominal Disetujui</th>
                                    <th class="py-3 font-bold text-slate-400 uppercase tracking-wider">Status</th>
                                    <th class="py-3 font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                                @forelse($payout->event->payouts as $hist)
                                    <tr>
                                        <td class="py-3 font-bold">
                                            {{ $hist->payout_type?->label() ?? 'Final' }}
                                        </td>
                                        <td class="py-3 text-slate-600 dark:text-slate-300">
                                            {{ $hist->isAdvance() ? 'Rp '.number_format($hist->requested_amount, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="py-3 font-black text-slate-900 dark:text-white">
                                            Rp {{ number_format($hist->isAdvance() ? ($hist->approved_amount ?? 0) : $hist->net_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider border {{ $hist->statusColor() }}">
                                                {{ $hist->statusLabel() }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-slate-400 font-medium">
                                            {{ $hist->created_at->translatedFormat('d M Y, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 text-center text-slate-400 italic">Tidak ada riwayat pencairan dana</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- 4-Eyes Action Panel --}}
            <section class="lg:col-span-1 glass-panel p-8 rounded-[2rem] space-y-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-1.5 h-4 bg-violet-500 rounded-full"></div>
                    <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest">Reviu & Aksi</h3>
                </div>

                <div class="space-y-6">
                    {{-- Step 1: Review/Approve --}}
                    <div class="relative pl-6 pb-6 border-l-2 {{ in_array($payout->status, [\App\Enums\PayoutStatus::Processing, \App\Enums\PayoutStatus::Completed, \App\Enums\PayoutStatus::Rejected]) ? 'border-emerald-500' : 'border-slate-200 dark:border-slate-800' }}">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-4 border-white dark:border-slate-900 {{ in_array($payout->status, [\App\Enums\PayoutStatus::Processing, \App\Enums\PayoutStatus::Completed, \App\Enums\PayoutStatus::Rejected]) ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                        
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Langkah 1: Persetujuan</div>
                        @if($payout->status === \App\Enums\PayoutStatus::Pending)
                            @if($payout->isAdvance())
                                {{-- Approval Form for Advance Payout --}}
                                <form action="{{ route('admin.payouts.approve-advance', $payout) }}" method="POST" class="mt-3 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Jumlah Disetujui (Rupiah)</label>
                                        <input type="number" name="approved_amount" required
                                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500/20 dark:text-white"
                                               value="{{ $payout->requested_amount }}" max="{{ $summary['available_advance_amount'] }}">
                                    </div>
                                    <button type="submit" 
                                            class="w-full py-2.5 rounded-xl bg-violet-600 text-white text-xs font-bold hover:bg-violet-700 transition-all shadow-lg shadow-violet-500/20">
                                        Setujui & Cairkan Uang Muka
                                    </button>
                                </form>

                                {{-- Rejection Form for Advance Payout --}}
                                <form action="{{ route('admin.payouts.reject-advance', $payout) }}" method="POST" class="mt-4 border-t border-slate-100 dark:border-slate-800 pt-4 space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="text-[10px] font-bold text-rose-400 uppercase tracking-widest block mb-1">Alasan Penolakan</label>
                                        <textarea name="rejection_reason" required rows="3"
                                                  class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-rose-500/20 dark:text-white"
                                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                                    </div>
                                    <button type="submit" 
                                            class="w-full py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition-all shadow-lg shadow-rose-500/20">
                                        Tolak Pengajuan
                                    </button>
                                </form>
                            @else
                                {{-- Regular Approval for Final Payout --}}
                                <form action="{{ route('admin.payouts.approve', $payout) }}" method="POST" class="mt-3">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" @disabled($payout->missing_bank_details) 
                                            class="w-full py-2.5 rounded-xl bg-violet-600 text-white text-xs font-bold hover:bg-violet-700 transition-all shadow-lg shadow-violet-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                        Setujui Pencairan Akhir
                                    </button>
                                </form>
                            @endif
                        @elseif($payout->reviewed_by)
                            <div class="text-xs text-slate-500 mt-1">
                                {{ $payout->status === \App\Enums\PayoutStatus::Rejected ? 'Ditolak' : 'Disetujui' }} oleh: {{ $payout->reviewer->name }}
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $payout->reviewed_at->translatedFormat('d M Y, H:i') }}</div>
                        @elseif($payout->status === \App\Enums\PayoutStatus::Completed)
                            <div class="text-xs text-slate-500 mt-1">Disetujui Otomatis (Sistem)</div>
                            @if($payout->disbursed_at)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $payout->disbursed_at->translatedFormat('d M Y, H:i') }}</div>
                            @endif
                        @else
                            <div class="text-xs text-slate-400 mt-1 italic">
                                {{ $payout->statusLabel() }}
                            </div>
                        @endif
                    </div>

                    {{-- Step 2: Confirm Selesai --}}
                    <div class="relative pl-6 border-l-2 {{ ($payout->disbursed_by || $payout->status === \App\Enums\PayoutStatus::Completed) ? 'border-emerald-500' : 'border-transparent' }}">
                        <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-4 border-white dark:border-slate-900 {{ ($payout->disbursed_by || $payout->status === \App\Enums\PayoutStatus::Completed) ? 'bg-emerald-500' : 'bg-slate-300 dark:bg-slate-700' }}"></div>
                        
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Langkah 2: Konfirmasi Selesai</div>
                        @if($payout->disbursed_by)
                            <div class="text-xs text-slate-500 mt-1">Dikonfirmasi oleh: {{ $payout->disburser->name }}</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">{{ $payout->disbursed_at->translatedFormat('d M Y, H:i') }}</div>
                        @elseif($payout->status === \App\Enums\PayoutStatus::Completed)
                            <div class="text-xs text-slate-500 mt-1">Selesai Otomatis (Sistem)</div>
                            @if($payout->disbursed_at)
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $payout->disbursed_at->translatedFormat('d M Y, H:i') }}</div>
                            @endif
                        @elseif($payout->status === \App\Enums\PayoutStatus::Processing)
                            <div class="space-y-4 mt-3">
                                @if ($errors->any())
                                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs">
                                        <ul class="list-disc pl-4 space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form action="{{ route('admin.payouts.disburse', $payout) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Bukti Transfer (Gambar, Maks 5MB)</label>
                                        <input type="file" name="proof_photo" required accept="image/*"
                                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500/20 dark:text-white file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100 dark:file:bg-violet-950 dark:file:text-violet-300">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Nomor Referensi Transfer</label>
                                        <input type="text" name="transfer_reference" required
                                               class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-violet-500/20 dark:text-white"
                                               placeholder="e.g. TRF-123456789"
                                               value="{{ old('transfer_reference') }}">
                                    </div>
                                    <button type="submit" 
                                            class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-600/20 cursor-pointer">
                                        Unggah & Konfirmasi Selesai
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-xs text-slate-400 mt-1 italic">
                                @if($payout->status === \App\Enums\PayoutStatus::Rejected)
                                    Pengajuan Ditolak
                                @elseif($payout->status === \App\Enums\PayoutStatus::Voided)
                                    Pencairan Dibatalkan
                                @elseif($payout->status === \App\Enums\PayoutStatus::Failed)
                                    Pencairan Gagal
                                @else
                                    Menunggu persetujuan langkah 1...
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-admin-layout>
