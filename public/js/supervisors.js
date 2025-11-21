// public/js/supervisors.js

(function () {
    const config = window.SupervisorsConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl   = config.baseUrl || '';

    // ====== Filtro en tiempo real (texto + fecha) ======
    document.addEventListener('DOMContentLoaded', () => {
        const textInput = document.getElementById('supervisor-search-text');
        const dateInput = document.getElementById('supervisor-search-date');

        if (!textInput || !dateInput) return;

        const filterRows = () => {
            const text = textInput.value.toLowerCase().trim();
            const date = dateInput.value; // formato YYYY-MM-DD

            const rows = document.querySelectorAll('tbody[data-supervisors] tr[data-supervisor-row]');

            rows.forEach(row => {
                const fullName = (row.dataset.fullName || '').toLowerCase();
                const created  = row.dataset.createdAt || '';

                const matchesText = !text || fullName.includes(text);
                const matchesDate = !date || created === date;

                if (matchesText && matchesDate) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        };

        textInput.addEventListener('input', filterRows);
        dateInput.addEventListener('input', filterRows);
    });

    // ====== Crear ======
    window.openCreateSupervisorModal = async function () {
        const { value: formValues } = await Swal.fire({
            title: 'Nuevo supervisor',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombres</span>
                        <input id="swal-nombres" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Apellido paterno</span>
                        <input id="swal-apellidoPaterno" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Apellido materno</span>
                        <input id="swal-apellidoMaterno" type="text"
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
                const nombres = document.getElementById('swal-nombres').value.trim();
                const apPat   = document.getElementById('swal-apellidoPaterno').value.trim();
                const apMat   = document.getElementById('swal-apellidoMaterno').value.trim();

                if (!nombres || !apPat) {
                    Swal.showValidationMessage('Nombres y Apellido paterno son obligatorios');
                    return false;
                }

                return { nombres, apellidoPaterno: apPat, apellidoMaterno: apMat };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/supervisors`, {
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
                text: data.message || 'Supervisor registrado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al registrar el patrón.');
        }
    };

    // ====== Editar ======
    window.openEditSupervisorModal = async function (button) {
        const supervisor = {
            id: button.dataset.id,
            nombres: button.dataset.nombres || '',
            apellidoPaterno: button.dataset.apellidoPaterno || '',
            apellidoMaterno: button.dataset.apellidoMaterno || '',
        };

        const { value: formValues } = await Swal.fire({
            title: 'Editar patrón',
            html: `
                <div class="space-y-3 text-left">
                    <label class="block text-sm">
                        <span class="text-gray-700">Nombres</span>
                        <input id="swal-nombres" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${supervisor.nombres}" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Apellido paterno</span>
                        <input id="swal-apellidoPaterno" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${supervisor.apellidoPaterno}" />
                    </label>
                    <label class="block text-sm">
                        <span class="text-gray-700">Apellido materno</span>
                        <input id="swal-apellidoMaterno" type="text"
                            class="mt-1 block w-full border rounded px-2 py-1 text-sm border-slate-200"
                            value="${supervisor.apellidoMaterno}" />
                    </label>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            preConfirm: () => {
                const nombres = document.getElementById('swal-nombres').value.trim();
                const apPat   = document.getElementById('swal-apellidoPaterno').value.trim();
                const apMat   = document.getElementById('swal-apellidoMaterno').value.trim();

                if (!nombres || !apPat) {
                    Swal.showValidationMessage('Nombres y Apellido paterno son obligatorios');
                    return false;
                }

                return { nombres, apellidoPaterno: apPat, apellidoMaterno: apMat };
            }
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/supervisors/${supervisor.id}`, {
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
                text: data.message || 'Patrón actualizado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al actualizar el patrón.');
        }
    };

    // ====== Eliminar ======
    window.confirmDeleteSupervisor = async function (id) {
        const result = await Swal.fire({
            title: '¿Eliminar patrón?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`${baseUrl}/supervisors/${id}`, {
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
                text: data.message || 'Patrón eliminado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al eliminar el patrón.');
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
