@extends('layouts.organizer')
@section('title', 'Pusat Pemindaian')
@section('page-title', 'Pusat Pemindaian')

@section('content')
<div class="space-y-6">
    <p class="text-gray-500">Pilih modul pemindaian untuk memulai verifikasi pengunjung atau klaim produk.</p>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Gate Verifikasi --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Gate Verifikasi</h3>
                    <p class="text-sm text-gray-500">Scanner Tiket Event</p>
                </div>
            </div>
            <div class="relative w-full bg-gray-900 rounded-xl overflow-hidden border-2 border-purple-500 aspect-video flex items-center justify-center cursor-pointer"
                 id="checkinScanner" onclick="document.getElementById('checkinInput').focus()">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-purple-500/10 to-transparent"></div>
                <div class="text-center z-10">
                    <svg class="w-12 h-12 text-purple-400 mx-auto mb-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-white font-bold">Menunggu QR Code...</p>
                    <p class="text-purple-300/80 text-xs mt-1">Arahkan kamera ke kode QR</p>
                </div>
            </div>
            <input type="text" id="checkinInput" class="opacity-0 h-0 w-0 pointer-events-none" placeholder="hidden">
            <div id="checkinResult" class="mt-4 hidden p-4 bg-purple-50 rounded-xl border border-purple-200">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold">Check-in Berhasil!</p>
                        <p id="visitorName" class="text-sm text-gray-600">Budi Santoso</p>
                    </div>
                </div>
            </div>
            <div id="checkinError" class="mt-4 hidden p-4 bg-red-50 rounded-xl border border-red-200">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="font-semibold">QR Tidak Valid</p>
                        <p id="errorMessage" class="text-sm text-gray-600">Tiket sudah dipindai atau tidak ditemukan.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Scan QR Merchandise --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a2 2 0 00-1.414.586l-2 2a2 2 0 01-2.828 0l-2-2a2 2 0 00-1.414-.586H4"/></svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Scan QR Merchandise</h3>
                    <p class="text-sm text-gray-500">Klaim produk pengunjung</p>
                </div>
            </div>
            <div class="relative w-full bg-gray-900 rounded-xl overflow-hidden border-2 border-orange-500 aspect-video flex items-center justify-center cursor-pointer"
                 id="merchandiseScanner" onclick="document.getElementById('merchandiseInput').focus()">
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-orange-500/10 to-transparent"></div>
                <div class="text-center z-10">
                    <svg class="w-12 h-12 text-orange-400 mx-auto mb-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p class="text-white font-bold">Menunggu QR Code...</p>
                    <p class="text-orange-300/80 text-xs mt-1">Arahkan kamera ke kode QR</p>
                </div>
            </div>
            <input type="text" id="merchandiseInput" class="opacity-0 h-0 w-0 pointer-events-none" placeholder="hidden">
            <div id="merchandiseResult" class="mt-4 hidden p-4 bg-orange-50 rounded-xl border border-orange-200">
                <h4 class="font-semibold">Detail Pengambilan</h4>
                <p id="merchandiseVisitor" class="text-sm text-gray-600">Budi Santoso</p>
                <div class="mt-2 text-sm space-y-1">
                    <span class="block">Kaos Festival (L) x1</span>
                    <span class="block">Topi Festival x1</span>
                </div>
                <button class="w-full mt-3 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600">Konfirmasi Pengambilan</button>
            </div>
        </div>
    </div>

    {{-- Aktivitas Terkini --}}
    <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
        <h3 class="font-semibold text-gray-900 mb-4">Aktivitas Terkini</h3>
        <div class="space-y-3 max-h-60 overflow-y-auto">
            @foreach(range(1, 5) as $i)
            <div class="flex justify-between items-center text-sm p-3 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900">Pengunjung #{{ $i }}</p>
                    <p class="text-gray-500">{{ $i % 2 == 0 ? 'Check-in Berhasil • VIP' : 'Klaim T-Shirt • Size L' }}</p>
                </div>
                <span class="text-green-600 font-medium">14:{{ 30 - $i }}:00</span>
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-right">
            <a href="#" class="text-sm font-medium text-purple-600 hover:text-purple-800">Lihat Semua Log →</a>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between text-xs text-gray-400">
            <span>Server: Singapore (SG-1)</span>
            <span>Auto-sync: On</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('checkinInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const value = this.value.trim();
            if (value) {
                showCheckinResult(value);
                this.value = '';
            }
        }
    });

    function showCheckinResult(token) {
        const result = document.getElementById('checkinResult');
        const error = document.getElementById('checkinError');
        if (token.includes('ERR')) {
            error.classList.remove('hidden');
            result.classList.add('hidden');
            setTimeout(() => error.classList.add('hidden'), 3000);
        } else {
            result.classList.remove('hidden');
            error.classList.add('hidden');
            setTimeout(() => result.classList.add('hidden'), 5000);
        }
    }

    document.getElementById('merchandiseInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            const value = this.value.trim();
            if (value) {
                showMerchandiseResult(value);
                this.value = '';
            }
        }
    });

    function showMerchandiseResult(token) {
        const result = document.getElementById('merchandiseResult');
        result.classList.remove('hidden');
        setTimeout(() => result.classList.add('hidden'), 8000);
    }
</script>
@endpush