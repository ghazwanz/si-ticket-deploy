@extends('layouts.organizer')
@section('title', 'Rincian Keuangan: ' . $event->name)
@section('page-title', 'Rincian Keuangan Acara')

@section('content')
<div class="space-y-6 animate-fade-in">
    {{-- Back Link --}}
    <div>
        <a href="{{ route('organizer.payouts.index') }}" data-link class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors group">
            <div class="p-2 rounded-xl glass-panel group-hover:scale-110 transition-transform">
                <x-heroicon-o-chevron-left class="w-4 h-4" />
            </div>
            <span class="text-xs font-bold uppercase tracking-widest">Kembali ke Daftar Pencairan Dana</span>
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 rounded-xl border border-emerald-500/20 text-sm font-bold flex items-center gap-2">
            <x-heroicon-o-check class="w-5 h-5 text-emerald-500" />
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-500/10 text-rose-700 dark:text-rose-300 rounded-xl border border-rose-500/20 text-sm font-bold flex items-center gap-2">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-rose-500" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Manual Settlement Warning --}}
    @if($event->manual_settlement_required)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400 text-sm font-bold flex items-center gap-3">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6 flex-shrink-0" />
            <div>
                <div class="font-extrabold uppercase tracking-wide">Penyelesaian Keuangan Manual Diperlukan</div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Acara ini telah dibatalkan setelah pembayaran uang muka selesai dicarikan. Hubungi admin JoinFest secara langsung untuk penyelesaian keuangan manual.</div>
            </div>
        </div>
    @endif

    {{-- Failed Payout Warning --}}
    @php
        $hasFailedPayout = $payouts->where('status', \App\Enums\PayoutStatus::Failed)->isNotEmpty();
    @endphp
    @if($hasFailedPayout)
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400 text-sm font-bold flex flex-col md:flex-row md:items-center justify-between gap-4 animate-fade-in">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-circle class="w-6 h-6 flex-shrink-0 mt-0.5" />
                <div>
                    <div class="font-extrabold uppercase tracking-wide">Pencairan Dana Gagal!</div>
                    <div class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-0.5">Penyaluran dana ke rekening Anda mengalami kegagalan. Silakan periksa kembali data nomor rekening bank Anda di pengaturan, lalu hubungi Admin JoinFest jika detail bank Anda sudah benar.</div>
                </div>
            </div>
            <a href="{{ route('organizer.settings') }}" data-link
               class="px-4 py-2 rounded-xl bg-rose-600 text-white hover:bg-rose-700 text-xs font-bold transition-all whitespace-nowrap self-start md:self-center">
                Periksa Rekening Bank &rarr;
            </a>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        {{-- Financial Metrics Breakdown --}}
        <div class="lg:col-span-2 glass-panel p-6 rounded-2xl space-y-6">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Ringkasan Pendapatan</p>
                <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">Rincian Finansial Acara</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 border-y border-slate-100 dark:border-slate-800 py-6">
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Pendapatan Kotor</div>
                    <div class="text-xl font-black text-slate-900 dark:text-white">Rp {{ number_format($summary['gross_sales'], 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-400 font-medium mt-1">Seluruh penjualan tiket berbayar yang lunas.</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Biaya Layanan ({{ $summary['fee_percentage'] }}%)</div>
                    <div class="text-xl font-black text-rose-500">Rp {{ number_format($summary['estimated_platform_fee'], 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-400 font-medium mt-1">Potongan biaya layanan.</div>
                </div>
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pendapatan Bersih (Net)</div>
                    <div class="text-xl font-black text-emerald-500">Rp {{ number_format($summary['estimated_net_sales'], 0, ',', '.') }}</div>
                    <div class="text-[10px] text-slate-400 font-medium mt-1">Total estimasi dana bersih yang akan diterima.</div>
                </div>
            </div>

            {{-- Advance Payout Math --}}
            <div class="space-y-4">
                <h4 class="text-sm font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">Detail Pencairan Uang Muka</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl bg-violet-600/5 border border-violet-500/15">
                        <div class="text-[9px] font-bold text-violet-400 uppercase tracking-widest mb-1">Maks. Batas Uang Muka ({{ $summary['advance_limit_percent'] }}%)</div>
                        <div class="text-lg font-bold text-slate-900 dark:text-white">Rp {{ number_format($summary['max_advance_limit'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                        <div class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-1">Total Uang Muka Diterima</div>
                        <div class="text-lg font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($summary['completed_advance_total'], 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-600/5 border border-emerald-500/15">
                        <div class="text-[9px] font-bold text-emerald-500 uppercase tracking-widest mb-1">Uang Muka Tersedia</div>
                        <div class="text-lg font-black text-emerald-500">Rp {{ number_format($summary['available_advance_amount'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            {{-- Bank account snapshot details --}}
            @php
                $organizerProfile = $event->organizer->organizerProfile;
                $hasCompleteBank = !empty($organizerProfile?->bank_name) && !empty($organizerProfile?->bank_account_number) && !empty($organizerProfile?->bank_account_name);
            @endphp
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800 flex items-start gap-3">
                <x-heroicon-o-credit-card class="w-5 h-5 text-slate-400 mt-0.5" />
                <div class="space-y-1">
                    <div class="text-sm font-bold text-slate-900 dark:text-white">Informasi Rekening Penerima</div>
                    @if($hasCompleteBank)
                        <div class="text-sm text-slate-500 dark:text-slate-400">
                            {{ $organizerProfile->bank_name }} - No. Rekening: <span class="font-mono font-bold tracking-wider">{{ $organizerProfile->bank_account_number }}</span> (an. {{ $organizerProfile->bank_account_name }})
                        </div>
                        <div class="text-xs text-slate-500 italic">Untuk memperbarui rekening penerima, silakan perbarui lewat <a href="{{ route('organizer.settings') }}" class="text-violet-500 font-bold hover:underline" data-link>Halaman Pengaturan</a>.</div>
                    @else
                        <div class="text-xs text-rose-500 font-bold">Data rekening bank Anda belum lengkap!</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Pencairan uang muka tidak dapat dilakukan sebelum data rekening dilengkapi.</div>
                        <div class="mt-1">
                            <a href="{{ route('organizer.settings') }}" class="inline-flex text-xs font-bold text-violet-500 hover:underline" data-link>Lengkapi data rekening sekarang &rarr;</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Request Payout Action Form --}}
        <div class="lg:col-span-1 glass-panel p-6 rounded-2xl flex flex-col justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Aksi Finansial</p>
                
                @if($event->status === \App\Enums\EventStatus::Completed || $event->status === 'completed')
                    <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-955 dark:text-white">Ajukan Payout Final</h3>
                    
                    @php
                        // Remaining balance calculation
                        $feePercentage = (float) \App\Models\SystemSetting::get('platform_fee_percent', 5.00);
                        $grossAmount = $event->orders()->where('status', 'paid')->sum('total_amount');
                        $platformFee = (int) round($grossAmount * ($feePercentage / 100));
                        $netSales = $grossAmount - $platformFee;
                        $completedAdvanceTotal = (int) $event->payouts()
                            ->where('payout_type', \App\Enums\PayoutType::Advance)
                            ->where('status', \App\Enums\PayoutStatus::Completed)
                            ->sum('approved_amount');
                        $remainingBalance = $netSales - $completedAdvanceTotal;
                        if ($remainingBalance < 0) {
                            $remainingBalance = 0;
                        }

                        $hasFinalPayout = $payouts->where('payout_type', \App\Enums\PayoutType::Final)->isNotEmpty();
                        $isFinalEligible = $hasCompleteBank && !$hasFinalPayout && $remainingBalance > 0 && $event->organizer->is_active;
                    @endphp

                    @if($isFinalEligible)
                        <div class="mt-6 space-y-4">
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/40 border border-slate-100 dark:border-slate-800">
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Jumlah Pencairan Akhir</div>
                                <div class="text-xl font-black text-emerald-600 dark:text-emerald-400">Rp {{ number_format($remainingBalance, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-slate-400 font-medium mt-1">Sisa hasil penjualan bersih (setelah dikurangi platform fee dan uang muka).</div>
                            </div>

                            <form action="{{ route('organizer.payouts.request', $event) }}" method="POST" class="space-y-4">
                                @csrf
                                <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-800/40 text-violet-600 dark:text-violet-400 text-[10px] leading-relaxed font-semibold flex items-start gap-2">
                                    <x-heroicon-o-information-circle class="w-4 h-4 flex-shrink-0 text-violet-500 dark:text-violet-400 mt-0.5" />
                                    <div>
                                        <span class="font-extrabold uppercase">Info:</span> Pengajuan ini akan diproses secara manual oleh tim admin. Pastikan nomor rekening Anda sudah benar.
                                    </div>
                                </div>

                                <button type="submit"
                                        class="w-full py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-xs font-bold hover:from-emerald-700 hover:to-teal-700 transition-all shadow-md cursor-pointer">
                                    Ajukan Payout Final Sekarang
                                </button>
                            </form>
                        </div>
                    @else
                        @php
                            $finalPayoutModel = $payouts->where('payout_type', \App\Enums\PayoutType::Final)->first();
                        @endphp
                        @if($finalPayoutModel)
                            <div class="mt-6 p-4 rounded-xl border {{ $finalPayoutModel->statusColor() }} space-y-2">
                                <div class="font-extrabold uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                                    <x-heroicon-o-information-circle class="w-4 h-4" />
                                    <span>Status Payout Final: {{ $finalPayoutModel->statusLabel() }}</span>
                                </div>
                                <p class="text-xs opacity-90 leading-relaxed">
                                    @if($finalPayoutModel->status === \App\Enums\PayoutStatus::Pending)
                                        Pengajuan payout final Anda telah masuk ke sistem dan sedang menunggu reviu/persetujuan oleh admin JoinFest.
                                    @elseif($finalPayoutModel->status === \App\Enums\PayoutStatus::Processing)
                                        Pengajuan payout final Anda telah disetujui dan saat ini sedang dalam proses transfer dana oleh tim keuangan.
                                    @elseif($finalPayoutModel->status === \App\Enums\PayoutStatus::Completed)
                                        Dana bersih acara Anda telah sepenuhnya dikirim ke rekening terdaftar.
                                    @elseif($finalPayoutModel->status === \App\Enums\PayoutStatus::Failed)
                                        Proses pencairan dana mengalami kendala atau gagal. Harap periksa rekening Anda dan hubungi admin.
                                    @else
                                        Status pengajuan saat ini: {{ $finalPayoutModel->statusLabel() }}.
                                    @endif
                                </p>
                                @if($finalPayoutModel->transfer_reference)
                                    <div class="border-t border-current/10 pt-2 mt-2 flex flex-col gap-1 text-[11px] font-medium">
                                        <div>Ref Transfer: <span class="font-mono font-bold">{{ $finalPayoutModel->transfer_reference }}</span></div>
                                        @if($finalPayoutModel->proof_photo_url)
                                            <div>
                                                <a href="{{ $finalPayoutModel->proof_photo_url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider hover:underline">
                                                    <x-heroicon-o-document-magnifying-glass class="w-3.5 h-3.5" />
                                                    Lihat Bukti Transfer
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-xs text-slate-400 space-y-3 leading-relaxed">
                                <div class="font-bold uppercase tracking-wider text-slate-500">Kenapa tidak bisa mengajukan?</div>
                                <ul class="list-disc list-inside space-y-1 text-[11px]">
                                    <li class="{{ $hasCompleteBank ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold' }}">Data Rekening Bank Lengkap</li>
                                    <li class="{{ !$hasFinalPayout ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400' }}">Belum Pernah Mengajukan Payout Final</li>
                                    <li class="{{ $remainingBalance > 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400' }}">Sisa Saldo Bersih > Rp 0 (Sisa: Rp {{ number_format($remainingBalance, 0, ',', '.') }})</li>
                                    <li class="{{ $event->organizer->is_active ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold' }}">Akun Penyelenggara Aktif</li>
                                </ul>
                            </div>
                        @endif
                    @endif

                @elseif($event->status === \App\Enums\EventStatus::Published || $event->status === 'published')
                    <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-955 dark:text-white">Ajukan Uang Muka</h3>
                    
                    @php
                        $isEligible = $event->status === \App\Enums\EventStatus::Published 
                            && !$event->isStarted()
                            && !$event->manual_settlement_required
                            && $summary['gross_sales'] > 0
                            && $summary['available_advance_amount'] > 0
                            && $hasCompleteBank
                            && $payouts->where('payout_type', \App\Enums\PayoutType::Advance)->whereIn('status', [\App\Enums\PayoutStatus::Pending, \App\Enums\PayoutStatus::Processing])->isEmpty();
                    @endphp

                    @if($isEligible)
                        <form action="{{ route('organizer.payouts.request', $event) }}" method="POST" class="mt-6 space-y-4">
                            @csrf
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Jumlah Pengajuan (IDR)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                                    <input type="number" name="amount" required min="1" max="{{ $summary['available_advance_amount'] }}"
                                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 dark:text-white"
                                           value="{{ old('amount', $summary['available_advance_amount']) }}">
                                </div>
                                @error('amount')
                                    <p class="text-rose-500 text-[11px] mt-1 font-bold">{{ $message }}</p>
                                @enderror
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 font-medium">Batas maksimum: Rp {{ number_format($summary['available_advance_amount'], 0, ',', '.') }}</p>
                            </div>

                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-1">Alasan Pengajuan (Min. 20 Karakter)</label>
                                <textarea name="reason" required minlength="20" rows="4"
                                          class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 dark:text-white"
                                          placeholder="Jelaskan kebutuhan operasional atau biaya persiapan acara...">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-800/40 text-amber-600 dark:text-amber-400 text-[10px] leading-relaxed font-semibold flex items-start gap-2">
                                <x-heroicon-o-exclamation-triangle class="w-4 h-4 flex-shrink-0 text-amber-500 dark:text-amber-400 mt-0.5" />
                                <div>
                                    <span class="font-extrabold uppercase">Penting:</span> Pengajuan pembayaran awal (advance payout) tidak membebaskan Penyelenggara dari tanggung jawab atas pembatalan acara, komunikasi pembeli, atau kewajiban pengembalian dana penuh kepada pembeli tiket.
                                </div>
                            </div>

                            <button type="submit"
                                    class="w-full py-3 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-xs font-bold hover:from-violet-700 hover:to-indigo-700 transition-all shadow-md cursor-pointer">
                                Ajukan Uang Muka Sekarang
                            </button>
                        </form>
                    @else
                        @php
                            $activeAdvanceModel = $payouts->where('payout_type', \App\Enums\PayoutType::Advance)->first();
                        @endphp
                        @if($activeAdvanceModel)
                            <div class="mt-6 p-4 rounded-xl border {{ $activeAdvanceModel->statusColor() }} space-y-2">
                                <div class="font-extrabold uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                                    <x-heroicon-o-information-circle class="w-4 h-4" />
                                    <span>Status Uang Muka: {{ $activeAdvanceModel->statusLabel() }}</span>
                                </div>
                                <p class="text-xs opacity-90 leading-relaxed">
                                    @if($activeAdvanceModel->status === \App\Enums\PayoutStatus::Pending)
                                        Pengajuan uang muka Anda sedang menunggu persetujuan admin JoinFest.
                                    @elseif($activeAdvanceModel->status === \App\Enums\PayoutStatus::Processing)
                                        Pengajuan uang muka Anda sedang dalam proses transfer dana oleh admin.
                                    @elseif($activeAdvanceModel->status === \App\Enums\PayoutStatus::Completed)
                                        Uang muka telah berhasil dicairkan ke rekening Anda.
                                    @elseif($activeAdvanceModel->status === \App\Enums\PayoutStatus::Failed)
                                        Pencairan uang muka gagal. Harap hubungi admin.
                                    @else
                                        Status pengajuan saat ini: {{ $activeAdvanceModel->statusLabel() }}.
                                    @endif
                                </p>
                                @if($activeAdvanceModel->transfer_reference)
                                    <div class="border-t border-current/10 pt-2 mt-2 flex flex-col gap-1 text-[11px] font-medium">
                                        <div>Ref Transfer: <span class="font-mono font-bold">{{ $activeAdvanceModel->transfer_reference }}</span></div>
                                        @if($activeAdvanceModel->proof_photo_url)
                                            <div>
                                                <a href="{{ $activeAdvanceModel->proof_photo_url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider hover:underline">
                                                    <x-heroicon-o-document-magnifying-glass class="w-3.5 h-3.5" />
                                                    Lihat Bukti Transfer
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-xs text-slate-400 space-y-3 leading-relaxed">
                                <div class="font-bold uppercase tracking-wider text-slate-500">Kenapa tidak bisa mengajukan?</div>
                                <ul class="list-disc list-inside space-y-1 text-[11px]">
                                    <li class="{{ $event->status === \App\Enums\EventStatus::Published ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Status Acara: Published</li>
                                    <li class="{{ !$event->isStarted() ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold' }}">Acara Belum Dimulai</li>
                                    <li class="{{ $summary['gross_sales'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Sudah Ada Tiket Terjual</li>
                                    <li class="{{ $summary['available_advance_amount'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">Uang Muka Tersedia > Rp 0</li>
                                    <li class="{{ $hasCompleteBank ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold' }}">Data Rekening Bank Lengkap</li>
                                    @php
                                        $hasActiveAdvance = $payouts->where('payout_type', \App\Enums\PayoutType::Advance)->whereIn('status', [\App\Enums\PayoutStatus::Pending, \App\Enums\PayoutStatus::Processing])->isNotEmpty();
                                    @endphp
                                    <li class="{{ !$hasActiveAdvance ? 'text-emerald-600 dark:text-emerald-400 font-bold' : 'text-rose-600 dark:text-rose-400 font-bold' }}">Tidak Ada Pengajuan Aktif</li>
                                </ul>
                            </div>
                        @endif
                    @endif
                @else
                    <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-955 dark:text-white">Pengajuan Payout</h3>
                    <div class="mt-6 p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-xs text-slate-400 leading-relaxed text-center">
                        <x-heroicon-o-no-symbol class="w-8 h-8 text-slate-400 mx-auto mb-2" />
                        <div class="font-bold uppercase tracking-wider text-slate-500">Pengajuan Payout Tidak Tersedia</div>
                        <p class="mt-1">Pencairan dana tidak dapat diajukan karena status acara saat ini adalah <span class="font-bold text-rose-600 dark:text-rose-400">{{ $event->status->label() ?? $event->status }}</span>.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- History of all payouts requested --}}
    <div class="glass-panel p-6 rounded-2xl shadow-sm border border-white/60 dark:border-white/10 overflow-hidden">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Riwayat Keuangan</p>
            <h3 class="mt-1 text-lg font-extrabold tracking-tight text-slate-950 dark:text-white">Riwayat Pengajuan Payout</h3>
        </div>

        <div class="overflow-x-auto mt-4">
            <table class="w-full text-left text-sm border-collapse">
                <thead class="bg-slate-50 dark:bg-slate-900/60 text-slate-500 text-xs uppercase tracking-widest border-b border-slate-100 dark:border-slate-800">
                    <tr>
                        <th class="px-6 py-3 font-bold">Tipe</th>
                        <th class="px-6 py-3 font-bold">Jumlah Diajukan</th>
                        <th class="px-6 py-3 font-bold">Jumlah Disetujui</th>
                        <th class="px-6 py-3 font-bold">Status</th>
                        <th class="px-6 py-3 font-bold">Ref Transfer / Bukti</th>
                        <th class="px-6 py-3 font-bold">Tanggal Pengajuan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                    @forelse($payouts as $payout)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40 transition-colors">
                            <td class="px-6 py-4 font-bold">
                                {{ $payout->payout_type?->label() ?? 'Final' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $payout->isAdvance() ? 'Rp ' . number_format($payout->requested_amount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-6 py-4 font-black text-slate-900 dark:text-white">
                                Rp {{ number_format($payout->isAdvance() ? ($payout->approved_amount ?? 0) : $payout->net_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wider border {{ $payout->statusColor() }}">
                                    {{ $payout->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                @if($payout->transfer_reference)
                                    <span class="font-mono block mb-1">{{ $payout->transfer_reference }}</span>
                                    @if($payout->proof_photo_url)
                                        <a href="{{ $payout->proof_photo_url }}" target="_blank" class="inline-flex items-center gap-1 text-[10px] text-violet-600 hover:text-violet-500 font-bold uppercase tracking-wider">
                                            <x-heroicon-o-document-magnifying-glass class="w-3.5 h-3.5" />
                                            Bukti Transfer
                                        </a>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-400 font-medium">
                                {{ $payout->created_at->format('d M Y, H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-400 italic">
                                Belum ada riwayat pengajuan pencairan dana untuk acara ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
