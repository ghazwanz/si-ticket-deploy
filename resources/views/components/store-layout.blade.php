<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JoinFest') }} - Store</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </head>
    <body class="min-h-screen flex flex-col bg-background font-sans antialiased text-foreground">

        <x-public.header />

        @if (isset($header))
            <header class="bg-background border-b border-border/60">
                <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $header }}
                </div>
            </header>
        @endif

        <main class="flex-1 w-full max-w-7xl mx-auto" data-page-shell>
            <div data-reveal data-reveal-delay="80" class="opacity-0 translate-y-6 scale-[0.98] blur-sm transition-all duration-700 ease-out">
                {{ $slot }}
            </div>
        </main>

        <x-public.footer />

    </body>
</html>
