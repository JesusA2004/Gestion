// public/js/departamentos.js
(function () {
    const config    = window.DepartamentosConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl   = config.baseUrl || '';

    // ====== Filtros en tiempo real (texto) ======
    document.addEventListener('DOMContentLoaded', () => {
        const textInput   = document.getElementById('departamento-search-text');

        if (!textInput) return;

        const filterRows = () => {
            const text   = textInput.value.toLowerCase().trim();

            const rows = document.querySelectorAll(
                'tbody[data-departamentos] tr[data-departamento-row]'
            );

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
    window.openCreateDepartamentoModal = async function () {
        const { value: formValues } = await Swal.fire({
            title: 'Nuevo departamento',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombre</span>
                        <input id="swal-nombre" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Dirección</span>
                        <input id="swal-direccion" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200" />
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
                    Swal.showValidationMessage('El nombre del departamento es obligatorio');
                    return false;
                }

                return {
                    nombre,
                    direccion,
                };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/departamentos`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formValues),
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Guardado',
                text: data.message || 'Departamento registrado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al registrar el departamento.');
        }
    };

    // ====== Editar ======
    window.openEditDepartamentoModal = async function (button) {
        const departamento = {
            id: button.dataset.id,
            nombre: button.dataset.nombre || '',
            direccion: button.dataset.direccion || '',
        };

        const { value: formValues } = await Swal.fire({
            title: 'Editar departamento',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombre</span>
                        <input id="swal-nombre" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${departamento.nombre}" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Dirección</span>
                        <input id="swal-direccion" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${departamento.direccion || ''}" />
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
                    Swal.showValidationMessage('El nombre del departamento es obligatorio');
                    return false;
                }

                return {
                    nombre,
                    direccion,
                };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/departamentos/${departamento.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(formValues),
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Actualizado',
                text: data.message || 'Departamento actualizado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al actualizar el departamento.');
        }
    };

    // ====== Eliminar ======
    window.confirmDeleteDepartamento = async function (id) {
        const result = await Swal.fire({
            title: '¿Eliminar departamento?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`${baseUrl}/departamentos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            await Swal.fire({
                icon: 'success',
                title: 'Eliminado',
                text: data.message || 'Departamento eliminado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al eliminar el departamento.');
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
