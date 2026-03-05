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
    <body class="font-sans antialiased bg-gray-50" style="overflow-y:auto;">
        <script>
            // Apply margin before paint — zero flicker
            document.documentElement.style.setProperty(
                '--sidebar-w',
                localStorage.getItem('sidebar_collapsed') === 'true' ? '72px' : '260px'
            );
        </script>
        <div class="flex" style="min-height:100vh;">

            @auth
                @include('layouts.partials.sidebar')
            @endauth

            <div id="main-content"
                 class="flex flex-col flex-1"
                 style="min-height:100vh; margin-left:var(--sidebar-w); transition:margin-left 0.3s ease;">

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
