<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gestion') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Icono -->
        <link rel="icon" type="image/png" href="{{ asset('img/icon-16.png') }}" />

        <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet">

        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        {{-- estado global del layout --}}
        <div
            x-data="{ sidebarOpen: false }"
            class="min-h-screen bg-gray-100 sb-shell"
        >
            {{-- SIDEBAR + TOPNAV --}}
            @include('layouts.navigation')

            {{-- CONTENIDO PRINCIPAL QUE SE EMPUJA --}}
            <div
                class="sb-main"
                :class="sidebarOpen ? 'sb-main-shifted' : 'sb-main-normal'"
            >
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        @stack('scripts')

    </body>
</html>
