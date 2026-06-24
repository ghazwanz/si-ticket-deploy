<aside x-cloak
       :class="[
           sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
           sidebarMini ? 'lg:w-20' : 'lg:w-64'
       ]"
       class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-950 border-r border-slate-200 dark:border-slate-800 sidebar-transition lg:translate-x-0 flex flex-col">
    <div class="h-16 flex items-center border-b border-slate-100 dark:border-slate-800 overflow-hidden transition-all duration-300" :class="sidebarMini ? 'justify-center px-0' : 'gap-3 px-6'">
        <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-violet-600/10 dark:bg-violet-500/10 shrink-0">
            <img src="{{ asset('favicon.svg') }}" class="h-5 w-5 object-contain" alt="">
        </div>
        <span x-show="!sidebarMini" x-transition.opacity class="font-bold tracking-tight text-slate-900 dark:text-white whitespace-nowrap">
            JoinFest <span class="text-violet-600 dark:text-violet-400">Organizer</span>
        </span>
        <button type="button" @click="sidebarOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-slate-900 dark:hover:text-white">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <nav class="flex-1 p-4 space-y-1 overflow-y-auto overflow-x-hidden custom-scrollbar">
        <div x-show="!sidebarMini" class="px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Konsol Penyelenggara</div>

        <x-organizer.nav-link href="{{ route('organizer.dashboard') }}" icon="squares-2x2" :active="request()->routeIs('organizer.dashboard')" :title="request()->routeIs('organizer.dashboard') ? 'Dasbor' : 'Dasbor'">
            Panel Kontrol
        </x-organizer.nav-link>

        @if(auth()->user()->isApprovedOrganizer())
        <x-organizer.nav-link href="{{ route('organizer.events.index') }}" icon="calendar-days" :active="request()->routeIs('organizer.events.*')" title="Acara">
            Acara
        </x-organizer.nav-link>

        <x-organizer.nav-link href="{{ route('organizer.payouts.index') }}" icon="banknotes" :active="request()->routeIs('organizer.payouts.*')" title="Pencairan Dana">
            Pencairan Dana
        </x-organizer.nav-link>

        <x-organizer.nav-link href="{{ route('organizer.scanner.index') }}" icon="qr-code" :active="request()->routeIs('organizer.scanner.*')" title="Pemindai QR">
            Pemindai QR
        </x-organizer.nav-link>
        @endif

        <div x-show="!sidebarMini" class="pt-4 px-3 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest whitespace-nowrap">Akun</div>

        <x-organizer.nav-link href="{{ route('organizer.settings') }}" icon="cog-6-tooth" :active="request()->routeIs('organizer.settings')" title="Pengaturan">
            Pengaturan
        </x-organizer.nav-link>
    </nav>

    <div class="mt-auto p-4 border-t border-slate-100 dark:border-slate-800 overflow-hidden">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold text-rose-500 hover:text-rose-600 hover:bg-rose-500/10 transition-all" :title="sidebarMini ? 'Keluar' : ''">
                <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5 shrink-0" />
                <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">Keluar</span>
            </button>
        </form>
    </div>
</aside>
