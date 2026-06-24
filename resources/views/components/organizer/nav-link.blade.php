@props([
    'active' => false,
    'icon' => 'squares-2x2',
])

@php
    $iconComponent = 'heroicon-o-'.$icon;
@endphp

<a {{ $attributes->merge([
        'data-link' => true,
        'class' => 'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all relative overflow-hidden group '.($active ? 'nav-active' : 'text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-white/5'),
    ]) }}>
    <x-dynamic-component :component="$iconComponent" class="w-5 h-5 shrink-0" />
    <span x-show="!sidebarMini" x-transition.opacity class="whitespace-nowrap">{{ $slot }}</span>
</a>