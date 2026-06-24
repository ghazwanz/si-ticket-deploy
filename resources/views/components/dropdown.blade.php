@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-card border border-border'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    'top-start' => 'origin-bottom-left start-0 bottom-full mb-2',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$isTop = str_starts_with($align, 'top-');
$marginClass = $isTop ? '' : 'mt-2';

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-cloak x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute z-50 {{ $marginClass }} {{ $width }} rounded-md shadow-lg {{ $alignmentClasses }}"
            @click="open = false">
        <div class="rounded-md {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
