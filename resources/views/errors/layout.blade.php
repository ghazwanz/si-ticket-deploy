<!DOCTYPE html>
<html lang="id"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') - {{ config('app.name', 'JoinFest') }}</title>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex items-center justify-center p-4 transition-colors duration-300 relative overflow-hidden">
        {{-- Background Ambient Glows --}}
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] rounded-full bg-violet-600/5 dark:bg-violet-600/10 blur-3xl"></div>
            <div class="absolute top-[40%] -left-20 w-[400px] h-[400px] rounded-full bg-emerald-600/5 dark:bg-emerald-600/5 blur-3xl"></div>
        </div>

        {{-- Theme Switcher (Floating top right) --}}
        <div class="absolute top-4 right-4 z-50">
            <button @click="darkMode = !darkMode" class="p-3 rounded-2xl border border-slate-200 dark:border-white/10 bg-white/80 dark:bg-slate-900/50 backdrop-blur-md text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition shadow-sm cursor-pointer" aria-label="Toggle theme">
                <template x-if="darkMode">
                    <x-heroicon-o-sun class="w-5 h-5" />
                </template>
                <template x-if="!darkMode">
                    <x-heroicon-o-moon class="w-5 h-5" />
                </template>
            </button>
        </div>

        {{-- Error Card Container --}}
        <div class="w-full max-w-xl text-center rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white/80 dark:bg-slate-900/40 backdrop-blur-xl p-8 md:p-12 shadow-xl relative overflow-hidden">
            <!-- Glow inside card -->
            <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-violet-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col items-center">
                <!-- Giant status code -->
                <div class="text-transparent bg-clip-text bg-gradient-to-r from-violet-600 via-violet-500 to-sky-500 dark:from-violet-400 dark:via-violet-450 dark:to-sky-400 text-7xl md:text-9xl font-black tracking-tighter select-none mb-4">
                    @yield('code')
                </div>

                <!-- Label -->
                <span class="inline-flex rounded-xl border border-violet-500/30 bg-violet-500/10 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-violet-600 dark:text-violet-400 mb-6">
                    @yield('title')
                </span>

                <!-- Message Description -->
                <p class="text-sm md:text-base leading-relaxed text-slate-600 dark:text-slate-400 max-w-md">
                    @yield('message')
                </p>

                <!-- Action Button -->
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ url('/') }}" data-link class="inline-flex h-12 items-center justify-center rounded-xl bg-violet-600 px-6 text-sm font-bold text-white shadow-lg shadow-violet-600/25 transition-all hover:-translate-y-0.5 hover:bg-violet-750 focus:outline-none focus:ring-4 focus:ring-violet-500/20">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
