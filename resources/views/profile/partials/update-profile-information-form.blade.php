<section x-data="{ open: true }" class="perfil-section space-y-4">
    <!-- Header con viñeta -->
    <header
        @click="open = !open"
        class="perfil-section-header flex items-center justify-between cursor-pointer select-none"
    >
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Información personal
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Actualiza la información de tu perfil y tu correo electrónico.
            </p>
        </div>

        <!-- ícono tipo caret ↑ / ↓ -->
        <button
            type="button"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white shadow-sm"
        >
            <svg
                class="w-4 h-4 text-gray-600 transition-transform duration-200 transform"
                :class="open ? 'rotate-0' : 'rotate-180'"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <!-- chevron-down por defecto; rotamos 180° cuando está cerrado -->
                <path
                    d="M6 9l6 6 6-6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
    </header>

    <!-- Contenido colapsable -->
    <div
        x-show="open"
        x-transition
        class="accordion-enter accordion-leave overflow-hidden"
    >
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-6">
            @csrf
            @method('patch')

            <!-- Nombre -->
            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" name="name" type="text"
                              class="mt-1 block w-full"
                              value="{{ old('name', $user->name) }}"
                              required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input id="email" name="email" type="email"
                              class="mt-1 block w-full"
                              value="{{ old('email', $user->email) }}"
                              required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800">
                            Tu correo electrónico no está verificado.
                            <button form="send-verification"
                                    class="underline text-sm text-gray-600 hover:text-gray-900">
                                Reenviar correo de verificación
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                Se ha enviado un nuevo enlace de verificación.
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Botones -->
            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p x-data="{ show: true }"
                       x-show="show"
                       x-transition
                       x-init="setTimeout(() => show = false, 2000)"
                       class="text-sm text-gray-600">
                        Guardado.
                    </p>
                @endif
            </div>
        </form>
    </div>
</section>
