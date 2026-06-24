@props([
    'width' => 'max-w-2xl',
    'center' => true,
    'card' => true,
    'backUrl' => null,
    'backText' => 'Kembali ke Beranda'
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' - ' . config('app.name', 'JoinFest') : config('app.name', 'JoinFest') }}</title>

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            [x-cloak] { display: none !important; }
            .page-fade-in {
                animation: fadeIn 0.4s ease-out;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(8px); }
                to { opacity: 1; transform: translateY(0); }
            }
        </style>
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-300 relative {{ $center ? 'justify-center items-center' : '' }}">
        {{-- Background Ambient Glows --}}
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] rounded-full bg-violet-600/5 dark:bg-violet-600/10 blur-3xl"></div>
            <div class="absolute top-[40%] -left-20 w-[400px] h-[400px] rounded-full bg-emerald-600/5 dark:bg-emerald-600/5 blur-3xl"></div>
        </div>

        {{-- SPA Loader --}}
        <div id="spa-loader" class="fixed top-0 left-0 w-full h-1 z-[9999] hidden">
            <div class="h-full bg-violet-600 animate-progress shadow-[0_0_10px_rgba(124,58,237,0.5)]"></div>
        </div>

        {{-- Dark Mode Toggle --}}
        <div class="absolute top-6 right-6">
            <button @click="darkMode = !darkMode" type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 transition hover:border-violet-200 dark:hover:border-violet-800 hover:text-violet-600 dark:hover:text-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                aria-label="Toggle Dark Mode">
                <x-heroicon-o-moon x-show="!darkMode" class="w-5 h-5" />
                <x-heroicon-o-sun x-show="darkMode" class="w-5 h-5" />
            </button>
        </div>

        {{-- Back to Home Link --}}
        <div class="absolute top-6 left-6 z-10">
            <a href="{{ $backUrl ?? url('/') }}" data-link class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition-colors">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
                {{ $backText }}
            </a>
        </div>

        {{-- Main Page Content --}}
        <main data-page-shell class="w-full {{ $width }} px-4 py-12 sm:px-6 lg:px-8 page-fade-in {{ $center ? '' : 'mx-auto mt-16' }}">
            @if($card)
                <section class="w-full rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl p-6 sm:p-12 shadow-xl transition-colors duration-300 space-y-6">
                    {{ $slot }}
                </section>
            @else
                <div class="space-y-6">
                    {{ $slot }}
                </div>
            @endif
        </main>

        <div id="spa-modals">
            @stack('modals')
        </div>
        @stack('scripts')
    </body>
</html>
