<x-guest-layout>
    <div class="text-center">
        <p class="mb-1 text-xs font-bold uppercase tracking-widest text-violet-650 dark:text-violet-400">JoinFest</p>
        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">Verifikasi Alamat Surel</h1>
        <p class="mt-2 text-sm text-slate-505 dark:text-slate-400">Terima kasih telah mendaftar. Silakan klik tautan verifikasi yang kami kirim ke surel Anda.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-6 rounded-lg border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-3 text-sm text-emerald-755 dark:text-emerald-400 flex items-center gap-2">
            <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500" />
            <span>Tautan verifikasi telah dikirim ke alamat surel Anda.</span>
        </div>
    @endif

    <div class="mt-8 space-y-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="group relative flex w-full justify-center rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-violet-755 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 dark:focus:ring-offset-slate-950 transition-all disabled:opacity-50">
                Kirim ulang tautan verifikasi alamat surel
                <x-heroicon-o-paper-airplane class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" />
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full justify-center rounded-xl border border-slate-300 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200 dark:focus:ring-slate-800 transition-all">
                Keluar
            </button>
        </form>
    </div>
</x-guest-layout>
