// public/js/sucursals.js

(function () {
    const config = window.SucursalsConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl   = config.baseUrl || '';

    // ====== Filtros en tiempo real (texto + estado) ======
    document.addEventListener('DOMContentLoaded', () => {
        const textInput  = document.getElementById('sucursal-search-text');

        if (!textInput) return;

        const filterRows = () => {
            const text   = textInput.value.toLowerCase().trim();

            const rows = document.querySelectorAll('tbody[data-sucursals] tr[data-sucursal-row]');

            rows.forEach(row => {
                const nombre    = (row.dataset.nombre || '').toLowerCase();
                const direccion = (row.dataset.direccion || '').toLowerCase();

                const matchesText =
                    !text ||
                    nombre.includes(text) ||
                    direccion.includes(text);

                if (matchesText) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        };

        textInput.addEventListener('input', filterRows);
    });

    // ====== Crear ======
    window.openCreateSucursalModal = async function () {
        const { value: formValues } = await Swal.fire({
            title: 'Nueva plaza',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombre</span>
                        <input id="swal-nombre" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Dirección</span>
                        <textarea id="swal-direccion"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            rows="3"></textarea>
                    </label>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            preConfirm: () => {
                const nombre    = document.getElementById('swal-nombre').value.trim();
                const direccion = document.getElementById('swal-direccion').value.trim();

                if (!nombre) {
                    Swal.showValidationMessage('El nombre de la plaza es obligatorio');
                    return false;
                }

                return { nombre, direccion };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/sucursals`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formValues),
            });

            if (!response.ok) {
                throw await response.json();
            }

            const data = await response.json();

            await Swal.fire({
                icon: 'success',
                title: 'Guardado',
                text: data.message || 'Plaza registrada correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al registrar la sucursal.');
        }
    };

    // ====== Editar ======
    window.openEditSucursalModal = async function (button) {
        const sucursal = {
            id:        button.dataset.id,
            nombre:    button.dataset.nombre || '',
            direccion: button.dataset.direccion || '',
        };

        const { value: formValues } = await Swal.fire({
            title: 'Editar plaza',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombre</span>
                        <input id="swal-nombre" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${sucursal.nombre.replace(/"/g, '&quot;')}" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Dirección</span>
                        <textarea id="swal-direccion"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            rows="3">${(sucursal.direccion || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                    </label>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            preConfirm: () => {
                const nombre    = document.getElementById('swal-nombre').value.trim();
                const direccion = document.getElementById('swal-direccion').value.trim();

                if (!nombre) {
                    Swal.showValidationMessage('El nombre de la sucursal es obligatorio');
                    return false;
                }

                return { nombre, direccion };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/sucursals/${sucursal.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formValues),
            });

            if (!response.ok) {
                throw await response.json();
            }

            const data = await response.json();

            await Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: data.message || 'Sucursal actualizada correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al actualizar la sucursal.');
        }
    };

    // ====== Eliminar ======
    window.confirmDeleteSucursal = async function (id) {
        const result = await Swal.fire({
            title: '¿Eliminar plaza?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`${baseUrl}/sucursals/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw await response.json();
            }

            const data = await response.json();

            await Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: data.message || 'Sucursal eliminada correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al eliminar la sucursal.');
        }
    };

    function handleCrudError(error, fallbackMessage) {
        let message = fallbackMessage;

        if (error && error.errors) {
            const firstKey = Object.keys(error.errors)[0];
            if (firstKey && error.errors[firstKey][0]) {
                message = error.errors[firstKey][0];
            }
        } else if (error && error.message) {
            message = error.message;
        }

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonColor: '#4f46e5',
        });
    }
})();
