<x-app-layout>
    @php
        $u = Auth::user();
    @endphp

    <x-slot name="header">
        <div class="perfil-hero flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            {{-- Lado izquierdo: título + texto --}}
            <div class="space-y-1">
                <p class="text-xs font-semibold tracking-[0.18em] uppercase text-indigo-500">
                    Panel de cuenta
                </p>

                <h2 class="text-2xl lg:text-3xl font-semibold tracking-tight text-slate-800">
                    Perfil
                </h2>

                <p class="text-sm text-slate-500 max-w-xl">
                    Administra tu información personal, contraseña y seguridad de la cuenta.
                </p>
            </div>
        </div>
    </x-slot>

    {{-- poco padding para no dejar huecos grandes --}}
    <div class="pt-3 pb-8">
        {{-- contenedor a lo ancho del main --}}
        <div class="w-full px-4 sm:px-6 lg:px-8">
            {{-- GRID RESPONSIVE
                 - base: 1 columna
                 - md:   2 columnas
                 - xl:   4 columnas (2+2 arriba, 4 abajo)
            --}}
            <div class="grid items-stretch gap-6 lg:gap-8 md:grid-cols-2 xl:grid-cols-4">
                {{-- Información personal --}}
                <div class="perfil-panel h-full flex flex-col xl:col-span-2">
                    @include('profile.partials.update-profile-information-form')
                </div>

                {{-- Cambiar contraseña --}}
                <div class="perfil-panel h-full flex flex-col xl:col-span-2">
                    @include('profile.partials.update-password-form')
                </div>

                {{-- Eliminar cuenta (más ancho, fila completa) --}}
                <div class="perfil-panel h-full flex flex-col xl:col-span-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="{{ asset('css/perfil.css') }}" rel="stylesheet">
    @endpush
</x-app-layout>
