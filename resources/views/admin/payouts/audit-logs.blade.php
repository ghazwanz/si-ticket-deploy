<x-admin-layout>
    <x-slot name="title">Log Transaksi Pemesanan - Admin JoinFest</x-slot>
    <x-slot name="header">LOG TRANSAKSI PEMESANAN </x-slot>

    @php
        $toggleOrderDir = $orderDir === 'asc' ? 'desc' : 'asc';
    @endphp

    <div class="space-y-6">
        {{-- Page Header --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Log Transaksi Checkout</h2>
                <p class="text-slate-500 dark:text-slate-400 mt-1 text-sm font-medium">Tinjau dan sinkronkan transaksi tiket dan merchandise yang dilakukan pembeli.</p>
            </div>
        </div>

        {{-- Sub-navigation Tabs --}}
        <div class="flex border-b border-slate-200 dark:border-slate-800 gap-6">
            <a href="{{ route('admin.payouts.index') }}" data-link
               class="pb-4 text-sm font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-900 dark:hover:text-white">
                Daftar Pencairan
            </a>
            <a href="{{ route('admin.payouts.audit-logs') }}" data-link
               class="pb-4 text-sm font-bold border-b-2 border-violet-600 text-violet-600 dark:text-violet-400">
                Log Transaksi Checkout
            </a>
        </div>

        {{-- Filters & Search --}}
        <div class="flex flex-col gap-4 bg-white/50 dark:bg-slate-900/50 p-4 rounded-3xl border border-slate-200 dark:border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide">
                    <a href="{{ route('admin.payouts.audit-logs', request()->except('page','status')) }}" data-link
                       class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ !$status ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                        Semua Status
                    </a>
                    @foreach($statuses as $st)
                        <a href="{{ route('admin.payouts.audit-logs', array_merge(request()->except('page'), ['status' => $st->value])) }}" data-link
                           class="px-5 py-2.5 rounded-2xl text-xs font-bold transition-all whitespace-nowrap {{ $status === $st->value ? 'bg-violet-600 text-white shadow-lg shadow-violet-600/20' : 'glass-panel text-slate-500 hover:text-slate-800 dark:hover:text-white' }}">
                            {{ $st->label() }}
                        </a>
                    @endforeach
                </div>

                <div class="relative w-full lg:w-72" x-data="{ 
                    search: '{{ $search }}',
                    updateSearch() {
                        const url = new URL(window.location.href);
                        url.searchParams.set('search', this.search);
                        url.searchParams.delete('page');
                        window.loadPage(url.toString(), true);
                    }
                }">
                    <div class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-slate-400">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4" />
                    </div>
                    <input type="text" 
                           id="audit-search"
                           x-model="search"
                           x-on:input.debounce.300ms="updateSearch()"
                           placeholder="Cari transaksi..." 
                           class="w-full bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 rounded-2xl pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all dark:text-white">
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="glass-panel border-emerald-500/30 bg-emerald-500/5 p-4 rounded-2xl flex items-center gap-3 text-emerald-600 dark:text-emerald-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-check class="w-5 h-5" />
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="glass-panel border-rose-500/30 bg-rose-500/5 p-4 rounded-2xl flex items-center gap-3 text-rose-600 dark:text-rose-400 text-sm font-bold animate-fade-in">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="glass-panel rounded-[2rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
                            <th class="px-8 py-5 text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase">
                                <a href="{{ route('admin.payouts.audit-logs', array_merge(request()->all(), ['sort' => 'created_at', 'order_dir' => $sort === 'created_at' ? $toggleOrderDir : 'desc'])) }}" data-link class="group inline-flex items-center gap-1 hover:text-slate-900 dark:hover:text-white">
                                    Waktu
                                    @if($sort === 'created_at')
                                        @if($orderDir === 'asc')
                                            <x-heroicon-s-chevron-up class="w-3 h-3" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-3 h-3" />
                                        @endif
                                    @else
                                        <x-heroicon-o-chevron-up-down class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                    @endif
                                </a>
                            </th>
                            <x-table-header label="ID Pesanan" />
                            <x-table-header label="Pembeli" />
                            <x-table-header label="Acara" />
                            <th class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-400 uppercase">
                                <a href="{{ route('admin.payouts.audit-logs', array_merge(request()->all(), ['sort' => 'total_amount', 'order_dir' => $sort === 'total_amount' ? $toggleOrderDir : 'desc'])) }}" data-link class="group inline-flex items-center gap-1 hover:text-slate-900 dark:hover:text-white">
                                    Total Harga
                                    @if($sort === 'total_amount')
                                        @if($orderDir === 'asc')
                                            <x-heroicon-s-chevron-up class="w-3 h-3" />
                                        @else
                                            <x-heroicon-s-chevron-down class="w-3 h-3" />
                                        @endif
                                    @else
                                        <x-heroicon-o-chevron-up-down class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity" />
                                    @endif
                                </a>
                            </th>
                            <x-table-header label="Status" />
                            <th class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-400 uppercase text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($orders as $order)
                        <tr class="group hover:bg-slate-50/80 dark:hover:bg-slate-900/50 transition-colors">
                            <td class="px-8 py-5 text-xs text-slate-500 dark:text-slate-400 font-medium whitespace-nowrap">
                                {{ $order->created_at->translatedFormat('d M Y, H:i:s') }}
                            </td>
                            <td class="px-8 py-5 text-xs font-mono font-bold text-slate-700 dark:text-slate-300">
                                {{ $order->midtrans_order_id }}
                            </td>
                            <td class="px-8 py-5">
                                <div class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ $order->user->name }}</div>
                                <div class="text-xs text-slate-400">{{ $order->user->email }}</div>
                            </td>
                            <td class="px-8 py-5 text-sm text-slate-650 dark:text-slate-350 font-semibold max-w-xs truncate">
                                {{ $order->event->name }}
                            </td>
                            <td class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-300">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-8 py-5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-[10px] font-bold uppercase tracking-wider {{ $order->status->color() }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-8 py-5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-3">
                                    <form action="{{ route('admin.orders.sync', $order->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                title="Sinkronkan status pesanan terkini dengan Midtrans"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-violet-500/20 bg-violet-500/5 text-violet-600 dark:text-violet-400 hover:bg-violet-500 hover:text-white text-[11px] font-bold transition-all">
                                            <x-heroicon-s-arrow-path class="w-3 h-3" />
                                            Sync
                                        </button>
                                    </form>

                                    @php
                                        // Build item details array for Alpine
                                        $ticketsList = [];
                                        foreach($order->tickets as $t) {
                                            $ticketsList[] = [
                                                'holder_name' => $t->holder_name,
                                                'category' => $t->ticketCategory->name,
                                                'price' => 'Rp ' . number_format($t->unit_price, 0, ',', '.'),
                                            ];
                                        }
                                        $merchList = [];
                                        foreach($order->merchandise as $m) {
                                            $merchList[] = [
                                                'name' => ($m->merchandiseItem->name ?? 'Merchandise') . ' (' . ($m->merchandiseVariant->name ?? 'Default') . ')',
                                                'quantity' => $m->quantity,
                                                'price' => 'Rp ' . number_format($m->unit_price, 0, ',', '.'),
                                            ];
                                        }
                                        $detailsPayload = json_encode([
                                            'tickets' => $ticketsList,
                                            'merchandise' => $merchList
                                        ]);
                                    @endphp
                                    <button x-data 
                                            @click="$dispatch('open-payload-modal', { payload: atob('{{ base64_encode($detailsPayload) }}'), orderId: '{{ $order->midtrans_order_id }}' })"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 text-[11px] font-bold transition-all">
                                        <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                        Rincian
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-8 py-12 text-center text-slate-500 dark:text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-3">
                                    <x-heroicon-o-document-magnifying-glass class="w-12 h-12 text-slate-300 dark:text-slate-700" />
                                    <p class="text-sm font-bold">Tidak ada transaksi ditemukan</p>
                                    <p class="text-xs text-slate-400">Coba ganti filter kata kunci atau filter status pencarian.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($orders->hasPages())
                <div class="px-8 py-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-900/10">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('modals')
    {{-- Inspect Items Modal --}}
    <div x-data="{ open: false, details: { tickets: [], merchandise: [] }, orderId: '' }"
         @open-payload-modal.window="open = true; details = JSON.parse($event.detail.payload); orderId = $event.detail.orderId"
         @keydown.escape.window="open = false"
         x-show="open"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6" x-cloak>
        
        <div x-show="open"
             x-transition.opacity
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
             @click="open = false"></div>
        
        <div x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="glass-panel w-full max-w-2xl rounded-[2rem] shadow-2xl relative overflow-hidden z-10 border border-slate-200 dark:border-slate-800">
             
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Rincian Transaksi</h2>
                        <p class="text-sm text-neutral-700 dark:text-slate-400 mt-1">ID Pesanan: <span class="font-mono text-violet-600 dark:text-violet-400" x-text="orderId"></span></p>
                    </div>
                    <button @click="open = false" class="p-2 text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 rounded-xl transition-colors">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-6 overflow-y-auto max-h-96 pr-2">
                    <!-- Tickets Section -->
                    <template x-if="details.tickets && details.tickets.length > 0">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Tiket (<span x-text="details.tickets.length"></span>)</h3>
                            <div class="space-y-2">
                                <template x-for="t in details.tickets">
                                    <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800/85">
                                        <div>
                                            <div class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="t.holder_name"></div>
                                            <div class="text-xs text-slate-400" x-text="t.category"></div>
                                        </div>
                                        <div class="text-sm font-mono font-bold text-slate-650 dark:text-slate-350" x-text="t.price"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Merchandise Section -->
                    <template x-if="details.merchandise && details.merchandise.length > 0">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-3">Suvenir (<span x-text="details.merchandise.length"></span>)</h3>
                            <div class="space-y-2">
                                <template x-for="m in details.merchandise">
                                    <div class="flex justify-between items-center bg-slate-50 dark:bg-slate-900/50 p-3 rounded-xl border border-slate-100 dark:border-slate-800/85">
                                        <div>
                                            <div class="text-sm font-bold text-slate-800 dark:text-slate-200" x-text="m.name"></div>
                                            <div class="text-xs text-slate-400">Jumlah: <span x-text="m.quantity"></span></div>
                                        </div>
                                        <div class="text-sm font-mono font-bold text-slate-655 dark:text-slate-345" x-text="m.price"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    
                    <template x-if="(!details.tickets || details.tickets.length === 0) && (!details.merchandise || details.merchandise.length === 0)">
                        <div class="text-center py-6 text-slate-500">Tidak ada item di pesanan ini.</div>
                    </template>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-slate-100 dark:border-slate-800 mt-6">
                    <button type="button" @click="open = false" class="px-6 py-2.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl text-sm font-bold uppercase tracking-widest hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endpush
</x-admin-layout>
