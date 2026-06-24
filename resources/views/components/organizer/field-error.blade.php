@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-2 flex items-center gap-1.5 text-xs font-bold text-rose-500']) }}>
        <x-heroicon-o-exclamation-circle class="h-4 w-4" />
        <span>{{ $message }}</span>
    </p>
@enderror
