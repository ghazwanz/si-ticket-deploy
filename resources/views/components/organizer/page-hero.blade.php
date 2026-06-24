@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'icon' => 'sparkles',
])

@php
    $iconComponent = 'heroicon-o-'.$icon;
@endphp

<section {{ $attributes->merge(['class' => 'relative overflow-hidden rounded-[2rem] bg-slate-950 p-8 text-white shadow-2xl shadow-violet-950/20']) }}>
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(124,58,237,0.35),transparent_35%),radial-gradient(circle_at_bottom_left,rgba(16,185,129,0.18),transparent_30%)]"></div>
    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="max-w-2xl">
            @if($eyebrow)
                <p class="text-[10px] font-bold uppercase tracking-widest text-violet-200">{{ $eyebrow }}</p>
            @endif
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight">{{ $title }}</h2>
            @if($description)
                <p class="mt-3 text-sm leading-6 text-slate-300">{{ $description }}</p>
            @endif
        </div>
        <div class="hidden h-20 w-20 items-center justify-center rounded-[2rem] bg-white/10 ring-1 ring-white/15 sm:flex">
            <x-dynamic-component :component="$iconComponent" class="h-10 w-10 text-violet-200" />
        </div>
    </div>
</section>
