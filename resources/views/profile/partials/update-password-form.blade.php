<section x-data="{ open: true }" class="perfil-section space-y-4">
    <header
        @click="open = !open"
        class="perfil-section-header flex items-center justify-between cursor-pointer select-none"
    >
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Actualizar contraseña
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Asegúrate de utilizar una contraseña larga, segura y difícil de adivinar.
            </p>
        </div>

        <!-- Flecha ↑ / ↓ -->
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
                <path
                    d="M6 9l6 6 6-6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                />
            </svg>
        </button>
    </header>

    <div
        x-show="open"
        x-transition
        class="accordion-enter accordion-leave overflow-hidden"
    >
        <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-6">
            @csrf
            @method('put')

            <div>
                <x-input-label for="update_password_current_password" value="Contraseña actual" />
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" value="Nueva contraseña" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" value="Confirmar contraseña" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                <x-primary-button>Guardar</x-primary-button>

                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600"
                    >Guardado.</p>
                @endif
            </div>
        </form>
    </div>
</section>
