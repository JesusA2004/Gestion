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

    </head>

    <body class="font-sans text-slate-50 antialiased bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950">

        {{-- NAVBAR PARA INVITADOS --}}
        <header class="border-b border-slate-800">
            <nav class="fixed w-full z-20 top-0 start-0 border-b border-slate-800 bg-transparent">
                <div class="max-w-7xl flex flex-wrap items-center justify-between mx-auto px-4 py-3">

                    {{-- Logo + nombre --}}
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <x-application-logo class="w-9 h-9" />
                        <span class="font-semibold text-sm sm:text-base tracking-tight">
                            {{ config('app.name', 'Gestión de Empleados') }}
                        </span>
                    </a>

                    {{-- Botón hamburguesa (solo móvil) --}}
                    <button
                        id="menu-toggle"
                        type="button"
                        class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-slate-200 rounded-md md:hidden hover:bg-slate-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-controls="navbar-default"
                        aria-expanded="false"
                    >
                        <span class="sr-only">Abrir menú principal</span>
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                                d="M5 7h14M5 12h14M5 17h14"/>
                        </svg>
                    </button>

                    {{-- Menú de enlaces --}}
                    <div class="hidden w-full md:block md:w-auto" id="navbar-default">
                        <ul class="font-medium flex flex-col p-4 mt-4 border border-slate-800 rounded-lg bg-slate-900/90 md:flex-row md:space-x-6 md:mt-0 md:border-0 md:bg-transparent">
                            <li>
                                <a href="{{ route('login') }}"
                                class="relative block py-2 px-3 rounded-md text-slate-200
                                        hover:text-amber-500
                                        md:hover:bg-transparent md:p-0
                                        after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2 after:bottom-0
                                        after:w-0 after:h-[2px] after:bg-yellow-500
                                        after:transition-all after:duration-300
                                        hover:after:w-3/4">
                                    Iniciar sesión
                                </a>
                            </li>

                            <li>
                                <a href="{{ route('register') }}"
                                class="relative block py-2 px-3 rounded-md text-slate-200
                                        hover:text-amber-500
                                        md:hover:bg-transparent md:p-0
                                        after:content-[''] after:absolute after:left-1/2 after:-translate-x-1/2 after:bottom-0
                                        after:w-0 after:h-[2px] after:bg-yellow-500
                                        after:transition-all after:duration-300
                                        hover:after:w-3/4">
                                    Crear cuenta
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </nav>
        </header>

        {{-- CONTENIDO DE LA VISTA --}}
        <main class="min-h-[calc(100vh-4rem)] flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-6xl">
                {{ $slot }}
            </div>
        </main>

        {{-- Script del menu hamburguesa --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const btn = document.getElementById('menu-toggle');
                const menu = document.getElementById('navbar-default');

                if (btn && menu) {
                    btn.addEventListener('click', () => {
                        menu.classList.toggle('hidden');
                    });
                }
            });
        </script>

        @stack('scripts')

    </body>
</html>
