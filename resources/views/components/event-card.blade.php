@props(['event', 'loopIndex'])

@php
    use App\Enums\EventStatus;
    
    // Price Range Parsing
    $lowest = $event->lowest_price;
    $highest = $event->highest_price;
    $hasTickets = !is_null($lowest);
    
    $priceDisplay = '';
    if (!$hasTickets) {
        $priceDisplay = 'Gratis';
    } elseif ($lowest === $highest) {
        $priceDisplay = 'Rp ' . number_format($lowest, 0, ',', '.');
    } else {
        $priceDisplay = 'Rp ' . number_format($lowest, 0, ',', '.') . ' - Rp ' . number_format($highest, 0, ',', '.');
    }

    // Quota details
    $remaining = $event->remaining_quota;
    $isSoldOut = $remaining <= 0;
    $isNearlySoldOut = $event->is_nearly_sold_out && !$isSoldOut;

    // Single Status/Stock Badge on Top-Right
    $badgeText = '';
    $badgeClass = '';
    
    if ($event->status === EventStatus::AwaitingCancellation) {
        $badgeText = 'Ditangguhkan';
        $badgeClass = 'bg-amber-600/90 dark:bg-amber-500/90 text-white';
    } elseif ($event->status === EventStatus::Completed) {
        $badgeText = 'Selesai';
        $badgeClass = 'bg-slate-650/90 dark:bg-slate-600/90 text-white';
    } else {
        if ($isSoldOut) {
            $badgeText = 'Habis Terjual';
            $badgeClass = 'bg-rose-600/90 dark:bg-rose-500/90 text-white';
        } elseif ($isNearlySoldOut) {
            $badgeText = 'Hampir Habis';
            $badgeClass = 'bg-amber-500/90 dark:bg-amber-500/90 text-white';
        } else {
            $badgeText = 'Mendatang';
            $badgeClass = 'bg-violet-600/90 dark:bg-violet-500/90 text-white';
        }
    }
@endphp

<a href="{{ route('events.show', $event->slug) }}" data-link 
   class="group relative overflow-hidden rounded-[2rem] border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-900/40 backdrop-blur-xl transition-all duration-500 hover:-translate-y-1 hover:border-violet-500/30 hover:shadow-2xl hover:shadow-violet-900/10 opacity-0 blur-sm translate-y-6 scale-[0.98] flex flex-col h-full" 
   data-reveal data-reveal-delay="{{ $loopIndex * 90 + 130 }}">
    
    {{-- Banner Image Area --}}
    <div class="relative h-52 overflow-hidden shrink-0">
        <img src="{{ $event->image_path ? Storage::url($event->image_path) : Storage::url('img/eobanner.png') }}" 
             alt="{{ $event->name }}" 
             class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" 
             style="view-transition-name: event-img-{{ $event->id }};">
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent"></div>
        
        {{-- Badges Container --}}
        <div class="absolute inset-x-4 top-4 flex items-center justify-between gap-2">
            {{-- Category Badge (Top-Left) --}}
            <span class="inline-flex rounded-xl border border-white/10 bg-black/45 px-3 py-1 text-[9px] font-bold uppercase tracking-widest text-white backdrop-blur-md">
                {{ $event->category?->name ?? 'Event' }}
            </span>

            {{-- Combined Status / Stock Badge (Top-Right) --}}
            <span class="inline-flex rounded-xl px-2.5 py-1 text-[9px] font-bold uppercase tracking-widest backdrop-blur-md shadow-sm {{ $badgeClass }}">
                {{ $badgeText }}
            </span>
        </div>
    </div>

    {{-- Metadata Details Area --}}
    <div class="relative p-6 flex flex-col justify-between flex-1 min-h-[180px]">
        <div>
            <h3 class="lg:text-xl text-lg font-bold text-slate-900 dark:text-white line-clamp-2 group-hover:text-violet-600 dark:group-hover:text-violet-405 transition-colors">
                {{ $event->name }}
            </h3>
            
            <div class="mt-3.5 flex flex-col gap-2 text-sm font-semibold text-slate-600 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <x-heroicon-s-calendar class="h-5 w-5 text-violet-600 dark:text-violet-400 shrink-0" />
                    <span>{{ $event->event_date->translatedFormat('d M Y') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <x-heroicon-s-map-pin class="h-5 w-5 text-sky-600 dark:text-sky-400 shrink-0" />
                    <span class="line-clamp-1">{{ $event->venue_name }}, {{ $event->city }}</span>
                </div>
            </div>
        </div>

        {{-- Footer area of the card (Minimalist, single row) --}}
        <div class="mt-5 border-t border-slate-100 dark:border-white/5 pt-4 flex items-center justify-between gap-4 shrink-0">
            {{-- Price Display --}}
            <span class="text-sm font-extrabold {{ !$hasTickets ? 'text-emerald-500 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">
                {{ $priceDisplay }}
            </span>

            {{-- Ticket remaining indicator with tiny colored status dot --}}
            @if($isSoldOut)
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    <span class="h-2 w-2 rounded-full bg-rose-500 shrink-0"></span>
                    Tiket Habis
                </span>
            @elseif($isNearlySoldOut)
                <span class="inline-flex items-center gap-1.5 text-sm font-bold text-amber-500">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    {{ $remaining }} Tersisa!
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>
                    {{ $remaining }} Tersisa
                </span>
            @endif
        </div>
    </div>
</a>
