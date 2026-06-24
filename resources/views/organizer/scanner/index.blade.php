@extends('layouts.organizer')
@section('title', 'Pusat Pemindaian')
@section('page-title', 'Pusat Pemindaian')

@section('content')
<div class="space-y-6">
    <x-organizer.page-hero
        eyebrow="Validasi Lapangan"
        title="Pusat Pemindaian Tiket dan Suvenir"
        description="Gunakan modul pemindaian untuk memverifikasi pengunjung, mencatat kehadiran, dan mengonfirmasi klaim suvenir secara cepat."
        icon="qr-code" />

    @if(!$activeEvent)
        <!-- Landing: Select Event Context State -->
        <div class="max-w-2xl mx-auto mt-8">
            <div class="glass-panel rounded-3xl p-8 border border-slate-200 dark:border-white/10 shadow-xl bg-white dark:bg-slate-900/40">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-violet-500/10 flex items-center justify-center text-violet-600 dark:text-violet-400 mx-auto mb-4">
                        <x-heroicon-o-qr-code class="w-8 h-8" />
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white">Pilih Acara Terlebih Dahulu</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Untuk memulai pemindaian tiket atau klaim produk suvenir, silakan pilih acara aktif Anda dari menu di bawah ini.</p>
                </div>

                <form action="{{ route('organizer.scanner.select') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="event_id" class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Daftar Event Aktif</label>
                        <select name="event_id" id="event_id" required class="w-full px-4 py-3 rounded-2xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 text-slate-900 dark:text-white">
                            <option value="">-- Pilih Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }} ({{ $event->status->label() }})</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="w-full inline-flex h-12 items-center justify-center rounded-2xl bg-violet-600 text-white dark:bg-violet-500 dark:text-slate-950 font-bold hover:bg-violet-700 dark:hover:bg-violet-400 transition duration-200 cursor-pointer shadow-md">
                        Konfirmasi & Masuk Pemindai
                    </button>
                </form>
            </div>
        </div>
    @else
        <!-- Header Selection Widget -->
        <div id="scanner-header" class="glass-panel rounded-3xl p-5 border border-slate-200 dark:border-white/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white dark:bg-slate-900/40">
            <div class="flex items-start gap-4">
                <img src="{{ $activeEvent->banner_image ? Storage::url($activeEvent->banner_image) : asset('img/eobanner.png') }}" class="w-16 h-10 rounded-lg object-cover border border-slate-200 dark:border-white/5 shadow-sm shrink-0" alt="">
                <div>
                    <h4 class="font-extrabold text-slate-900 dark:text-white leading-tight">{{ $activeEvent->name }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-wider
                            {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'bg-rose-500/10 text-rose-500' : 'bg-violet-600/10 text-violet-600 dark:text-violet-400' }}">
                            {{ $activeEvent->status->label() }}
                        </span>
                        <span>•</span>
                        <span>{{ \Carbon\Carbon::parse($activeEvent->event_date)->translatedFormat('d M Y') }}</span>
                    </p>
                </div>
            </div>

            <form action="{{ route('organizer.scanner.select') }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                @csrf
                <select name="event_id" onchange="this.form.submit()" class="w-full sm:w-64 px-4 py-2 text-xs rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ $activeEvent->id === $event->id ? 'selected' : '' }}>{{ $event->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($activeEvent->status === \App\Enums\EventStatus::Cancelled)
            <!-- Event Cancelled Warning Banner -->
            <div class="p-5 bg-rose-500/10 rounded-3xl border border-rose-500/25 flex items-start gap-4 shadow-sm text-rose-800 dark:text-rose-400">
                <x-heroicon-o-exclamation-triangle class="w-6 h-6 shrink-0 mt-0.5 text-rose-500" />
                <div>
                    <h4 class="font-extrabold text-sm tracking-tight mb-1">Event Telah Dibatalkan</h4>
                    <p class="text-xs leading-relaxed font-medium">Acara ini telah dibatalkan secara resmi. Seluruh pemindaian tiket gate check-in dan redemption merchandise diblokir dan tidak diizinkan untuk dilanjutkan.</p>
                </div>
            </div>
        @endif        <!-- Main Dashboard Split Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" 
             x-data="{
                 mode: 'gate',
                 token: '',
                 activeScanner: null,
                 isScanning: false,
                 wasScanning: false,
                 scanResult: null,
                 scanMessage: '',
                 scanDetail: '',
                 scanTime: '',
                 resultTimeout: null,

                 // Two-step confirmation state
                 pendingConfirmation: false,
                 scannedToken: '',
                 pendingName: '',
                 pendingDetail: '',

                 // Switch-event state
                 isWrongEvent: false,
                 targetEventId: null,
                 targetEventName: '',

                 initScanner() {
                     if (this.isScanning) return;
                     this.isScanning = true;
                     this.scanResult = null;

                     this.$nextTick(() => {
                         const html5QrcodeScanner = new Html5Qrcode('reader');
                         this.activeScanner = html5QrcodeScanner;
                         
                         html5QrcodeScanner.start(
                             { facingMode: 'environment' },
                             {
                                 fps: 15,
                                 qrbox: { width: 250, height: 250 }
                             },
                             (decodedText) => {
                                 this.processScan(decodedText);
                             },
                             () => {} // silent
                         ).catch(err => {
                             console.error(err);
                             this.isScanning = false;
                             alert('Gagal mengakses kamera: ' + err);
                         });
                     });
                 },

                 stopScanner() {
                     if (!this.isScanning) return;
                     if (this.activeScanner) {
                         this.activeScanner.stop().then(() => {
                             this.isScanning = false;
                             this.activeScanner = null;
                         }).catch(err => console.error(err));
                     }
                 },

                 processScan(scannedToken) {
                     this.wasScanning = true;
                     this.stopScanner();
                     this.sendValidation(scannedToken, false);
                 },

                 submitManual() {
                     if (!this.token.trim()) return;
                     this.wasScanning = this.isScanning;
                     const currentToken = this.token;
                     this.token = '';
                     if (this.isScanning) this.stopScanner();
                     this.sendValidation(currentToken, false);
                 },

                 clearResultTimeout() {
                     if (this.resultTimeout) {
                         clearTimeout(this.resultTimeout);
                         this.resultTimeout = null;
                     }
                 },

                 sendValidation(scannedToken, confirm = false) {
                     this.scannedToken = scannedToken;
                     this.clearResultTimeout();
                     
                     axios.post('{{ route('organizer.scanner.validate') }}', {
                         mode: this.mode,
                         token: scannedToken,
                         confirm: confirm
                     }).then(response => {
                         const data = response.data;
                         
                         if (data.status === 'pending_confirmation') {
                             this.pendingConfirmation = true;
                             this.pendingName = data.name;
                             this.pendingDetail = data.detail;
                             this.isWrongEvent = false;
                             // Camera remains stopped, user confirms via UI buttons
                         } else if (data.status === 'confirmed') {
                             this.pendingConfirmation = false;
                             this.scanResult = 'success';
                             this.scanMessage = data.message;
                             this.scanDetail = `${data.name} • ${data.detail}`;
                             this.scanTime = data.time;

                             this.appendActivity(data.name, data.detail, data.time, 'success');

                             // Refresh stats
                             this.refreshStats();

                             this.resultTimeout = setTimeout(() => {
                                 this.clearResult();
                                 if (this.wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) {
                                     this.initScanner();
                                 }
                             }, 4000);
                         }
                     }).catch(error => {
                         const msg = error.response?.data?.message || 'Gagal memproses kode QR.';
                         const isWrong = error.response?.data?.wrong_event || false;
                         const targetId = error.response?.data?.target_event_id || null;
                         const targetName = error.response?.data?.target_event_name || '';

                         this.scanResult = 'error';
                         this.scanMessage = msg;
                         
                         if (isWrong) {
                             this.isWrongEvent = true;
                             this.targetEventId = targetId;
                             this.targetEventName = targetName;
                             this.scanDetail = targetName;
                         } else {
                             this.isWrongEvent = false;
                             this.targetEventId = null;
                             this.targetEventName = '';
                             this.scanDetail = scannedToken;
                         }
                         
                         this.scanTime = new Date().toLocaleTimeString('id-ID');

                         this.appendActivity('Scan Gagal', msg, this.scanTime, 'error');

                         if (!isWrong) {
                             this.resultTimeout = setTimeout(() => {
                                 if (this.scanResult === 'error') {
                                     this.clearResult();
                                     if (this.wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) {
                                         this.initScanner();
                                     }
                                 }
                             }, 4000);
                         }
                     });
                 },

                 confirmAction() {
                     const token = this.scannedToken;
                     this.pendingConfirmation = false;
                     this.sendValidation(token, true);
                 },

                 cancelAction() {
                     this.pendingConfirmation = false;
                     this.scannedToken = '';
                     this.pendingName = '';
                     this.pendingDetail = '';
                     if (this.wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) {
                         this.initScanner();
                     }
                 },

                 switchAndSubmit(targetId, token) {
                     this.clearResult();
                     axios.post('{{ route('organizer.scanner.select') }}', {
                         event_id: targetId
                     }, {
                         headers: { 'X-Requested-With': 'XMLHttpRequest' }
                     }).then(() => {
                         // Reload header & stats dynamically
                         this.refreshStats();
                         
                         // Run check validation in the new event context
                         this.sendValidation(token, false);
                     }).catch(err => {
                         console.error(err);
                         alert('Gagal mengganti event.');
                         if (this.wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) {
                             this.initScanner();
                         }
                     });
                 },

                 clearResult() {
                     this.clearResultTimeout();
                     this.scanResult = null;
                     this.scanMessage = '';
                     this.scanDetail = '';
                     this.scanTime = '';
                     this.isWrongEvent = false;
                     this.targetEventId = null;
                     this.targetEventName = '';
                 },

                 appendActivity(name, detail, time, status) {
                     const logList = document.getElementById('log-list');
                     if (!logList) return;

                     const item = document.createElement('div');
                     item.className = 'flex justify-between items-center text-sm p-3 bg-white/70 dark:bg-white/5 rounded-xl border border-slate-200/50 dark:border-white/5';

                     const statusText = status === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500';

                     item.innerHTML = `
                         <div>
                             <p class='font-bold text-slate-955 dark:text-white'>${name}</p>
                             <p class='text-slate-500 dark:text-slate-400 text-xs mt-0.5'>${detail}</p>
                         </div>
                         <span class='${statusText} font-bold text-xs'>${time}</span>
                     `;

                     logList.insertBefore(item, logList.firstChild);
                     if (logList.children.length > 10) {
                         logList.removeChild(logList.lastChild);
                     }
                 },

                 refreshStats() {
                     // Reload dynamic widgets and headers asynchronously
                     fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                         .then(res => res.text())
                         .then(html => {
                             const parser = new DOMParser();
                             const doc = parser.parseFromString(html, 'text/html');
                             ['tickets-stat', 'merch-stat', 'scanner-header'].forEach(id => {
                                 const current = document.getElementById(id);
                                 const updated = doc.getElementById(id);
                                 if (current && updated) {
                                     current.innerHTML = updated.innerHTML;
                                 }
                             });
                         });
                 }
             }">
            
            <!-- Left Column: Scanner Viewport & Controls -->
            <div class="glass-panel lg:col-span-2 rounded-3xl p-6 border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-slate-900/40">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <!-- Mode Tabs -->
                    <div class="flex rounded-xl bg-slate-100 dark:bg-slate-955 p-1 border border-slate-200/50 dark:border-slate-800 w-full sm:w-auto">
                        <button type="button" @click="mode = 'gate'; stopScanner();" :class="mode === 'gate' ? 'bg-violet-600 text-white dark:bg-violet-500 dark:text-slate-955' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'" class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Pindai Tiket Masuk
                        </button>
                        <button type="button" @click="mode = 'merchandise'; stopScanner();" :class="mode === 'merchandise' ? 'bg-violet-600 text-white dark:bg-violet-500 dark:text-slate-955' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white'" class="flex-1 sm:flex-none px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer text-center">
                            Klaim Suvenir
                        </button>
                    </div>

                    <div class="flex items-center gap-2.5 self-end sm:self-auto">
                        <span class="relative flex h-2 w-2">
                            <span :class="isScanning ? 'animate-ping' : ''" class="absolute inline-flex h-full w-full rounded-full opacity-75 {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'bg-rose-500' : 'bg-emerald-400' }}"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'bg-rose-500' : ( 'bg-emerald-500' ) }}"></span>
                        </span>
                        <span x-text="isScanning ? 'Kamera Aktif' : 'Kamera Mati'" class="text-xs font-bold text-slate-500 dark:text-slate-400">Kamera Mati</span>
                        
                        <button type="button" x-show="isScanning" x-cloak @click="stopScanner()"
                            class="px-2.5 py-1 rounded-lg text-[10px] font-bold border border-rose-500/30 text-rose-500 hover:bg-rose-500 hover:text-white transition cursor-pointer">
                            Hentikan Kamera
                        </button>
                    </div>
                </div>

                <!-- Single Camera Viewport -->
                <div class="relative w-full bg-slate-955 rounded-2xl overflow-hidden border border-slate-200/50 dark:border-slate-800 flex flex-col items-center justify-center shadow-inner transition-all duration-300">
                    <div id="reader" class="w-full" x-show="isScanning"></div>
                    
                    <div class="text-center z-10 p-6" x-show="!isScanning">
                        <x-heroicon-o-camera class="w-12 h-12 text-violet-300 mx-auto mb-2 {{ $activeEvent->status !== \App\Enums\EventStatus::Cancelled ? 'animate-pulse' : '' }}" />
                        <h4 class="text-slate-955 dark:text-white font-extrabold">Kamera Peninjau</h4>
                        <p class="text-violet-500 dark:text-violet-200 text-xs mt-1 max-w-xs mx-auto">
                            @if($activeEvent->status === \App\Enums\EventStatus::Cancelled)
                                Scanner diblokir karena event telah dibatalkan.
                            @else
                                Aktifkan kamera untuk memindai kode QR.
                            @endif
                        </p>
                        @if($activeEvent->status !== \App\Enums\EventStatus::Cancelled)
                            <button type="button" @click="initScanner()" class="mt-4 inline-flex items-center gap-1.5 px-5 py-2.5 bg-violet-600 hover:bg-violet-750 text-white rounded-xl text-xs font-extrabold transition duration-200 cursor-pointer shadow-lg shadow-violet-600/30">
                                <x-heroicon-o-video-camera class="w-4 h-4" />
                                Mulai Pemindaian
                            </button>
                        @endif
                    </div>

                </div>

                <!-- Result Overlays (Full Screen / Toast) -->
                <template x-teleport="body">
                    <div>
                        <!-- Success Toast -->
                        <div x-cloak x-show="scanResult === 'success'" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-x-10"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-x-0"
                             x-transition:leave-end="opacity-0 translate-x-10"
                             class="fixed top-6 right-6 z-[100] max-w-sm w-full bg-emerald-500 text-white rounded-2xl shadow-2xl border border-emerald-400 p-4 flex items-start gap-4">
                            <x-heroicon-o-check-circle class="w-8 h-8 shrink-0 text-white animate-bounce" />
                            <div class="flex-1">
                                <h3 class="text-base font-black" x-text="scanMessage">Berhasil</h3>
                                <p class="text-sm font-semibold mt-0.5 text-emerald-50" x-text="scanDetail"></p>
                                <span class="text-xs opacity-75 mt-1 font-mono block" x-text="scanTime"></span>
                            </div>
                            <button @click="clearResult(); if (wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) { initScanner(); }" class="shrink-0 p-1 hover:bg-emerald-600 rounded-lg transition">
                                <x-heroicon-m-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Confirmation Overlay (Modal) -->
                        <div x-cloak x-show="pendingConfirmation" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
                            <!-- Backdrop -->
                            <div x-show="pendingConfirmation" x-transition.opacity class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
                            
                            <!-- Modal Card -->
                            <div x-show="pendingConfirmation" 
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                                 class="relative w-full max-w-sm glass-panel bg-white dark:bg-slate-900/80 rounded-[2rem] border border-slate-200 dark:border-white/10 shadow-2xl p-6 flex flex-col items-center text-center">
                                
                                <div class="w-16 h-16 rounded-full bg-violet-500/10 flex items-center justify-center text-violet-600 dark:text-violet-400 mb-4">
                                    <x-heroicon-o-information-circle class="w-8 h-8 animate-pulse" />
                                </div>
                                
                                <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight" x-text="mode === 'gate' ? 'Verifikasi Tiket' : 'Verifikasi Klaim Produk'">Verifikasi Tiket</h3>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Mohon verifikasi data berikut sebelum menyetujui.</p>
                                
                                <div class="mt-6 mb-6 w-full bg-slate-50 dark:bg-slate-950/50 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-left space-y-4">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Nama Pemegang / Pembeli</span>
                                        <p class="text-base font-extrabold text-slate-900 dark:text-white mt-0.5" x-text="pendingName"></p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500" x-text="mode === 'gate' ? 'Kategori Tiket' : 'Suvenir / Produk'">Kategori Tiket</span>
                                        <p class="text-sm font-semibold text-violet-600 dark:text-violet-400 mt-0.5" x-text="pendingDetail"></p>
                                    </div>
                                </div>

                                <div class="flex gap-3 w-full">
                                    <button type="button" @click="cancelAction()" class="flex-1 py-3 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 rounded-xl text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="button" @click="confirmAction()" class="flex-1 py-3 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-violet-600/30 transition cursor-pointer">
                                        Konfirmasi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Error Toast -->
                        <div x-cloak x-show="scanResult === 'error'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 translate-x-10"
                             x-transition:enter-end="opacity-100 translate-x-0"
                             x-transition:leave="transition ease-in duration-200"
                             x-transition:leave-start="opacity-100 translate-x-0"
                             x-transition:leave-end="opacity-0 translate-x-10"
                             class="fixed top-6 right-6 z-[100] max-w-sm w-full bg-rose-500 text-white rounded-2xl shadow-2xl border border-rose-400 p-4 flex items-start gap-4">
                            <x-heroicon-o-x-circle class="w-8 h-8 shrink-0 text-white" />
                            <div class="flex-1">
                                <h3 class="text-base font-black" x-text="scanMessage">Gagal</h3>
                                <p class="text-xs font-mono opacity-90 break-all mt-1" x-text="scanDetail"></p>
                                <span class="text-xs opacity-75 mt-1 font-mono block" x-text="scanTime"></span>

                                <!-- Quick Switch Button for Wrong Event -->
                                <button type="button" x-show="isWrongEvent" @click="switchAndSubmit(targetEventId, scannedToken)" class="mt-3 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-white text-rose-600 hover:bg-rose-50 rounded-xl text-xs font-extrabold shadow-sm transition cursor-pointer">
                                    <x-heroicon-o-arrow-path class="w-4 h-4" />
                                    Ganti & Validasi
                                </button>
                            </div>
                            <button @click="clearResult(); if (wasScanning && {{ $activeEvent->status === \App\Enums\EventStatus::Cancelled ? 'true' : 'false' }} === false) { initScanner(); }" class="shrink-0 p-1 hover:bg-rose-600 rounded-lg transition cursor-pointer">
                                <x-heroicon-m-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </template>

                <!-- Manual Input Fallback -->
                @if($activeEvent->status !== \App\Enums\EventStatus::Cancelled)
                    <div class="mt-5 border-t border-slate-200 dark:border-white/5 border-dashed pt-5">
                        <label class="block text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-2">Entri Token Manual</label>
                        <form @submit.prevent="submitManual" class="flex lg:flex-row gap-2 flex-col">
                            <input type="text" x-model="token" placeholder="Masukkan token tiket/merchandise..." class="flex-1 px-4 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500/20 text-slate-900 dark:text-white">
                            <button type="submit" class="px-5 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-xs font-bold hover:bg-slate-800 dark:hover:bg-slate-100 transition cursor-pointer">
                                Kirim
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Right Column: Sidebar Panels -->
            <div class="space-y-6">
                <!-- Recent Activities list -->
                <div class="glass-panel rounded-3xl p-6 border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-slate-900/40">
                    <h3 class="font-extrabold tracking-tight text-slate-950 dark:text-white mb-4">Aktivitas Pemindaian</h3>
                    <div id="log-list" class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
                        @forelse($recentScans as $log)
                            <div class="flex justify-between items-center text-sm p-3 bg-white/70 dark:bg-white/5 rounded-xl border border-slate-200/50 dark:border-white/5">
                                <div>
                                    <p class="font-bold text-slate-955 dark:text-white">{{ $log['name'] }}</p>
                                    <p class="text-slate-500 dark:text-slate-400 text-xs mt-0.5">{{ $log['detail'] }}</p>
                                </div>
                                <span class="text-emerald-600 dark:text-emerald-400 font-bold text-xs">{{ $log['time'] }}</span>
                            </div>
                        @empty
                            <div class="text-center py-6 text-xs text-slate-400 font-medium">Belum ada aktivitas scan terverifikasi.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Statistics Card: Tickets -->
                <div id="tickets-stat" class="glass-panel rounded-3xl p-6 border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-slate-900/40">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center text-violet-600 dark:text-violet-400">
                            <x-heroicon-o-ticket class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none">Statistik Tiket Masuk</h4>
                            <p class="text-lg font-black text-slate-955 dark:text-white mt-1">Tiket Terverifikasi</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-slate-500">
                            <span>Selesai Dipindai</span>
                            <span class="text-slate-950 dark:text-white">{{ $stats['tickets_scanned'] }} / {{ $stats['tickets_sold'] }} Tiket</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 border border-slate-200/50 dark:border-slate-800">
                            @php
                                $ticketPercent = $stats['tickets_sold'] > 0 ? ($stats['tickets_scanned'] / $stats['tickets_sold']) * 100 : 0;
                            @endphp
                            <div class="bg-gradient-to-r from-violet-600 to-fuchsia-500 h-2 rounded-full" style="width: {{ $ticketPercent }}%"></div>
                        </div>
                        <p class="text-sm text-slate-400 dark:text-slate-500 leading-normal font-medium pt-1">
                            Persentase kehadiran: {{ number_format($ticketPercent, 1, ',', '.') }}% dari total tiket terjual.
                        </p>
                    </div>
                </div>

                <!-- Statistics Card: Merchandise -->
                <div id="merch-stat" class="glass-panel rounded-3xl p-6 border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-slate-900/40">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 dark:text-amber-400">
                            <x-heroicon-o-shopping-bag class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest leading-none">Klaim Suvenir</h4>
                            <p class="text-lg font-black text-slate-955 dark:text-white mt-1">Produk Diambil</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-slate-500">
                            <span>Diambil</span>
                            <span class="text-slate-955 dark:text-white">{{ $stats['merch_claimed'] }} / {{ $stats['merch_sold'] }} Produk</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-2 border border-slate-200/50 dark:border-slate-800">
                            @php
                                $merchPercent = $stats['merch_sold'] > 0 ? ($stats['merch_claimed'] / $stats['merch_sold']) * 100 : 0;
                            @endphp
                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2 rounded-full" style="width: {{ $merchPercent }}%"></div>
                        </div>
                        <p class="text-sm text-slate-400 dark:text-slate-500 leading-normal font-medium pt-1">
                            {{ number_format($merchPercent, 1, ',', '.') }}% dari total pesanan suvenir.
                        </p>
                    </div>
                </div>

                <!-- Sync Status info -->
                <div class="glass-panel rounded-3xl p-6 border border-slate-200 dark:border-white/10 shadow-sm bg-white dark:bg-slate-900/40 text-xs text-slate-400 dark:text-slate-500">
                    <p class="leading-relaxed font-medium">Sinkronisasi otomatis diaktifkan. Setiap kali pemindaian berhasil dilakukan, status lokal akan segera disimpan secara aman pada database dan log aktivitas akan diperbarui.</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
@endpush

@push('styles')
<style>
    #reader video {
        border-radius: 1rem;
        object-fit: cover;
        width: 100% !important;
        height: auto !important;
    }
</style>
@endpush
