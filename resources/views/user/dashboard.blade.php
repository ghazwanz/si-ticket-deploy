<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold text-foreground tracking-tight">
                {{ __('Dashboard') }}
            </h1>
            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors cursor-pointer">
                <x-heroicon-o-plus-circle class="h-4 w-4" />
                Quick Create
            </button>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="px-4 sm:px-6 lg:px-8 lg:space-y-12 space-y-6">
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-medium tracking-tight text-foreground">Welcome, {{ Auth::user()->name }}!</h2>
                    <p class="text-base text-muted-foreground">Here is a summary of your event activity today.</p>
                </div>

                {{-- KPI Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
                    {{-- Total Revenue --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-muted-foreground">Total Revenue</p>
                            <x-heroicon-o-currency-dollar class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-card-foreground">$45,231.89</p>
                            <p class="text-xs text-muted-foreground mt-1">+20.1% from last month</p>
                        </div>
                    </div>
    
                    {{-- Tickets Sold --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-muted-foreground">Tickets Sold</p>
                            <x-heroicon-o-users class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-card-foreground">+2,350</p>
                            <p class="text-xs text-muted-foreground mt-1">+180.1% from last month</p>
                        </div>
                    </div>
    
                    {{-- QR Check-ins --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-muted-foreground">QR Check-ins</p>
                            <x-heroicon-o-qr-code class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-card-foreground">+1,234</p>
                            <p class="text-xs text-muted-foreground mt-1">+19% from last month</p>
                        </div>
                    </div>
    
                    {{-- Active Now --}}
                    <div class="rounded-2xl border border-border/60 bg-card p-6 shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-muted-foreground">Active Now</p>
                            <x-heroicon-o-signal class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-card-foreground">+573</p>
                            <p class="text-xs text-muted-foreground mt-1">+201 since last hour</p>
                        </div>
                    </div>
                </div>

                {{-- Charts + Recent Sales Row --}}
                <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
    
                    {{-- Chart Area --}}
                    <div class="rounded-2xl border border-border/60 bg-card shadow-sm lg:col-span-4">
                        <div class="p-6 pb-2">
                            <h3 class="text-base font-semibold text-card-foreground">Check-in Flow</h3>
                            <p class="text-sm text-muted-foreground">Live event check-in activity</p>
                        </div>
                        <div class="p-6 pt-0">
                            {{--     Chart (area chart mock) --}}
                            <div class="relative h-[280px] w-full">
                                <svg viewBox="0 0 600 280" class="w-full h-full" preserveAspectRatio="none">
                                    {{-- Grid lines --}}
                                    <line x1="0" y1="56" x2="600" y2="56" stroke="hsl(220 13% 91%)" stroke-width="0.5" stroke-dasharray="4 4"/>
                                    <line x1="0" y1="112" x2="600" y2="112" stroke="hsl(220 13% 91%)" stroke-width="0.5" stroke-dasharray="4 4"/>
                                    <line x1="0" y1="168" x2="600" y2="168" stroke="hsl(220 13% 91%)" stroke-width="0.5" stroke-dasharray="4 4"/>
                                    <line x1="0" y1="224" x2="600" y2="224" stroke="hsl(220 13% 91%)" stroke-width="0.5" stroke-dasharray="4 4"/>
    
                                    {{-- Area fill --}}
                                    <defs>
                                        <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="hsl(262 83% 58%)" stop-opacity="0.2"/>
                                            <stop offset="100%" stop-color="hsl(262 83% 58%)" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 240 C50 220, 80 190, 100 200 S150 160, 200 140 S250 180, 300 120 S350 80, 400 100 S450 60, 500 40 S550 70, 600 50 L600 280 L0 280 Z" fill="url(#chartGradient)"/>
    
                                    {{-- Line --}}
                                    <path d="M0 240 C50 220, 80 190, 100 200 S150 160, 200 140 S250 180, 300 120 S350 80, 400 100 S450 60, 500 40 S550 70, 600 50" fill="none" stroke="hsl(262 83% 58%)" stroke-width="2"/>
    
                                    {{-- Second line (secondary data) --}}
                                    <defs>
                                        <linearGradient id="chartGradient2" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="hsl(220 9% 46%)" stop-opacity="0.1"/>
                                            <stop offset="100%" stop-color="hsl(220 9% 46%)" stop-opacity="0"/>
                                        </linearGradient>
                                    </defs>
                                    <path d="M0 260 C50 250, 80 230, 100 240 S150 210, 200 200 S250 220, 300 190 S350 160, 400 170 S450 140, 500 120 S550 150, 600 130 L600 280 L0 280 Z" fill="url(#chartGradient2)"/>
                                    <path d="M0 260 C50 250, 80 230, 100 240 S150 210, 200 200 S250 220, 300 190 S350 160, 400 170 S450 140, 500 120 S550 150, 600 130" fill="none" stroke="hsl(220 9% 46%)" stroke-width="2" stroke-dasharray="4 2"/>
                                </svg>
    
                                {{-- X-axis labels --}}
                                <div class="flex justify-between text-xs text-muted-foreground mt-2 px-1">
                                    <span>09:00</span>
                                    <span>10:00</span>
                                    <span>11:00</span>
                                    <span>12:00</span>
                                    <span>13:00</span>
                                    <span>14:00</span>
                                    <span>Now</span>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    {{-- Recent Sales --}}
                    <div class="rounded-2xl border border-border/60 bg-card shadow-sm lg:col-span-3">
                        <div class="p-6 pb-2">
                            <h3 class="text-base font-semibold text-card-foreground">Recent Sales</h3>
                            <p class="text-sm text-muted-foreground">You made 265 sales this month.</p>
                        </div>
                        <div class="p-6 pt-4 space-y-6">
                            {{-- Sale Item --}}
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-muted text-foreground text-sm font-semibold shrink-0">OL</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground leading-none">Olivia Martin</p>
                                    <p class="text-sm text-muted-foreground truncate">olivia.martin@email.com</p>
                                </div>
                                <span class="text-sm font-medium text-foreground whitespace-nowrap">+$1,999.00</span>
                            </div>
    
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-muted text-foreground text-sm font-semibold shrink-0">JL</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground leading-none">Jackson Lee</p>
                                    <p class="text-sm text-muted-foreground truncate">jackson.lee@email.com</p>
                                </div>
                                <span class="text-sm font-medium text-foreground whitespace-nowrap">+$39.00</span>
                            </div>
    
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-muted text-foreground text-sm font-semibold shrink-0">IN</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground leading-none">Isabella Nguyen</p>
                                    <p class="text-sm text-muted-foreground truncate">isabella.nguyen@email.com</p>
                                </div>
                                <span class="text-sm font-medium text-foreground whitespace-nowrap">+$299.00</span>
                            </div>
    
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-muted text-foreground text-sm font-semibold shrink-0">WK</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground leading-none">William Kim</p>
                                    <p class="text-sm text-muted-foreground truncate">will@email.com</p>
                                </div>
                                <span class="text-sm font-medium text-foreground whitespace-nowrap">+$99.00</span>
                            </div>
    
                            <div class="flex items-center gap-4">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-muted text-foreground text-sm font-semibold shrink-0">SD</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-foreground leading-none">Sofia Davis</p>
                                    <p class="text-sm text-muted-foreground truncate">sofia.davis@email.com</p>
                                </div>
                                <span class="text-sm font-medium text-foreground whitespace-nowrap">+$39.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="space-y-4">
                <div class="flex justify-between">
                    <h2 class="text-2xl font-medium tracking-tight text-foreground">Recent QR Scans</h2>
                    <a href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md border border-border bg-background text-foreground hover:bg-secondary transition-colors cursor-pointer">
                        View All
                    </a>
                </div>

                {{-- Recent QR Scans Table --}}
                <div class="rounded-2xl border border-border/60 bg-card shadow-sm">
                    <div class="p-6 pb-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-card-foreground">Recent QR Scans</h3>
                                <p class="text-sm text-muted-foreground">Latest check-in activity across all gates.</p>
                            </div>
                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-medium rounded-md border border-border bg-background text-foreground hover:bg-secondary transition-colors cursor-pointer">
                                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">Ticket ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">Attendee</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">Tier</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">Gate</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-muted-foreground">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-muted-foreground">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-mono font-medium text-foreground">#QR-98321</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-muted text-xs font-semibold text-foreground">JD</span>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">John Doe</p>
                                                <p class="text-xs text-muted-foreground">john@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-foreground">VIP</td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">Gate A</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Success
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground text-right font-mono">10:42:01</td>
                                </tr>
                                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-mono font-medium text-foreground">#QR-10294</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-muted text-xs font-semibold text-foreground">AS</span>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">Alice Smith</p>
                                                <p class="text-xs text-muted-foreground">alice@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-foreground">General</td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">Gate B</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Success
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground text-right font-mono">10:41:45</td>
                                </tr>
                                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-mono font-medium text-destructive">#QR-55821</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-destructive/10 text-xs font-semibold text-destructive">?</span>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">Unknown</p>
                                                <p class="text-xs text-muted-foreground">—</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">—</td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">Gate A</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full border border-red-200 bg-red-50 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                            Invalid
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground text-right font-mono">10:40:12</td>
                                </tr>
                                <tr class="border-b border-border hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-mono font-medium text-foreground">#QR-33219</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-muted text-xs font-semibold text-foreground">BJ</span>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">Bob Johnson</p>
                                                <p class="text-xs text-muted-foreground">bob@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-foreground">Early Bird</td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">Gate C</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Success
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground text-right font-mono">10:39:55</td>
                                </tr>
                                <tr class="hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-3 text-sm font-mono font-medium text-foreground">#QR-77142</td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-muted text-xs font-semibold text-foreground">EM</span>
                                            <div>
                                                <p class="text-sm font-medium text-foreground">Emma Wilson</p>
                                                <p class="text-xs text-muted-foreground">emma@example.com</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-foreground">VIP</td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground">Gate A</td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-full border border-amber-200 bg-amber-50 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Pending
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-muted-foreground text-right font-mono">10:38:30</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
