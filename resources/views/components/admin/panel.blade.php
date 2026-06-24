@props(['name', 'title' => '', 'description' => '', 'width' => '2xl'])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
    '3xl' => 'max-w-3xl',
    '4xl' => 'max-w-4xl',
    '5xl' => 'max-w-5xl',
    '6xl' => 'max-w-6xl',
    'full' => 'max-w-full',
][$width];
@endphp

<div
    x-data="{ 
        show: false,
        name: '{{ $name }}',
        close() { this.show = false }
    }"
    x-init="
        $watch('show', value => {
            if (value) document.body.classList.add('overflow-y-hidden');
            else document.body.classList.remove('overflow-y-hidden');
        })
    "
    x-on:open-panel.window="$event.detail == name ? show = true : null"
    x-on:close-panel.window="$event.detail == name ? show = false : null"
    x-on:close.stop="close()"
    x-on:keydown.escape.window="close()"
    x-show="show"
    class="fixed inset-0 z-[90] overflow-hidden"
    x-cloak
>
    {{-- Latar belakang panel --}}
    <div 
        x-show="show"
        x-transition:enter="ease-in-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-500"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
    ></div>

    {{-- Kontainer panel --}}
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
        <div 
            x-show="show"
            x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="w-screen {{ $maxWidthClass }} pointer-events-auto"
        >
            <div class="flex h-full flex-col bg-white dark:bg-slate-950 shadow-2xl border-l border-slate-200 dark:border-slate-800 overflow-hidden">
                {{-- Header --}}
                <div class="px-8 py-8 border-b border-slate-100 dark:border-slate-800 shrink-0">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>
                            @if($description)
                                <p class="mt-1 text-sm font-medium text-slate-500 dark:text-slate-400">{{ $description }}</p>
                            @endif
                        </div>
                        <button @click="close()" type="button" aria-label="Tutup panel" class="p-2.5 rounded-2xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                            <x-heroicon-o-x-mark class="w-6 h-6" />
                        </button>
                    </div>
                </div>

                {{-- Isi panel --}}
                <main class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    {{ $slot }}
                </main>

                {{-- Footer panel --}}
                @if(isset($footer))
                    <div class="px-8 py-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-900/50 backdrop-blur-md shrink-0">
                        {{ $footer }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
