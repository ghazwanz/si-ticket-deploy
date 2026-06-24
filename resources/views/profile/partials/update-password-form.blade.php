<section>
    <header>
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
            {{ __('Ubah Kata Sandi') }}
        </h2>

        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 font-medium">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk keamanan.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-1">
            <x-input-label for="update_password_current_password" :value="__('Kata Sandi Saat Ini')" class="text-xs font-bold text-slate-450 dark:text-slate-400 uppercase tracking-widest ml-1" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/20 text-slate-900 dark:text-white" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="space-y-1">
            <x-input-label for="update_password_password" :value="__('Kata Sandi Baru')" class="text-xs font-bold text-slate-450 dark:text-slate-400 uppercase tracking-widest ml-1" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/20 text-slate-900 dark:text-white" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div class="space-y-1">
            <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Kata Sandi Baru')" class="text-xs font-bold text-slate-450 dark:text-slate-400 uppercase tracking-widest ml-1" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full rounded-2xl border-slate-200 dark:border-slate-800 bg-white/50 dark:bg-slate-950/20 text-slate-900 dark:text-white" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="rounded-xl bg-violet-600 hover:bg-violet-700 px-6 py-2.5 text-xs font-bold uppercase tracking-widest text-white border-none">{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'password-updated')
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
