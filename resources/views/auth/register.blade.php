<x-guest-layout>
    <div class="max-w-md mx-auto mt-10 mb-8 bg-slate-900/80 border border-slate-800/80 
                rounded-2xl shadow-2xl shadow-black/40 px-6 py-8 sm:px-8 sm:py-10 
                backdrop-blur">
        
        {{-- Encabezado --}}
        <div class="mb-6 text-center">
            <h1 class="text-2xl font-semibold text-slate-50">
                Crea tu cuenta
            </h1>
            <p class="mt-1 text-sm text-slate-400">
                Registra tus datos para comenzar a utilizar el sistema de gestión de empleados.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            {{-- Nombre --}}
            <div>
                <x-input-label for="name" :value="__('Nombre completo')" class="text-slate-200" />
                <x-text-input
                    id="name"
                    type="text"
                    name="name"
                    placeholder="Ingresa tu nombre completo"
                    :value="old('name')"
                    required
                    autofocus
                    autocomplete="name"
                    class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900/70 
                           text-slate-100 placeholder-slate-500
                           focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1
                           transition-colors duration-150"
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm" />
            </div>

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Correo electrónico')" class="text-slate-200" />
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    placeholder="ejemplo@dominio.com"
                    :value="old('email')"
                    required
                    autocomplete="username"
                    class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900/70 
                           text-slate-100 placeholder-slate-500
                           focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1
                           transition-colors duration-150"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
            </div>

            {{-- Password con ojo --}}
            <div>
                <x-input-label for="password" :value="__('Contraseña')" class="text-slate-200" />

                <div class="relative">
                    <x-text-input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="********"
                        required
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900/70 
                               text-slate-100 placeholder-slate-500
                               focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1
                               transition-colors duration-150 pr-10"
                    />

                    {{-- Botón ojo --}}
                    <button
                        type="button"
                        data-toggle-password
                        data-target="password"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        </svg>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
            </div>

            {{-- Confirmar password con ojo --}}
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" class="text-slate-200" />

                <div class="relative">
                    <x-text-input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        placeholder="Repite tu contraseña"
                        required
                        autocomplete="new-password"
                        class="mt-1 block w-full rounded-xl border border-slate-700 bg-slate-900/70 
                               text-slate-100 placeholder-slate-500
                               focus:border-indigo-400 focus:ring-indigo-400 focus:ring-1
                               transition-colors duration-150 pr-10"
                    />

                    {{-- Botón ojo --}}
                    <button
                        type="button"
                        data-toggle-password
                        data-target="password_confirmation"
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200 transition"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        </svg>
                    </button>
                </div>

                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm" />
            </div>

            {{-- Pie: enlace a login + botón --}}
            <div class="pt-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <a href="{{ route('login') }}"
                   class="text-xs sm:text-sm text-slate-400 hover:text-indigo-300 
                          underline-offset-4 hover:underline transition-colors">
                    ¿Ya tienes una cuenta? Inicia sesión
                </a>

                <x-primary-button
                    class="w-full sm:w-auto inline-flex items-center justify-center 
                           px-6 py-2.5 rounded-xl text-sm font-semibold
                           bg-gradient-to-r from-indigo-500 to-blue-500 
                           hover:from-indigo-400 hover:to-blue-400
                           shadow-lg shadow-indigo-500/40
                           focus:ring-indigo-400 focus:ring-offset-0
                           transition-transform transition-shadow duration-150
                           hover:-translate-y-0.5">
                    {{ __('Registrarme') }}
                </x-primary-button>
            </div>

            <p class="mt-3 text-[11px] text-slate-500 text-center leading-relaxed">
                Al registrarte aceptas el uso responsable del sistema y el tratamiento interno de la información de empleados.
            </p>
        </form>
    </div>

    {{-- Script para mostrar/ocultar contraseña --}}
    @push('scripts')
        <script src="{{ asset('js/password.js') }}"></script>
    @endpush
    
</x-guest-layout>
