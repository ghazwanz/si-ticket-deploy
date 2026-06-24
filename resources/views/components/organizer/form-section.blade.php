@props([
    'icon' => 'sparkles',
    'title',
    'description' => null,
])

@php
    $iconComponent = 'heroicon-o-'.$icon;
@endphp

<section {{ $attributes->merge(['class' => 'glass-panel rounded-[2rem] border border-white/60 p-6 shadow-sm dark:border-white/10']) }}>
    <div class="mb-6 flex items-start gap-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-600/10 text-violet-600 dark:text-violet-400">
            <x-dynamic-component :component="$iconComponent" class="h-5 w-5" />
        </div>
        <div>
            <h3 class="font-extrabold text-lg tracking-tight text-slate-950 dark:text-white">{{ $title }}</h3>
            @if($description)
                <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>
    </div>

    {{ $slot }}
</section>
