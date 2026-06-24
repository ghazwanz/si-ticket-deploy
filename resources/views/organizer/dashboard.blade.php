@extends('layouts.organizer')
@section('title', 'Panel Kontrol Penyelenggara')
@section('page-title', 'Ringkasan Penyelenggara')

@section('content')
<div class="space-y-6">
    <x-organizer.page-hero
        eyebrow="Panel Kontrol Operasional"
        title="Selamat datang, {{ Auth::user()->name }}"
        description="Pantau performa acara, penjualan tiket, dan tren pendapatan dalam satu konsol penyelenggara yang terpadu."
        icon="presentation-chart-line" />

    @if(auth()->user()->organizerProfile?->status === \App\Enums\OrganizerStatus::Pending)
    <div class="glass-panel p-6 rounded-2xl border border-amber-500/20 dark:border-amber-500/30 bg-amber-500/5 dark:bg-amber-500/10 flex items-start gap-4">
        <div class="p-3 bg-amber-500/10 rounded-xl text-amber-500 shrink-0">
            <x-heroicon-o-clock class="w-6 h-6" />
        </div>
        <div class="flex-1">
            <h4 class="text-base font-bold text-amber-800 dark:text-amber-400 font-extrabold tracking-tight">Pendaftaran Menunggu Verifikasi</h4>
            <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 font-medium">
                Akun penyelenggara Anda sedang ditinjau oleh Administrator. Akses ke fitur manajemen acara, pencairan dana, dan pemindai QR akan dibuka secara otomatis setelah pendaftaran disetujui.
            </p>
        </div>
    </div>
    @elseif(auth()->user()->organizerProfile?->status === \App\Enums\OrganizerStatus::Rejected)
    <div class="glass-panel p-6 rounded-2xl border border-rose-500/20 dark:border-rose-500/30 bg-rose-500/5 dark:bg-rose-500/10 flex items-start gap-4">
        <div class="p-3 bg-rose-500/10 rounded-xl text-rose-500 shrink-0">
            <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
        </div>
        <div class="flex-1 md:flex md:items-center md:justify-between gap-4">
            <div>
                <h4 class="text-base font-bold text-rose-800 dark:text-rose-400 font-extrabold tracking-tight">Pendaftaran Ditolak</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300 mt-1 font-medium">
                    Sayang sekali, permohonan pendaftaran penyelenggara Anda ditolak karena alasan berikut:
                </p>
                <div class="mt-2 p-3 bg-rose-500/10 rounded-xl border border-rose-500/25 text-rose-700 dark:text-rose-300 text-sm font-semibold">
                    {{ auth()->user()->organizerProfile->rejection_reason ?? 'Dokumen atau informasi yang diberikan kurang lengkap/valid.' }}
                </div>
                <p class="text-sm text-slate-700 dark:text-slate-300 mt-2 font-medium">
                    Silakan perbarui profil organisasi dan unggah dokumen pendukung yang sesuai untuk mengajukan peninjauan ulang.
                </p>
            </div>
            <div class="mt-4 md:mt-0 shrink-0">
                <a href="{{ route('organizer.settings') }}" data-link class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-650 hover:bg-rose-700 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-rose-650/20 cursor-pointer">
                    <x-heroicon-s-pencil-square class="w-4 h-4" />
                    Perbarui Profil
                </a>
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-organizer.stat-card label="Total Penjualan" value="Rp {{ number_format($stats['total_penjualan'], 0, ',', '.') }}" meta="Akumulasi pembayaran berhasil" icon="banknotes" tone="emerald" />
        <x-organizer.stat-card label="Tiket Terjual" value="{{ number_format($stats['tiket_terjual'], 0, ',', '.') }}" meta="Total seluruh kategori tiket" icon="ticket" tone="violet" />
        <x-organizer.stat-card label="Total Check-in" value="{{ number_format($stats['total_checkin'], 0, ',', '.') }}" meta="Tiket yang telah dipindai" icon="qr-code" tone="fuchsia" />
        <x-organizer.stat-card label="Acara Aktif" value="{{ number_format($stats['acara_aktif'], 0, ',', '.') }}" meta="Acara dengan status terbit" icon="calendar-days" tone="sky" />
        <x-organizer.stat-card label="Perlu Ditinjau" value="{{ number_format($stats['perlu_ditinjau'], 0, ',', '.') }}" meta="Draf atau menunggu persetujuan" icon="exclamation-triangle" tone="amber" />
        <x-organizer.stat-card label="Siap Cair" value="{{ number_format($stats['siap_cair'], 0, ',', '.') }}" meta="Acara selesai & siap dicairkan" icon="banknotes" tone="emerald" />
    </div>

    @include('organizer.dashboard.partials.statistics-chart')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('organizer.dashboard.partials.recent-events')
        </div>

        @include('organizer.dashboard.partials.ticket-distribution')
    </div>
</div>
@endsection
