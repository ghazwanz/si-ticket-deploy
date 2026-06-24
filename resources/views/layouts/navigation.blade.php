{{-- Sidebar Navigation --}}
<aside
    data-reveal
    data-reveal-delay="0"
    x-cloak
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0',
        sidebarMini ? 'lg:w-20' : 'lg:w-64'
    ]"
    class="fixed inset-y-0 left-0 z-50 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 flex flex-col sidebar-transition md:translate-x-0 opacity-0 translate-y-6 scale-[0.98] blur-sm"
>
    {{-- Brand --}}
    <div class="flex items-center h-14 border-b border-slate-100 dark:border-slate-800 shrink-0 overflow-hidden transition-all duration-300" :class="sidebarMini ? 'justify-center px-0' : 'gap-3 px-6'">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 shrink-0">
            <img src="{{ asset('favicon.svg') }}" class="h-5 w-5 object-contain" alt="">
        </div>
        <span x-show="!sidebarMini" x-transition.opacity class="font-bold text-sm text-slate-900 dark:text-white tracking-tight">{{ config('app.name', 'JoinFest') }}</span>

        {{-- Mobile close button --}}
        <button @click="sidebarOpen = false" class="ml-auto md:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
            <x-heroicon-o-x-mark class="h-5 w-5" />
        </button>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 space-y-6 custom-scrollbar transition-all duration-300" :class="sidebarMini ? 'px-2' : 'px-4'">
        @if(auth()->user()->role->value === 'admin')
        {{-- Admin Section --}}
        <div>
            <p x-show="!sidebarMini" x-transition.opacity class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Admin Panel</p>
            <div class="space-y-1">
                <a href="{{ route('profile.index') }}" data-link
                   :title="sidebarMini ? 'Profil Akun' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('profile.index') || request()->routeIs('profile.edit') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-user class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Profil Akun</span>
                </a>
                <a href="{{ route('dashboard') }}" data-link
                   :title="sidebarMini ? 'Dashboard' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('admin.users.index') }}" data-link
                   :title="sidebarMini ? 'Kelola Pengguna' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-users class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Kelola Pengguna</span>
                </a>
                <a href="{{ route('admin.events.index') }}" data-link
                   :title="sidebarMini ? 'Validasi Event' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.events.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-calendar-days class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Validasi Event</span>
                </a>
            </div>
        </div>
        @elseif(auth()->user()->role->value === 'organizer')
        {{-- Organizer Section --}}
        <div>
            <p x-show="!sidebarMini" x-transition.opacity class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">EO Panel</p>
            <div class="space-y-1">
                <a href="{{ route('organizer.settings') }}" data-link
                   :title="sidebarMini ? 'Profil Akun' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('profile.index') || request()->routeIs('organizer.settings') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-user class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Profil Akun</span>
                </a>
                <a href="{{ route('dashboard') }}" data-link
                   :title="sidebarMini ? 'Dashboard' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('dashboard') || request()->routeIs('organizer.dashboard') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Dashboard</span>
                </a>
                <a href="{{ route('organizer.events.index') }}" data-link
                   :title="sidebarMini ? 'Manajemen Event & Tiket' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('organizer.events.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-calendar class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Manajemen Event & Tiket</span>
                </a>
                <a href="{{ route('organizer.scanner.index') }}" data-link
                   :title="sidebarMini ? 'QR-Scanner' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('organizer.scanner.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-qr-code class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">QR-Scanner</span>
                </a>
            </div>
        </div>
        @else
        {{-- User Section --}}
        <div>
            <p x-show="!sidebarMini" x-transition.opacity class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">My Profile</p>
            <div class="space-y-1">
                <a href="{{ url('/') }}" data-link
                   :title="sidebarMini ? 'Beranda' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->is('/') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-home class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Beranda</span>
                </a>
                <a href="{{ route('profile.index') }}" data-link
                   :title="sidebarMini ? 'Informasi Saya' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('profile.index') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-user class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Informasi Saya</span>
                </a>
                <a href="{{ route('pesanan.index') }}" data-link
                   :title="sidebarMini ? 'Pesanan Saya' : ''"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('pesanan.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}">
                    <x-heroicon-o-shopping-bag class="h-5 w-5 shrink-0 text-slate-400 group-hover:text-slate-655 dark:group-hover:text-slate-300" />
                    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Pesanan Saya</span>
                </a>
            </div>
        </div>
        @endif
    </nav>

    {{-- User section --}}
    <div class="border-t border-slate-100 dark:border-slate-800 py-4 shrink-0 transition-all duration-300" :class="sidebarMini ? 'px-2' : 'px-4'">
        <x-dropdown align="top-start" width="48">
            <x-slot name="trigger">
                <button class="w-full flex items-center gap-3 p-1.5 text-sm rounded-xl hover:bg-slate-50 dark:hover:bg-white/5 transition-colors cursor-pointer" :class="sidebarMini ? 'justify-center px-0' : ''">
                    @if(Auth::user()->profile_photo_path)
                        <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 shadow-md shadow-violet-600/10">
                    @else
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-violet-600 dark:bg-violet-500 text-white text-xs font-semibold shrink-0 shadow-md shadow-violet-600/10">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    @endif
                    <div x-show="!sidebarMini" x-transition.opacity class="flex-1 min-w-0 text-left">
                        <p class="text-xs font-bold text-slate-900 dark:text-white leading-none truncate">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ Auth::user()->email }}</p>
                    </div>
                    <x-heroicon-o-ellipsis-horizontal x-show="!sidebarMini" class="h-4 w-4 text-slate-400 shrink-0" />
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.index')" data-link>
                    {{ __('Profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
