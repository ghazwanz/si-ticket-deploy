@extends('layouts.organizer')
@section('title', 'Pengaturan Penyelenggara')
@section('page-title', 'PENGATURAN PENYELENGGARA')

@section('content')
<div class="space-y-6">
    {{-- Header Halaman --}}
    <div>
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Akun</h2>
        <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Kelola informasi profil penanggung jawab, detail organisasi, dan rekening bank Anda.</p>
    </div>

    {{-- Main Read-Only Profile & Organization Details Card --}}
    <div class="glass-panel p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            @if($user->profile_photo_path)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover shadow-lg shadow-violet-600/20 shrink-0">
            @else
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-violet-600 to-fuchsia-600 flex items-center justify-center shadow-lg shadow-violet-600/20 text-4xl font-bold text-white shrink-0">
                    {{ substr($user->organizerProfile?->organization_name ?? $user->name, 0, 1) }}
                </div>
            @endif
            <div class="flex-1 w-full text-center md:text-left">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-2">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->organizerProfile?->organization_name ?? 'Nama Organisasi Belum Diatur' }}</h3>
                        <p class="text-slate-500 dark:text-slate-400 font-medium">Penanggung Jawab: {{ $user->name }} ({{ $user->email }})</p>
                    </div>
                    <div class="mt-2 md:mt-0 flex justify-center gap-2">
                        <span class="px-3 py-1 bg-violet-500/10 text-violet-600 dark:text-violet-400 text-xs font-bold uppercase tracking-widest rounded-full border border-violet-500/20">
                            {{ $user->role->label() }}
                        </span>
                        @if($user->is_active)
                        <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-450 text-xs font-bold uppercase tracking-widest rounded-full border border-emerald-500/20">
                            Aktif
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-4">
                    <button x-data @click="$dispatch('open-panel', 'update-profile')" class="flex items-center gap-2 px-4 py-2 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-sm font-bold hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors cursor-pointer">
                        <x-heroicon-s-pencil-square class="w-4 h-4" />
                        Perbarui Informasi & Bank
                    </button>
                    <button x-data @click="$dispatch('open-panel', 'update-password')" class="flex items-center gap-2 px-4 py-2 glass-panel text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-xl text-sm font-bold transition-colors border border-slate-200 dark:border-slate-800 cursor-pointer">
                        <x-heroicon-s-key class="w-4 h-4" />
                        Ubah Kata Sandi
                    </button>
                </div>
            </div>
        </div>
        
        <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nomor Telepon</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->phone ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Kontak Resmi</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->official_contact ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Dokumen Legalitas</span>
                @if($user->organizerProfile?->legality_document)
                    <p class="text-sm font-semibold text-violet-600 dark:text-violet-400">
                        <a href="{{ Storage::url($user->organizerProfile->legality_document) }}" target="_blank" class="hover:underline inline-flex items-center gap-1">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                            Lihat Dokumen
                        </a>
                    </p>
                @else
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">-</p>
                @endif
            </div>
            <div class="col-span-1 md:col-span-2 lg:col-span-3">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Alamat Organisasi</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white whitespace-pre-line">{{ $user->organizerProfile?->organization_address ?? '-' }}</p>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800 grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nama Rekening Bank</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->bank_name ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Nomor Rekening</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->bank_account_number ?? '-' }}</p>
            </div>
            <div>
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Pemilik Rekening</span>
                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $user->organizerProfile?->bank_account_name ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if ($user->pending_email)
    <div class="glass-panel p-6 rounded-2xl border border-amber-500/30 bg-amber-500/5 flex items-start gap-4">
        <div class="p-2 bg-amber-500/10 rounded-xl text-amber-500 shrink-0">
            <x-heroicon-o-envelope-open class="w-6 h-6" />
        </div>
        <div class="flex-1">
            <h4 class="text-sm font-bold text-amber-600 dark:text-amber-400">Verifikasi Email Baru Tertunda</h4>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">Kami telah mengirimkan tautan verifikasi ke email baru Anda: <span class="font-bold text-slate-800 dark:text-white">{{ $user->pending_email }}</span>. Email lama Anda (<span class="italic text-slate-700 dark:text-slate-300">{{ $user->email }}</span>) akan tetap aktif sampai Anda memverifikasi email baru.</p>
        </div>
    </div>
    @endif
</div>

@push('modals')
{{-- Update Profile & Bank Modal --}}
<div x-data="{ open: false }" 
     @open-panel.window="if ($event.detail === 'update-profile') open = true"
     @keydown.escape.window="open = false"
     x-init="if ({{ $errors->any() && !$errors->updatePassword->isNotEmpty() ? 'true' : 'false' }}) open = true"
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
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="glass-panel w-full max-w-2xl rounded-[2rem] shadow-2xl relative overflow-hidden z-10 border border-slate-200 dark:border-slate-800">
         
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Perbarui Informasi & Bank</h2>
                    <p class="text-sm text-slate-700 dark:text-slate-400 mt-1">Ubah profil lembaga, kontak, dan nomor rekening penarikan dana.</p>
                </div>
                <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('organizer.settings.profile') }}" enctype="multipart/form-data" class="space-y-4" x-data="{ photoName: null, photoPreview: null }">
                @csrf
                @method('PUT')

                {{-- Profile Photo Upload Field --}}
                <div class="space-y-2 mb-4">
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Foto Profil</label>
                    <input type="file" id="profile_photo" name="profile_photo" class="hidden" accept="image/*"
                           x-ref="photo"
                           @change="
                                $refs.removePhotoInput.value = '0';
                                photoName = $refs.photo.files[0].name;
                                const reader = new FileReader();
                                reader.onload = (e) => {
                                    photoPreview = e.target.result;
                                };
                                reader.readAsDataURL($refs.photo.files[0]);
                           ">
                    
                    <div class="flex items-center gap-4">
                        <!-- Current Avatar or Preview -->
                        <div class="relative w-16 h-16 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-800 flex items-center justify-center shrink-0">
                            <template x-if="!photoPreview">
                                @if($user->profile_photo_path)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl font-bold text-slate-400">{{ substr($user->name, 0, 1) }}</span>
                                @endif
                            </template>
                            <template x-if="photoPreview && photoPreview !== 'REMOVE'">
                                <img :src="photoPreview" alt="Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="photoPreview === 'REMOVE'">
                                <span class="text-xl font-bold text-slate-400">{{ substr($user->name, 0, 1) }}</span>
                            </template>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="$refs.photo.click()" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-bold transition-colors cursor-pointer border-none">
                                Pilih Foto
                            </button>
                            
                            @if($user->profile_photo_path)
                                <button type="button" @click="$refs.removePhotoInput.value = '1'; photoPreview = 'REMOVE'; $refs.photo.value = '';" class="px-3 py-1.5 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl text-xs font-bold transition-all border border-rose-500/25 cursor-pointer">
                                    Hapus Foto
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Hidden Input to track photo removal -->
                    <input type="hidden" name="remove_photo" x-ref="removePhotoInput" value="0">
                    <x-input-error class="mt-2" :messages="$errors->get('profile_photo')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="name" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Penanggung Jawab</label>
                        <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                        <x-input-error class="mt-1" :messages="$errors->get('name')" />
                    </div>
                    <div class="space-y-1">
                        <label for="email" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Email Bisnis</label>
                        <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                        <x-input-error class="mt-1" :messages="$errors->get('email')" />
                        
                        @if ($user->pending_email)
                            <div class="mt-1 p-2 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                                <p class="text-[10px] text-amber-800 dark:text-amber-200 font-medium">
                                    Email baru (<span class="font-bold">{{ $user->pending_email }}</span>) sedang menunggu verifikasi.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="organization_name"class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Organisasi</label>
                        <x-text-input id="organization_name" name="organization_name" type="text" :value="old('organization_name', $user->organizerProfile?->organization_name)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                        <x-input-error class="mt-1" :messages="$errors->get('organization_name')" />
                    </div>
                    <div class="space-y-1">
                        <label for="phone" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Nomor Telepon</label>
                        <x-text-input id="phone" name="phone" type="text" :value="old('phone', $user->organizerProfile?->phone)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                        <x-input-error class="mt-1" :messages="$errors->get('phone')" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="official_contact" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Kontak Resmi</label>
                        <x-text-input id="official_contact" name="official_contact" type="text" :value="old('official_contact', $user->organizerProfile?->official_contact)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                        <x-input-error class="mt-1" :messages="$errors->get('official_contact')" />
                    </div>
                    <div class="space-y-1">
                        <label for="legality_document" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Dokumen Legalitas (PDF/JPG/PNG, Max 5MB)</label>
                        <input id="legality_document" type="file" name="legality_document" class="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-violet-50/10 file:text-violet-700 dark:file:bg-violet-500/10 dark:file:text-violet-400 hover:file:bg-violet-100 dark:hover:file:bg-violet-500/20 bg-transparent border border-slate-200 dark:border-slate-800 rounded-2xl py-2 px-4 focus:outline-none">
                        @if($user->organizerProfile?->legality_document)
                            <p class="text-xs text-slate-550 mt-1">Dokumen saat ini: <a href="{{ Storage::url($user->organizerProfile->legality_document) }}" target="_blank" class="text-violet-650 dark:text-violet-400 hover:underline">Lihat Dokumen</a></p>
                        @endif
                        <x-input-error class="mt-1" :messages="$errors->get('legality_document')" />
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="organization_address" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Alamat Organisasi</label>
                    <textarea id="organization_address" name="organization_address" rows="2" required
                              class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm p-3 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 transition-shadow">{{ old('organization_address', $user->organizerProfile?->organization_address) }}</textarea>
                    <x-input-error class="mt-1" :messages="$errors->get('organization_address')" />
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800/80 border-dashed my-4 pt-4">
                    <p class="text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest mb-3">Informasi Rekening Bank (Untuk Pencairan Dana)</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-1">
                            <label for="bank_name" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1"">Nama Bank</label>
                            <x-text-input id="bank_name" name="bank_name" type="text" :value="old('bank_name', $user->organizerProfile?->bank_name)" placeholder="BCA / Mandiri" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                            <x-input-error class="mt-1" :messages="$errors->get('bank_name')" />
                        </div>
                        <div class="space-y-1">
                            <label for="bank_account_number" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Nomor Rekening</label>
                            <x-text-input id="bank_account_number" name="bank_account_number" type="text" :value="old('bank_account_number', $user->organizerProfile?->bank_account_number)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                            <x-input-error class="mt-1" :messages="$errors->get('bank_account_number')" />
                        </div>
                        <div class="space-y-1">
                            <label for="bank_account_name" class="block text-xs font-bold text-neutral-900 dark:text-slate-500 uppercase tracking-widest ml-1">Nama Pemilik</label>
                            <x-text-input id="bank_account_name" name="bank_account_name" type="text" :value="old('bank_account_name', $user->organizerProfile?->bank_account_name)" required class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white text-sm" />
                            <x-input-error class="mt-1" :messages="$errors->get('bank_account_name')" />
                        </div>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-neutral-900 dark:hover:text-white transition-colors cursor-pointer">
                        Batal
                    </button>
                    <x-primary-button class="rounded-xl bg-violet-600 hover:bg-violet-700 px-6 py-2.5 text-sm font-bold uppercase tracking-widest text-white border-none cursor-pointer">
                        Simpan Perubahan
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Update Password Modal --}}
<div x-data="{ open: false }" 
     @open-panel.window="if ($event.detail === 'update-password') open = true"
     @keydown.escape.window="open = false"
     x-init="if ({{ $errors->updatePassword->isNotEmpty() ? 'true' : 'false' }}) open = true"
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
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="glass-panel w-full max-w-lg rounded-[2rem] shadow-2xl relative overflow-hidden z-10 border border-slate-200 dark:border-slate-800">
         
        <div class="p-6 sm:p-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Ubah Kata Sandi</h2>
                    <p class="text-sm text-slate-700 dark:text-slate-400 mt-1">Pastikan Anda menggunakan kata sandi yang kuat dan unik.</p>
                </div>
                <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('organizer.settings.password') }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="space-y-2">
                    <x-input-label for="current_password" :value="__('Kata Sandi Saat Ini')" class="text-xs font-bold text-eutral-900 dark:text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" required />
                    <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="password" :value="__('Kata Sandi Baru')" class="text-xs font-bold text-eutral-900 dark:text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" required />
                    <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                </div>

                <div class="space-y-2">
                    <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="text-xs font-bold text-eutral-900 dark:text-slate-400 uppercase tracking-widest ml-1" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" required />
                    <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                    <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">
                        Batal
                    </button>
                    <x-primary-button class="rounded-xl bg-violet-600 hover:bg-violet-700 px-6 py-2.5 text-sm font-bold uppercase tracking-widest text-white border-none cursor-pointer">
                        Perbarui Keamanan
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush
@endsection