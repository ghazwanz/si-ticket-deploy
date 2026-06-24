<x-guest-layout>
    <div class="text-center">
        <p class="mb-1 text-xs font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400">JoinFest</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Selamat Datang Kembali</h1>
        <p class="mt-2 text-sm text-slate-505 dark:text-slate-400">Silakan masuk untuk melanjutkan</p>
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
                <span>Ada beberapa masalah dengan data Anda</span>
            </div>
            <ul class="list-disc list-inside pl-7 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Alamat Surel</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-envelope class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow">
            </div>
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-300">Kata Sandi</label>
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <x-heroicon-o-lock-closed class="h-5 w-5 text-slate-400 dark:text-slate-550" />
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="block w-full rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 py-2.5 pl-10 pr-4 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-600 focus:border-violet-500 dark:focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5 transition-shadow">
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 text-sm">
            <label class="flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 text-violet-650 dark:text-violet-450 focus:ring-violet-500 dark:focus:ring-offset-slate-900 transition-colors">
                <span class="text-slate-600 dark:text-slate-400">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="font-medium text-violet-605 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">Lupa Sandi?</a>
            @endif
        </div>

        <button type="submit" class="group relative flex w-full justify-center rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-violet-750 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950 transition-all disabled:opacity-50">
            Masuk
            <x-heroicon-s-arrow-right class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
        </button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-violet-605 dark:text-violet-400 hover:text-violet-700 dark:hover:text-violet-300 transition-colors">Daftar</a>
        </p>
    @endif
</x-guest-layout>

