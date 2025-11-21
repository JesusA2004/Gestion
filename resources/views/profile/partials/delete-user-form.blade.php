<section x-data="{ open: true }" class="perfil-section space-y-4">
    <!-- Header -->
    <header
        @click="open = !open"
        class="perfil-section-header flex items-center justify-between cursor-pointer select-none"
    >
        <div>
            <h2 class="text-lg font-semibold text-gray-900">
                Eliminar cuenta
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Una vez que elimines tu cuenta, todos tus datos e información serán borrados de forma permanente. Antes de continuar, descarga cualquier información que desees conservar.
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

    <!-- Contenido colapsable -->
    <div
        x-show="open"
        x-transition
        class="accordion-enter accordion-leave overflow-hidden"
    >
        <div class="mt-4 space-y-6">
            {{-- Botón que dispara SweetAlert2 --}}
            <x-danger-button
                type="button"
                x-on:click.prevent="
                    if (typeof Swal === 'undefined') {
                        console.error('SweetAlert2 no está disponible');
                        return;
                    }

                    Swal.fire({
                        title: '¿Eliminar cuenta?',
                        text: 'Esta acción no se puede deshacer. Escribe tu contraseña para confirmar.',
                        input: 'password',
                        inputPlaceholder: 'Contraseña',
                        inputAttributes: {
                            autocomplete: 'current-password'
                        },
                        showCancelButton: true,
                        confirmButtonText: 'Eliminar',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: true,
                        focusConfirm: false,
                        customClass: {
                            popup: 'swal2-mindora' // tu estilo custom si ya lo usas
                        },
                        preConfirm: (value) => {
                            if (!value) {
                                Swal.showValidationMessage('Debes escribir tu contraseña');
                            }
                            return value;
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            const form = document.getElementById('form-eliminar-cuenta');
                            if (!form) return;

                            const input = form.querySelector('input[name=password]');
                            if (input) {
                                input.value = result.value;
                            }
                            form.submit();
                        }
                    });
                "
            >
                Eliminar cuenta
            </x-danger-button>

            {{-- Formulario oculto que realmente elimina la cuenta --}}
            <form
                id="form-eliminar-cuenta"
                method="post"
                action="{{ route('profile.destroy') }}"
                class="hidden"
            >
                @csrf
                @method('delete')

                <input type="password" name="password" />
            </form>

            @if ($errors->userDeletion->any())
                <div class="text-sm text-red-600">
                    @foreach ($errors->userDeletion->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
