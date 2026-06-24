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
    <body class="font-sans antialiased bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen flex flex-col transition-colors duration-300">
        {{-- Background Ambient Glows --}}
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-40 right-0 w-[500px] h-[500px] rounded-full bg-violet-600/5 dark:bg-violet-600/10 blur-3xl"></div>
            <div class="absolute top-[40%] -left-20 w-[400px] h-[400px] rounded-full bg-emerald-600/5 dark:bg-emerald-600/5 blur-3xl"></div>
        </div>

        {{-- SPA Loader --}} 
        <div id="spa-loader" class="fixed top-0 left-0 w-full h-1 z-[9999] hidden">
            <div class="h-full bg-violet-600 animate-progress shadow-[0_0_10px_rgba(124,58,237,0.5)]"></div>
        </div>

        {{-- Header --}}
        <x-public.header />

        {{-- Main Page Content --}}
        <main data-page-shell class="flex-1 page-fade-in">
            <div data-reveal data-reveal-delay="80" class="transition-all duration-700 ease-out">
                {{ $slot }}
            </div>
        </main>

        {{-- Footer --}}
        <x-public.footer />

        {{-- Modals and Scripts --}}
        <div id="spa-modals">
            @stack('modals')
        </div>

        {{-- Global Notification Toast --}}
        @php
            $notification = null;
            $type = 'success';
            
            if (session('success')) {
                $notification = session('success');
                $type = 'success';
            } elseif (session('status')) {
                if (session('status') === 'profile-updated') {
                    $notification = 'Informasi profil berhasil diperbarui.';
                } elseif (session('status') === 'password-updated') {
                    $notification = 'Kata sandi berhasil diubah.';
                } else {
                    $notification = session('status');
                }
                $type = 'success';
            } elseif (session('error')) {
                $notification = session('error');
                $type = 'error';
            }
        @endphp

        @if ($notification)
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                 class="fixed bottom-6 right-6 z-[110] glass-panel {{ $type === 'success' ? 'bg-emerald-500/10 border-emerald-500/20' : 'bg-rose-500/10 border-rose-500/20' }} px-6 py-4 rounded-2xl shadow-lg flex items-center gap-3">
                @if ($type === 'success')
                    <x-heroicon-s-check-circle class="w-6 h-6 text-emerald-500" />
                @else
                    <x-heroicon-s-x-circle class="w-6 h-6 text-rose-500" />
                @endif
                <div>
                    <h4 class="text-sm font-bold {{ $type === 'success' ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                        {{ $type === 'success' ? 'Berhasil' : 'Gagal' }}
                    </h4>
                    <p class="text-xs font-medium {{ $type === 'success' ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }}">
                        {{ $notification }}
                    </p>
                </div>
                <button type="button" @click="show = false" class="ml-4 text-slate-400 hover:text-slate-650 dark:hover:text-slate-200">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif

        @stack('scripts')
    </body>
</html>
