// public/js/patrons.js
(function () {
    const config = window.PatronsConfig || {};

    document.addEventListener('DOMContentLoaded', () => {
        // ====== BÚSQUEDA EN TIEMPO REAL ======
        const searchInput = document.getElementById('patron-search-text');
        if (searchInput) {
            const rows = document.querySelectorAll('tbody[data-patrons] tr[data-patron-row]');

            const filterRows = () => {
                const text = searchInput.value.toLowerCase().trim();

                rows.forEach(row => {
                    const nombre = (row.dataset.nombre || '').toLowerCase();
                    const matches = !text || nombre.includes(text);

                    if (matches) {
                        row.classList.remove('hidden');
                    } else {
                        row.classList.add('hidden');
                    }
                });
            };

            searchInput.addEventListener('input', filterRows);
        }
    });

    // ====== EDITAR CON SWAL + SUBMIT DEL FORM ======
    window.openEditPatronModal = function (button) {
        const form = button.closest('form');
        if (!form) return;

        const inputNombre = form.querySelector('input[name="nombre"]');
        const currentName = button.dataset.nombre || (inputNombre ? inputNombre.value : '');

        Swal.fire({
            title: 'Editar patrón',
            input: 'text',
            inputValue: currentName,
            inputAttributes: {
                autocapitalize: 'on',
                maxlength: '255'
            },
            showCancelButton: true,
            confirmButtonText: 'Guardar cambios',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#6b7280',
            showLoaderOnConfirm: true,
            preConfirm: (value) => {
                const v = (value || '').trim();
                if (!v) {
                    Swal.showValidationMessage('El nombre de la empresa es obligatorio');
                    return false;
                }
                return v;
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then(result => {
            if (!result.isConfirmed) return;

            if (inputNombre) {
                inputNombre.value = result.value;
            }

            form.submit();
        });
    };

    // ====== ELIMINAR CON SWAL + SUBMIT DEL FORM ======
    window.confirmDeletePatron = function (button) {
        const form = button.closest('form');
        if (!form) return;

        Swal.fire({
            title: '¿Eliminar patrón?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280'
        }).then(result => {
            if (!result.isConfirmed) return;
            form.submit();
        });
    };
})();
