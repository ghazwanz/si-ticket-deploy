<x-guest-layout>
    <div class="text-center">
        <p class="mb-1 text-xs font-bold uppercase tracking-widest text-violet-650 dark:text-violet-400">JoinFest</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Buat Akun</h1>
        <p class="mt-2 text-sm text-slate-505 dark:text-slate-400">Daftar untuk menemukan dan memesan tiket acara.</p>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
            <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/20 p-4 text-sm text-red-705 dark:text-red-400">
            <div class="flex items-center gap-2 font-semibold mb-2">
                <x-heroicon-s-exclamation-circle class="w-5 h-5 text-red-500" />
                <span>Ada kesalahan pada pengisian formulir</span>
            </div>
            <ul class="list-disc list-inside pl-7 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="mt-8 space-y-5" x-data="{ role: 'user' }">
        @csrf

        {{-- Role Selection --}}
        <div>
            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Saya mendaftar sebagai <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                       :class="role === 'user' ? 'border-violet-600 dark:border-violet-500 bg-violet-50 dark:bg-violet-955/30 text-violet-900 dark:text-violet-300' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-400'">
                    <input type="radio" name="role" value="user" x-model="role" class="sr-only" required>
                    <x-heroicon-o-user class="w-5 h-5" />
                    <span class="text-sm font-semibold">Pengguna</span>
                </label>
                <label class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-3 shadow-sm transition-all"
                       :class="role === 'organizer' ? 'border-violet-600 dark:border-violet-500 bg-violet-50 dark:bg-violet-955/30 text-violet-900 dark:text-violet-300' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-400'">
                    <input type="radio" name="role" value="organizer" x-model="role" class="sr-only" required>
                    <x-heroicon-o-building-storefront class="w-5 h-5" />
                    <span class="text-sm font-semibold">Penyelenggara</span>
                </label>
            </div>
        </div>

        <div>
            <label for="name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Lengkap <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-identification class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="John Doe">
            </div>
        </div>

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Surel <span class="text-red-500">*</span></label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-envelope class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="you@example.com">
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Kata Sandi <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-o-lock-closed class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                    </div>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow">
                </div>
            </div>
        </div>

        {{-- Organizer Fields --}}
        <div x-show="role === 'organizer'" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-5 rounded-2xl border border-violet-200 dark:border-violet-900/30 bg-violet-50/20 dark:bg-violet-955/10 p-5 mt-6 shadow-sm">
            <div class="pb-2 border-b border-violet-100 dark:border-violet-900/30">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-s-building-office-2 class="h-4 w-4 text-violet-500" />
                    Data Penyelenggara & Pembayaran
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-450 mt-1">Wajib diisi untuk verifikasi dan pencairan dana tiket.</p>
            </div>

            <div>
                <label for="organization_name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Penyelenggara <span class="text-red-500">*</span></label>
                <input id="organization_name" type="text" name="organization_name" value="{{ old('organization_name') }}" :required="role === 'organizer'"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="e.g. Bintang Kreasindo">
            </div>

            <div>
                <label for="phone" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Telepon<span class="text-red-500">*</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" :required="role === 'organizer'"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="0812xxxxxx">
            </div>

            <div>
                <label for="organization_address" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Organisasi <span class="text-red-500">*</span></label>
                <textarea id="organization_address" name="organization_address" :required="role === 'organizer'" rows="3"
                          class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="e.g. Jl. Jend. Sudirman No. 123, Jakarta">{{ old('organization_address') }}</textarea>
            </div>

            <div>
                <label for="official_contact" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Kontak Resmi (Email/Telepon Tambahan) <span class="text-red-500">*</span></label>
                <input id="official_contact" type="text" name="official_contact" value="{{ old('official_contact') }}" :required="role === 'organizer'"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="e.g. info@bintangkreasindo.com">
            </div>

            <div>
                <label for="legality_document" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Dokumen Legalitas (PDF/JPG/PNG, Max 5MB) <span class="text-red-500">*</span></label>
                <input id="legality_document" type="file" name="legality_document" :required="role === 'organizer'"
                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-violet-50/10 file:text-violet-700 dark:file:bg-violet-500/10 dark:file:text-violet-400 hover:file:bg-violet-100 dark:hover:file:bg-violet-500/20 bg-transparent border border-slate-300 dark:border-slate-800 rounded-xl py-2 px-4 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2 sm:col-span-1">
                    <label for="bank_name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Bank <span class="text-red-500">*</span></label>
                    <input id="bank_name" type="text" name="bank_name" value="{{ old('bank_name') }}" :required="role === 'organizer'"
                           class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="BCA / Mandiri / BNI">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="bank_account_number" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input id="bank_account_number" type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" :required="role === 'organizer'"
                           class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="e.g. 1234567890">
                </div>
            </div>

            <div>
                <label for="bank_account_name" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                <input id="bank_account_name" type="text" name="bank_account_name" value="{{ old('bank_account_name') }}" :required="role === 'organizer'"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 px-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow" placeholder="Sesuai dengan rekening bank">
            </div>
        </div>

        <button type="submit" class="group relative flex w-full justify-center mt-6 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-violet-755 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950 transition-all disabled:opacity-50">
            Buat Akun
            <x-heroicon-s-arrow-right class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-violet-605 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">Masuk</a>
    </p>
</x-guest-layout>
