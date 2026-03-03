<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="flex min-h-screen">

            @auth
                @include('layouts.navigation')
            @endauth

            <div x-data="{ collapsed: localStorage.getItem('sidebar_collapsed') === 'true' }"
                 x-on:storage.window="collapsed = localStorage.getItem('sidebar_collapsed') === 'true'"
                 :class="collapsed ? 'ml-[72px]' : 'ml-[260px]'"
                 class="flex flex-col flex-1 min-h-screen transition-all duration-300 ease-in-out">

                @if (isset($header))
                    <header class="flex-shrink-0 px-6 py-4 bg-white border-b border-gray-200">
                        {{ $header }}
                    </header>
                @endif

                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>

            </div>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </body>
</html>
