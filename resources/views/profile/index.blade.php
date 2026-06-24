<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
            {{ __('Profil Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="px-4 sm:px-6 lg:px-8 space-y-6 mx-auto">
            {{-- Header halaman --}}
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Profil Pengguna</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Kelola informasi profil, preferensi, dan keamanan akun Anda.</p>
            </div>

            {{-- 1. Kartu Informasi Profil Utama (Glass Panel Admin-style) --}}
            <div class="glass-panel p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                    @if($user->profile_photo_path)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full object-cover shadow-lg shadow-violet-600/20 shrink-0">
                    @else
                        <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-600/20 text-4xl font-bold text-white shrink-0">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1 w-full text-center md:text-left">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-2">
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $user->name }}</h3>
                                <p class="text-slate-500 dark:text-slate-400 font-medium">{{ $user->email }}</p>
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
                                Perbarui Informasi
                            </button>
                            <button x-data @click="$dispatch('open-panel', 'update-password')" class="flex items-center gap-2 px-4 py-2 glass-panel text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-xl text-sm font-bold transition-colors border border-slate-200 dark:border-slate-800 cursor-pointer">
                                <x-heroicon-s-key class="w-4 h-4" />
                                Ubah Kata Sandi
                            </button>
                            <button x-data @click="$dispatch('open-panel', 'delete-account')" class="flex items-center gap-2 px-4 py-2 bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white rounded-xl text-sm font-bold transition-all border border-rose-500/25 cursor-pointer">
                                <x-heroicon-s-trash class="w-4 h-4" />
                                Hapus Akun
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-800 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Peran Akses</span>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">Pengguna</p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Tanggal Bergabung</span>
                        <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $user->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status Verifikasi</span>
                        @if ($user->hasVerifiedEmail())
                            <div class="flex items-center gap-1.5 text-emerald-500 font-medium text-sm">
                                <x-heroicon-s-check-circle class="w-4 h-4" />
                                Terverifikasi
                            </div>
                        @else
                            <div class="flex items-center gap-1.5 text-amber-500 font-medium text-sm">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4" />
                                Belum Terverifikasi
                            </div>
                        @endif
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

            {{-- 2. Section Pesanan Saya --}}
            <div class="glass-panel p-8 rounded-[2rem] border border-slate-200 dark:border-slate-800">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Aktivitas Pembelian Terakhir</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Riwayat pesanan tiket acara Anda akhir-akhir ini.</p>
                    </div>
                    <a href="{{ route('pesanan.index') }}" data-link class="inline-flex items-center gap-2 rounded-xl bg-violet-50 px-4 py-2 text-sm font-bold text-violet-750 transition hover:bg-violet-100 hover:text-violet-800 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20">
                        Lihat Semua
                        <x-heroicon-o-arrow-right class="h-4 w-4" />
                    </a>
                </div>

                @if($recentOrders->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/40 py-12 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-violet-100 dark:bg-violet-500/20 text-violet-500 dark:text-violet-400 mb-4">
                        <x-heroicon-o-ticket class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Belum Ada Transaksi</h3>
                    <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">Anda belum melakukan pembelian tiket apapun. Temukan acara menarik sekarang.</p>
                    <a href="{{ route('events.index') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-violet-700">
                        Jelajahi Acara
                    </a>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-500 dark:text-slate-400">
                        <thead class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-4 py-3 font-bold">Acara</th>
                                <th scope="col" class="px-4 py-3 font-bold">Tanggal</th>
                                <th scope="col" class="px-4 py-3 font-bold">Status</th>
                                <th scope="col" class="px-4 py-3 font-bold">Total</th>
                                <th scope="col" class="px-4 py-3 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($recentOrders as $order)
                            @php
                                $badgeClass = match($order->status->value) {
                                    'paid', 'sukses', 'success' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20',
                                    'pending' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20',
                                    'cancelled', 'batal' => 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20',
                                    default => 'text-slate-700 dark:bg-slate-850 dark:text-slate-300 border border-slate-200 dark:border-slate-750',
                                };
                            @endphp
                            <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-white/5">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
                                            @if($order->event?->banner_image)
                                                <img src="{{ Storage::url($order->event->banner_image) }}" alt="" class="h-full w-full object-cover">
                                            @else
                                                <x-heroicon-o-ticket class="h-5 w-5 text-slate-400 dark:text-slate-500" />
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white line-clamp-1">{{ $order->event->name ?? 'Acara Dihapus' }}</div>
                                            <div class="text-[10px] text-slate-400 font-mono mt-0.5">#{{ $order->midtrans_order_id ?? strtoupper(substr($order->id, 0, 8)) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-medium text-slate-700 dark:text-slate-300">
                                    {{ $order->created_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider {{ $badgeClass }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 font-extrabold text-slate-900 dark:text-white">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <a href="{{ route('pesanan.show', $order->id) }}" data-link class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-1.5 text-xs font-bold text-slate-700 dark:text-slate-300 shadow-sm transition-all hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-violet-600 dark:hover:text-violet-400">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    @push('modals')
    {{-- Update Profile Modal --}}
    <div x-data="{ open: false }" 
         @open-panel.window="if ($event.detail === 'update-profile') open = true"
         @keydown.escape.window="open = false"
         x-init="if ({{ $errors->any() && !$errors->updatePassword->isNotEmpty() && !$errors->userDeletion->isNotEmpty() ? 'true' : 'false' }}) open = true"
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
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Perbarui Informasi</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Ubah nama dan alamat email profil Anda.</p>
                    </div>
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ photoName: null, photoPreview: null }">
                    @csrf
                    @method('patch')

                    {{-- Profile Photo Upload Field --}}
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1">Foto Profil</label>
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

                    <div class="space-y-2">
                        <x-input-label for="name" :value="__('Nama Lengkap')" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="email" :value="__('Alamat Surel')" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                                <p class="text-sm text-amber-800 dark:text-amber-200">
                                    Alamat email Anda belum diverifikasi.
                                    <button form="send-verification" class="underline font-bold hover:text-amber-900 dark:hover:text-amber-100 cursor-pointer">
                                        Kirim ulang verifikasi.
                                    </button>
                                </p>
                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-1 font-medium text-xs text-emerald-600 dark:text-emerald-400">
                                        Tautan verifikasi baru telah dikirim!
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if ($user->pending_email)
                            <div class="mt-2 p-3 bg-amber-50 dark:bg-amber-500/10 rounded-xl border border-amber-200 dark:border-amber-500/20">
                                <p class="text-xs text-amber-850 dark:text-amber-200 font-medium">
                                    Email baru Anda (<span class="font-bold text-amber-900 dark:text-amber-100">{{ $user->pending_email }}</span>) sedang menunggu verifikasi.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                        <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">
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
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">Pastikan Anda menggunakan kata sandi yang kuat dan unik.</p>
                    </div>
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                    @csrf
                    @method('put')

                    <div class="space-y-2">
                        <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" autocomplete="current-password" />
                        <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" autocomplete="new-password" />
                        <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
                    </div>

                    <div class="space-y-2">
                        <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" autocomplete="new-password" />
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

    {{-- Delete Account Modal --}}
    <div x-data="{ open: false }" 
         @open-panel.window="if ($event.detail === 'delete-account') open = true"
         @keydown.escape.window="open = false"
         x-init="if ({{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }}) open = true"
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
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Apakah Anda yakin ingin menghapus akun?</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Setelah akun Anda dihapus, semua data akan dihapus secara permanen.</p>
                    </div>
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors cursor-pointer">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <form method="post" action="{{ route('profile.destroy') }}" class="space-y-6">
                    @csrf
                    @method('delete')

                    <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                        Silakan masukkan kata sandi Anda untuk mengonfirmasi penghapusan akun secara permanen.
                    </p>

                    <div class="space-y-2">
                        <x-input-label for="delete_password" value="{{ __('Kata Sandi') }}" class="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1" />
                        <x-text-input id="delete_password" name="password" type="password" class="mt-1 block w-full glass-panel !bg-transparent rounded-2xl border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white" placeholder="Kata Sandi" required />
                        <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800 mt-6">
                        <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer">
                            Batal
                        </button>
                        <x-danger-button class="rounded-xl px-6 py-2.5 text-sm font-bold uppercase tracking-widest cursor-pointer border-none bg-rose-600 hover:bg-rose-700 text-white">
                            Hapus Akun Permanen
                        </x-danger-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endpush
</x-app-layout>
