@props([
    'label',
    'value',
    'meta' => null,
    'icon' => 'chart-bar',
    'tone' => 'violet',
])

@php
    $iconComponent = 'heroicon-o-'.$icon;
    $tones = [
        'violet' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400',
        'emerald' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
        'amber' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
        'rose' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
        'sky' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'glass-panel rounded-2xl p-5 border border-white/60 dark:border-white/10 shadow-sm']) }}>
    <div class="flex items-center justify-between gap-4">
        <p class="text-sm font-bold uppercase tracking-widest text-slate-600 dark:text-slate-400">{{ $label }}</p>
        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl {{ $tones[$tone] ?? $tones['violet'] }}">
            <x-dynamic-component :component="$iconComponent" class="w-5 h-5" />
        </span>
    </div>
    <p class="mt-3 text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">{{ $value }}</p>
    @if($meta)
        <p class="mt-1 text-sm font-medium text-slate-600 dark:text-slate-400">{{ $meta }}</p>
    @endif
</div>
