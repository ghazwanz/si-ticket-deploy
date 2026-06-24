<aside 
    x-cloak
    :class="[
        sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        sidebarMini ? 'lg:w-20' : 'lg:w-64'
    ]"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 sidebar-transition lg:translate-x-0 flex flex-col">
    
    {{-- Sidebar Header --}}
    <div class="h-16 flex items-center border-b border-slate-100 dark:border-slate-800 overflow-hidden transition-all duration-300" :class="sidebarMini ? 'justify-center px-0' : 'gap-3 px-6'">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 shrink-0">
            <img src="{{ asset('favicon.svg') }}" class="h-5 w-5 object-contain" alt="">
        </div>
        <span x-show="!sidebarMini" x-transition.opacity class="font-bold tracking-tight text-slate-900 dark:text-white whitespace-nowrap">JoinFest <span class="text-violet-600 dark:text-violet-400">Admin</span></span>
        <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    {{-- Nav Links --}}
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto overflow-x-hidden custom-scrollbar">
        <div x-show="!sidebarMini" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Konsol Utama</div>
        
        <a href="{{ route('admin.dashboard') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Panel Kontrol' : ''">
            <x-heroicon-o-squares-2x2 class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Panel Kontrol</span>
        </a>

        <a href="{{ route('admin.users.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Manajemen Pengguna' : ''">
            <x-heroicon-o-users class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Pengguna</span>
        </a>

        <a href="{{ route('admin.events.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.events.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Persetujuan Acara' : ''">
            <x-heroicon-o-calendar-days class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Acara</span>
        </a>

        <a href="{{ route('admin.cancellations.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.cancellations.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Pembatalan Acara' : ''">
            <x-heroicon-o-document-minus class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Pembatalan</span>
        </a>

        <a href="{{ route('admin.payouts.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.payouts.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Pencairan Dana' : ''">
            <x-heroicon-o-banknotes class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Pencairan Dana</span>
        </a>

        <a href="{{ route('admin.event-categories.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.event-categories.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Kategori' : ''">
            <x-heroicon-o-tag class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Kategori</span>
        </a>

        <div x-show="!sidebarMini" class="pt-4 px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Konfigurasi</div>

        <a href="{{ route('admin.settings.index') }}" data-link
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group {{ request()->routeIs('admin.settings.*') ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5' }}"
           :title="sidebarMini ? 'Pengaturan Sistem' : ''">
            <x-heroicon-o-cog-6-tooth class="w-5 h-5 shrink-0" />
            <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Pengaturan</span>
        </a>
    </nav>

    {{-- Sidebar Footer --}}
    <div class="mt-auto p-4 border-t border-slate-100 dark:border-slate-800 overflow-hidden">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-500/10 transition-all"
                    :title="sidebarMini ? 'Keluar' : ''">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 shrink-0" />
                <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Keluar</span>
            </button>
        </form>
    </div>
</aside>
