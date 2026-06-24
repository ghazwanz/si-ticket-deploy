<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true',
          sidebarOpen: window.innerWidth > 768,
          sidebarMini: localStorage.getItem('sidebarMini_app') === 'true'
      }"
      x-init="
          $watch('darkMode', val => localStorage.setItem('darkMode', val));
          $watch('sidebarMini', val => localStorage.setItem('sidebarMini_app', val));
          if (window.innerWidth > 768 && window.innerWidth < 1024 && localStorage.getItem('sidebarMini_app') === null) {
              sidebarMini = true;
          }
      "
      :class="{ 'dark': darkMode }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
 
        <title>{{ config('app.name', 'JoinFest') }}</title>
 
        <!-- Scripts -->
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
    </head>
    <body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased selection:bg-violet-500/30">
        {{-- SPA Loader --}}
        <div id="spa-loader" class="fixed top-0 left-0 w-full h-1 z-[9999] hidden">
            <div class="h-full bg-violet-600 animate-progress shadow-[0_0_10px_rgba(124,58,237,0.5)]"></div>
        </div>
            <div class="min-h-screen bg-background">
            {{-- Mobile sidebar overlay --}}
            <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-black/50 md:hidden" @click="sidebarOpen = false" x-cloak></div>

            {{-- Sidebar --}}
            @include('layouts.navigation')

            {{-- Main content area --}}
            <div :class="[
                sidebarMini ? 'lg:pl-20' : 'lg:pl-64',
                sidebarOpen ? '' : 'pl-0'
            ]" class="sidebar-transition min-h-screen flex flex-col">
                {{-- Top header bar --}}
                <header data-site-header data-scrolled="false" class="sticky top-0 z-20 border-b border-slate-200 dark:border-slate-800/80 bg-white/95 dark:bg-slate-950/95 backdrop-blur transition-all duration-300 data-[scrolled=true]:border-violet-200 dark:data-[scrolled=true]:border-violet-900/50 data-[scrolled=true]:shadow-[0_12px_30px_rgba(15,23,42,0.08)]">
                    <div class="flex items-center justify-between h-14 px-4 sm:px-6 lg:px-8">
                        {{-- Hamburger toggle for both desktop (mini) and mobile --}}
                        <button @click="if (window.innerWidth > 1024) { sidebarMini = !sidebarMini } else { sidebarOpen = !sidebarOpen }" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <x-heroicon-o-bars-3 class="h-5 w-5" />
                        </button>
 
                        {{-- Page Heading --}}
                        <div class="flex-1 min-w-0" id="spa-header">
                            @isset($header)
                                {{ $header }}
                            @endisset
                        </div>
 
                        {{-- Dark/Light Mode Toggle & Profile --}}
                        <div class="flex items-center gap-3">
                            <button @click="darkMode = !darkMode" type="button"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-650 dark:text-slate-400 transition hover:border-violet-200 dark:hover:border-violet-800 hover:text-violet-600 dark:hover:text-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-500/30 cursor-pointer"
                                aria-label="Toggle Dark Mode">
                                <x-heroicon-o-moon x-show="!darkMode" class="w-4 h-4" />
                                <x-heroicon-o-sun x-show="darkMode" class="w-4 h-4" />
                            </button>

                            @auth
                            <div class="relative ml-2" x-data="{ open: false }" @click.away="open = false">
                                <button type="button" @click="open = !open" class="flex items-center gap-3 p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer border-none bg-transparent">
                                    @if(Auth::user()->profile_photo_path)
                                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-xl object-cover shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-xl bg-violet-500 flex items-center justify-center font-bold text-white text-xs">
                                            {{ substr(Auth::user()->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div class="hidden sm:block text-left">
                                        <div class="text-[11px] font-bold text-slate-900 dark:text-white leading-none">{{ Auth::user()->name }}</div>
                                        <div class="text-[10px] text-slate-400 font-medium mt-0.5 uppercase tracking-tighter">Pengguna</div>
                                    </div>
                                    <x-heroicon-m-chevron-down class="w-4 h-4 text-slate-400" />
                                </button>

                                <div x-show="open" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     class="absolute right-0 mt-3 w-52 glass-panel rounded-2xl shadow-xl py-2 z-50">
                                    <a href="{{ route('profile.index') }}" data-link class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-violet-500 hover:text-white transition-colors">
                                        <x-heroicon-o-user class="w-4 h-4" />
                                        Profil Saya
                                    </a>
                                    <a href="{{ route('pesanan.index') }}" data-link class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 dark:text-slate-300 hover:bg-violet-500 hover:text-white transition-colors">
                                        <x-heroicon-o-shopping-bag class="w-4 h-4" />
                                        Pesanan Saya
                                    </a>
                                    <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2 text-sm text-rose-500 hover:bg-rose-500 hover:text-white transition-colors cursor-pointer border-none bg-transparent">
                                            <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endauth
                        </div>
                    </div>
                </header>

                {{-- Page Content --}}
                <main data-page-shell class="page-fade-in">
                    <div data-reveal data-reveal-delay="80" class="transition-all duration-700 ease-out">
                        <!-- @yield('content') -->
                         {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
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
                } elseif (session('status') === 'verification-link-sent') {
                    $notification = 'Tautan verifikasi telah dikirim ke alamat email Anda.';
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
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
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
            </div>
        @endif
    </body>
</html>
