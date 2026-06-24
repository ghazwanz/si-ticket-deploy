<header x-data="{ open: false }" data-site-header data-scrolled="false"
    class="sticky top-0 z-30 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/85 dark:bg-slate-950/85 backdrop-blur transition-all duration-300 data-[scrolled=true]:border-violet-200 data-[scrolled=true]:dark:border-violet-900/50 data-[scrolled=true]:bg-white/95 data-[scrolled=true]:dark:bg-slate-950/95 data-[scrolled=true]:shadow-[0_12px_30px_rgba(15,23,42,0.08)] data-[scrolled=true]:dark:shadow-[0_12px_30px_rgba(0,0,0,0.5)]">
    <div class="w-full items-center justify-between gap-3 px-4 py-4 md:px-6">
        <div class="max-w-7xl flex w-full items-center justify-between gap-6 mx-auto">

            <a href="{{ url('/') }}" data-link
                class="inline-flex items-center gap-3 text-base font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-lg">
                <img src="{{ asset('favicon.svg') }}" alt="JoinFest logo" class="h-9 w-9 object-contain">
                <span>{{ config('app.name') }}</span>
            </a>

            <div class="hidden min-w-0 flex-1 items-center justify-center px-4 lg:flex">
                <form action="{{ route('events.index') }}" method="GET" role="search" class="relative w-full max-w-md">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400 dark:text-slate-500" />
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari acara, atau penyelenggara..."
                        aria-label="Cari acara, atau penyelenggara"
                        class="h-11 w-full rounded-full border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 py-2 pl-9 pr-4 text-sm text-slate-700 dark:text-slate-200 outline-none transition focus:border-violet-500 dark:focus:border-violet-500 focus:bg-white dark:focus:bg-slate-955 focus:ring-4 focus:ring-violet-500/10 dark:focus:ring-violet-500/5"
                    >
                    <button type="submit" class="sr-only">Cari</button>
                </form>
            </div>

            <div class="flex items-center gap-3">
                {{-- Dark Mode Toggle --}}
                <button @click="darkMode = !darkMode" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 transition hover:border-violet-200 dark:hover:border-violet-800 hover:text-violet-600 dark:hover:text-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                    aria-label="Toggle Dark Mode">
                    <x-heroicon-o-moon x-show="!darkMode" class="w-5 h-5" />
                    <x-heroicon-o-sun x-show="darkMode" class="w-5 h-5" />
                </button>

                {{-- Desktop Navigation --}}
                <nav class="hidden items-center gap-6 text-sm font-semibold md:flex">
                    <x-nav-link href="{{ url('/') }}" data-link :active="request()->is('/')">
                        Beranda
                    </x-nav-link>
                    <x-nav-link :href="route('events.index')" data-link :active="request()->routeIs('events.index') || request()->routeIs('events.show')">
                        Jelajahi Acara
                    </x-nav-link>

                    @auth
                        <div class="relative" x-data="{ profileOpen: false }">
                            <button @click="profileOpen = !profileOpen" type="button"
                                class="flex items-center gap-2 focus:outline-none transition hover:opacity-90 cursor-pointer"
                                id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                @if(Auth::user()->profile_photo_path)
                                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 shadow-md shadow-violet-600/10 dark:shadow-none">
                                @else
                                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-violet-600 dark:bg-violet-500 text-white text-sm font-semibold shrink-0 shadow-md shadow-violet-600/10 dark:shadow-none">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                                    </span>
                                @endif
                                <x-heroicon-o-chevron-down class="w-4 h-4 text-slate-500 dark:text-slate-400 transition-transform duration-200" ::class="profileOpen ? 'rotate-180' : ''" />
                            </button>

                            <div x-show="profileOpen"
                                @click.away="profileOpen = false"
                                x-cloak
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-2.5 w-48 origin-top-right rounded-2xl glass-panel bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/85 py-1.5 shadow-lg dark:shadow-[0_12px_30px_rgba(0,0,0,0.5)] focus:outline-none z-50">
                                
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800/60 mb-1">
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest leading-none">Akun</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white truncate mt-1.5 leading-none">{{ Auth::user()->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-1 leading-none">{{ Auth::user()->email }}</p>
                                </div>

                                <div class="py-1">
                                    @if(Auth::user()->role->value === 'admin' || Auth::user()->role->value === 'organizer')
                                        <a href="{{ route('dashboard') }}" data-link
                                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-violet-500 hover:text-white transition-colors">
                                            <x-heroicon-o-squares-2x2 class="w-4 h-4 shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-white" />
                                            Dashboard
                                        </a>
                                    @else
                                        <a href="{{ route('profile.index') }}" data-link
                                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-violet-500 hover:text-white transition-colors">
                                            <x-heroicon-o-user class="w-4 h-4 shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-white" />
                                            Profil Saya
                                        </a>
                                        <a href="{{ route('pesanan.index') }}" data-link
                                            class="flex items-center gap-3 px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-violet-500 hover:text-white transition-colors">
                                            <x-heroicon-o-shopping-bag class="w-4 h-4 shrink-0 text-slate-400 dark:text-slate-500 group-hover:text-white" />
                                            Pesanan Saya
                                        </a>
                                    @endif
                                </div>

                                <div class="border-t border-slate-100 dark:border-slate-800/60 my-1"></div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-4 py-2 text-sm text-rose-500 hover:bg-rose-500 hover:text-white transition-colors text-left font-semibold cursor-pointer">
                                        <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4 shrink-0 text-rose-500 group-hover:text-white" />
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <x-nav-link :href="route('login')" data-link :active="request()->routeIs('login')">
                            Masuk
                        </x-nav-link>
                        <div class="flex items-center ml-2">
                            <a href="{{ route('register') }}" data-link
                                class="inline-flex h-9 items-center justify-center rounded-full bg-violet-600 px-4 text-white transition hover:-translate-y-0.5 hover:bg-violet-700 shadow-md shadow-violet-600/10">Daftar</a>
                        </div>
                    @endauth
                </nav>

                {{-- Mobile Menu Button --}}
                <button @click="open = !open" type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 transition hover:border-violet-200 dark:hover:border-violet-800 hover:text-violet-600 dark:hover:text-violet-400 focus:outline-none focus:ring-2 focus:ring-violet-500/30 md:hidden"
                    aria-controls="mobile-menu" :aria-expanded="open.toString()">
                    <x-heroicon-o-bars-3 x-show="!open" class="h-5 w-5" />
                    <x-heroicon-o-x-mark x-show="open" x-cloak class="h-5 w-5" />
                </button>
            </div>
        </div>

        {{-- Mobile Navigation Dropdown --}}
        <div id="mobile-menu" x-show="open" x-transition
            class="border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pb-4 pt-3 md:hidden rounded-2xl mt-2 shadow-lg">
            <div class="grid gap-2">
                <x-responsive-nav-link href="{{ url('/') }}" data-link :active="request()->is('/')">
                    Beranda
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('events.index')" data-link :active="request()->routeIs('events.index')">
                    Jelajahi Acara
                </x-responsive-nav-link>
                @auth
                    <div class="border-t border-slate-200 dark:border-slate-800 my-2"></div>
                    <div class="flex items-center gap-3 px-3 py-2">
                        @if(Auth::user()->profile_photo_path)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-9 h-9 rounded-full object-cover shrink-0 shadow-md shadow-violet-600/10">
                        @else
                            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-violet-600 dark:bg-violet-500 text-white text-sm font-semibold shrink-0 shadow-md shadow-violet-600/10">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <div class="grid gap-1 mt-1">
                        @if(Auth::user()->role->value === 'admin' || Auth::user()->role->value === 'organizer')
                            <x-responsive-nav-link :href="route('dashboard')" data-link :active="request()->is('dashboard*') || request()->is('admin*') || request()->is('organizer*')">
                                Dashboard
                            </x-responsive-nav-link>
                        @else
                            <x-responsive-nav-link :href="route('profile.index')" data-link :active="request()->is('profile*')">
                                Profil Saya
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('pesanan.index')" data-link :active="request()->is('pesanan*')">
                                Pesanan Saya
                            </x-responsive-nav-link>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-semibold text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-200 hover:bg-rose-50 dark:hover:bg-rose-955/20 transition duration-150 ease-in-out cursor-pointer">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <x-responsive-nav-link :href="route('login')" data-link :active="request()->routeIs('login')">
                        Masuk
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" data-link :active="request()->routeIs('register')">
                        Daftar
                    </x-responsive-nav-link>
                @endauth
            </div>
        </div>
    </div>
</header>
