<section>
    <header>
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
            {{ __('Informasi Profil') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">
            {{ __('Perbarui informasi profil akun dan alamat email Anda.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-1">
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-xs font-bold text-slate-450 dark:text-slate-400 uppercase tracking-widest ml-1" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/20 text-slate-900 dark:text-white" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="space-y-1">
            <x-input-label for="email" :value="__('Alamat Email')" class="text-xs font-bold text-slate-455 dark:text-slate-400 uppercase tracking-widest ml-1" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/20 text-slate-900 dark:text-white" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-500/10 rounded-2xl border border-amber-200 dark:border-amber-550/20">
                    <p class="text-sm text-amber-800 dark:text-amber-200">
                        {{ __('Alamat email Anda belum diverifikasi.') }}

                        <button form="send-verification" class="underline font-bold text-amber-900 dark:text-amber-100 hover:text-amber-950 dark:hover:text-white focus:outline-none">
                            {{ __('Klik di sini untuk mengirim ulang email verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-bold text-xs text-emerald-600 dark:text-emerald-450">
                            {{ __('Tautan verifikasi baru telah dikirim ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="rounded-xl bg-violet-600 hover:bg-violet-700 px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-white border-none">{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-semibold text-emerald-600 dark:text-emerald-400"
                >{{ __('Berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
