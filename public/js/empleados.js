// public/js/empleados.js
(function () {
    const config = window.EmpleadosConfig || {};
    const csrfToken = config.csrfToken || '';
    const baseUrl   = config.baseUrl || '';
    const lookups   = config.lookups || {};

    /* ===================== Filtro tabla principal ===================== */

    document.addEventListener('DOMContentLoaded', () => {
        const textInput   = document.getElementById('empleado-search-text');
        const estadoInput = document.getElementById('empleado-filter-estado');

        const applyFilters = () => {
            const text   = (textInput?.value || '').toLowerCase().trim();
            const estado = (estadoInput?.value || '').toLowerCase().trim();

            const rows = document.querySelectorAll('tbody[data-empleados] tr[data-empleado-row]');
            rows.forEach(row => {
                const rowSearch = (row.dataset.search || '').toLowerCase();
                const rowEstado = (row.dataset.estado || '').toLowerCase();

                const matchesText   = !text   || rowSearch.includes(text);
                const matchesEstado = !estado || rowEstado === estado;

                if (matchesText && matchesEstado) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        };

        if (textInput)   textInput.addEventListener('input', applyFilters);
        if (estadoInput) estadoInput.addEventListener('change', applyFilters);
    });

    /* ===================== Helpers generales ===================== */

    function handleCrudError(error, fallbackMessage) {
        let message = fallbackMessage;

        if (error && error.errors) {
            const firstKey = Object.keys(error.errors)[0];
            if (firstKey && error.errors[firstKey] && error.errors[firstKey][0]) {
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

    function buildSupervisorNombreCompleto(sup) {
        const parts = [sup.nombres, sup.apellidoPaterno, sup.apellidoMaterno].filter(Boolean);
        return parts.join(' ');
    }

    /**
     * Inicializa un buscador con lista visible y filtrable.
     * - scopeEl: elemento raíz del modal (Swal.getHtmlContainer()).
     * - name: string ('patron' | 'sucursal' | 'departamento' | 'supervisor').
     * - items: array de registros.
     * - selectedId: id preseleccionado (string|number|null).
     */
    function setupLookup(scopeEl, name, items, selectedId) {
        const searchInput = scopeEl.querySelector(`[data-lookup-search="${name}"]`);
        const listEl      = scopeEl.querySelector(`[data-lookup-list="${name}"]`);
        if (!searchInput || !listEl) return;

        // Si hay seleccionado, marcamos el data-selected-id inicial
        if (selectedId != null && selectedId !== '') {
            searchInput.dataset.selectedId = String(selectedId);
        }

        const getLabel = (item) => {
            if (name === 'supervisor') {
                return buildSupervisorNombreCompleto(item) || `(ID ${item.id})`;
            }
            return item.nombre || `(ID ${item.id})`;
        };

        const render = (query = '') => {
            const q = query.toLowerCase().trim();
            listEl.innerHTML = '';

            items
                .filter(it => {
                    const label = getLabel(it).toLowerCase();
                    return !q || label.includes(q);
                })
                .forEach(it => {
                    const isActive =
                        searchInput.dataset.selectedId &&
                        String(searchInput.dataset.selectedId) === String(it.id);

                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className =
                        'w-full flex items-center justify-between px-3 py-1.5 text-sm rounded-md border ' +
                        (isActive
                            ? 'bg-indigo-50 border-indigo-400 text-indigo-700 font-semibold'
                            : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50');
                    btn.dataset.id = it.id;

                    const spanLabel = document.createElement('span');
                    spanLabel.textContent = getLabel(it);

                    const spanId = document.createElement('span');
                    spanId.className = 'ml-2 text-[11px] text-slate-400';
                    spanId.textContent = `ID: ${it.id}`;

                    btn.appendChild(spanLabel);
                    btn.appendChild(spanId);

                    btn.addEventListener('click', () => {
                        searchInput.dataset.selectedId = String(it.id);
                        searchInput.value = getLabel(it);
                        render(q);
                    });

                    listEl.appendChild(btn);
                });

            if (!items.length) {
                const empty = document.createElement('div');
                empty.className = 'px-3 py-2 text-xs text-slate-400';
                empty.textContent = 'Sin registros disponibles.';
                listEl.appendChild(empty);
            }
        };

        render();

        searchInput.addEventListener('input', (e) => {
            render(e.target.value);
        });
    }

    function collectEmpleadoPayloadFromModal(isEdit = false, existing) {
        const get = (id) => {
            const el = document.getElementById(id);
            return el ? el.value.trim() : '';
        };

        // Lookups seleccionados
        const patronSearch      = document.querySelector('[data-lookup-search="patron"]');
        const sucursalSearch    = document.querySelector('[data-lookup-search="sucursal"]');
        const departamentoSearch= document.querySelector('[data-lookup-search="departamento"]');
        const supervisorSearch  = document.querySelector('[data-lookup-search="supervisor"]');

        const patronId       = patronSearch?.dataset.selectedId || '';
        const sucursalId     = sucursalSearch?.dataset.selectedId || '';
        const departamentoId = departamentoSearch?.dataset.selectedId || '';
        const supervisorId   = supervisorSearch?.dataset.selectedId || '';

        const payload = {
            nombres: get('swal-nombres'),
            apellidoPaterno: get('swal-apellidoPaterno'),
            apellidoMaterno: get('swal-apellidoMaterno'),
            numero_trabajador: get('swal-numero_trabajador'),
            estado: get('swal-estado') || 'alta',

            patron_id: patronId || null,
            sucursal_id: sucursalId || null,
            departamento_id: departamentoId || null,
            supervisor_id: supervisorId || null,

            fecha_ingreso: get('swal-fecha_ingreso') || null,
            fecha_baja: get('swal-fecha_baja') || null,

            numero_imss: get('swal-numero_imss'),
            registro_patronal: get('swal-registro_patronal'),
            codigo_postal: get('swal-codigo_postal'),
            fecha_alta_imss: get('swal-fecha_alta_imss') || null,
            curp: get('swal-curp'),
            rfc: get('swal-rfc'),

            cuenta_bancaria: get('swal-cuenta_bancaria'),
            tarjeta: get('swal-tarjeta'),
            clabe_interbancaria: get('swal-clabe_interbancaria'),
            banco: get('swal-banco'),

            sueldo_diario_bruto: get('swal-sueldo_diario_bruto'),
            sueldo_diario_neto: get('swal-sueldo_diario_neto'),
            salario_diario_imss: get('swal-salario_diario_imss'),
            sdi: get('swal-sdi'),

            empresa_facturar: get('swal-empresa_facturar'),
            total_guardias_factura: get('swal-total_guardias_factura') || 0,
            importe_factura_mensual: get('swal-importe_factura_mensual') || 0,
        };

        // Validaciones mínimas (nombre, apPaterno, número trabajador, patrón, sucursal, departamento, IMSS, CURP, RFC, registro patronal)
        if (!payload.nombres || !payload.apellidoPaterno || !payload.numero_trabajador) {
            Swal.showValidationMessage('Nombres, Apellido paterno y Número de trabajador son obligatorios.');
            return false;
        }
        if (!payload.patron_id || !payload.sucursal_id || !payload.departamento_id) {
            Swal.showValidationMessage('Debes seleccionar Patrón, Sucursal y Departamento.');
            return false;
        }
        if (!payload.numero_imss || !payload.curp || !payload.rfc || !payload.registro_patronal) {
            Swal.showValidationMessage('Número IMSS, Registro patronal, CURP y RFC son obligatorios.');
            return false;
        }

        return payload;
    }

    /* ===================== Crear Empleado ===================== */

    window.openCreateEmpleadoModal = async function () {
        const { value: formValues } = await Swal.fire({
            title: 'Nuevo empleado',
            width: '900px',
            html: `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left text-sm">
                    <!-- Columna 1: Datos personales y laborales -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos personales</h3>
                            <label class="block text-xs text-slate-600">
                                Nombres
                                <input id="swal-nombres" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Apellido paterno
                                <input id="swal-apellidoPaterno" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Apellido materno
                                <input id="swal-apellidoMaterno" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos laborales</h3>
                            <label class="block text-xs text-slate-600">
                                Número trabajador
                                <input id="swal-numero_trabajador" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Estado
                                    <select id="swal-estado"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="alta" selected>Alta</option>
                                        <option value="baja">Baja</option>
                                    </select>
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Fecha ingreso
                                    <input id="swal-fecha_ingreso" type="date"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>

                            <label class="block text-xs text-slate-600">
                                Fecha baja
                                <input id="swal-fecha_baja" type="date"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Asignación</h3>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Patrón (empresa)
                                    <input type="text" data-lookup-search="patron"
                                        placeholder="Buscar patrón..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="patron"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Sucursal
                                    <input type="text" data-lookup-search="sucursal"
                                        placeholder="Buscar sucursal..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="sucursal"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Departamento
                                    <input type="text" data-lookup-search="departamento"
                                        placeholder="Buscar departamento..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="departamento"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Supervisor
                                    <input type="text" data-lookup-search="supervisor"
                                        placeholder="Buscar supervisor..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="supervisor"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: IMSS, bancarios, facturación -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos IMSS</h3>
                            <label class="block text-xs text-slate-600">
                                Número IMSS
                                <input id="swal-numero_imss" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Registro patronal
                                <input id="swal-registro_patronal" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Código postal
                                    <input id="swal-codigo_postal" type="text"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Fecha alta IMSS
                                    <input id="swal-fecha_alta_imss" type="date"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                            <label class="block text-xs text-slate-600">
                                CURP
                                <input id="swal-curp" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 uppercase focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                RFC
                                <input id="swal-rfc" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 uppercase focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos bancarios</h3>
                            <label class="block text-xs text-slate-600">
                                Banco
                                <input id="swal-banco" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Cuenta bancaria
                                <input id="swal-cuenta_bancaria" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Tarjeta
                                <input id="swal-tarjeta" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                CLABE interbancaria
                                <input id="swal-clabe_interbancaria" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sueldos</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Sueldo diario bruto
                                    <input id="swal-sueldo_diario_bruto" type="number" step="0.01"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Sueldo diario neto
                                    <input id="swal-sueldo_diario_neto" type="number" step="0.01"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Salario diario IMSS
                                    <input id="swal-salario_diario_imss" type="number" step="0.01"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    SDI
                                    <input id="swal-sdi" type="number" step="0.01"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Facturación</h3>
                            <label class="block text-xs text-slate-600">
                                Empresa a facturar
                                <input id="swal-empresa_facturar" type="text"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Total guardias factura
                                    <input id="swal-total_guardias_factura" type="number" step="1" min="0"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Importe factura mensual
                                    <input id="swal-importe_factura_mensual" type="number" step="0.01" min="0"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            didOpen: () => {
                const container = Swal.getHtmlContainer();
                setupLookup(container, 'patron', lookups.patrones || [], null);
                setupLookup(container, 'sucursal', lookups.sucursales || [], null);
                setupLookup(container, 'departamento', lookups.departamentos || [], null);
                setupLookup(container, 'supervisor', lookups.supervisores || [], null);
            },
            preConfirm: () => {
                return collectEmpleadoPayloadFromModal(false, null);
            },
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/empleados`, {
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
                text: data.message || 'Empleado registrado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al registrar el empleado.');
        }
    };

    /* ===================== Editar Empleado ===================== */

    window.openEditEmpleadoModal = async function (button) {
        const empleado = {
            id: button.dataset.id,
            nombres: button.dataset.nombres || '',
            apellidoPaterno: button.dataset.apellidoPaterno || '',
            apellidoMaterno: button.dataset.apellidoMaterno || '',
            numero_trabajador: button.dataset.numeroTrabajador || '',
            estado: button.dataset.estado || 'alta',
            fecha_ingreso: button.dataset.fechaIngreso || '',
            fecha_baja: button.dataset.fechaBaja || '',

            patron_id: button.dataset.patronId || '',
            sucursal_id: button.dataset.sucursalId || '',
            departamento_id: button.dataset.departamentoId || '',
            supervisor_id: button.dataset.supervisorId || '',

            numero_imss: button.dataset.numeroImss || '',
            registro_patronal: button.dataset.registroPatronal || '',
            codigo_postal: button.dataset.codigoPostal || '',
            fecha_alta_imss: button.dataset.fechaAltaImss || '',
            curp: button.dataset.curp || '',
            rfc: button.dataset.rfc || '',

            cuenta_bancaria: button.dataset.cuentaBancaria || '',
            tarjeta: button.dataset.tarjeta || '',
            clabe_interbancaria: button.dataset.clabeInterbancaria || '',
            banco: button.dataset.banco || '',

            sueldo_diario_bruto: button.dataset.sueldoDiarioBruto || '',
            sueldo_diario_neto: button.dataset.sueldoDiarioNeto || '',
            salario_diario_imss: button.dataset.salarioDiarioImss || '',
            sdi: button.dataset.sdi || '',

            empresa_facturar: button.dataset.empresaFacturar || '',
            total_guardias_factura: button.dataset.totalGuardiasFactura || '',
            importe_factura_mensual: button.dataset.importeFacturaMensual || '',
        };

        const { value: formValues } = await Swal.fire({
            title: 'Editar empleado',
            width: '900px',
            html: `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left text-sm">
                    <!-- Columna 1 -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos personales</h3>
                            <label class="block text-xs text-slate-600">
                                Nombres
                                <input id="swal-nombres" type="text"
                                    value="${empleado.nombres}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Apellido paterno
                                <input id="swal-apellidoPaterno" type="text"
                                    value="${empleado.apellidoPaterno}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Apellido materno
                                <input id="swal-apellidoMaterno" type="text"
                                    value="${empleado.apellidoMaterno}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos laborales</h3>
                            <label class="block text-xs text-slate-600">
                                Número trabajador
                                <input id="swal-numero_trabajador" type="text"
                                    value="${empleado.numero_trabajador}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>

                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Estado
                                    <select id="swal-estado"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="alta" ${empleado.estado === 'alta' ? 'selected' : ''}>Alta</option>
                                        <option value="baja" ${empleado.estado === 'baja' ? 'selected' : ''}>Baja</option>
                                    </select>
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Fecha ingreso
                                    <input id="swal-fecha_ingreso" type="date"
                                        value="${empleado.fecha_ingreso}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>

                            <label class="block text-xs text-slate-600">
                                Fecha baja
                                <input id="swal-fecha_baja" type="date"
                                    value="${empleado.fecha_baja}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Asignación</h3>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Patrón (empresa)
                                    <input type="text" data-lookup-search="patron"
                                        data-selected-id="${empleado.patron_id || ''}"
                                        placeholder="Buscar patrón..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="patron"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Sucursal
                                    <input type="text" data-lookup-search="sucursal"
                                        data-selected-id="${empleado.sucursal_id || ''}"
                                        placeholder="Buscar sucursal..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="sucursal"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Departamento
                                    <input type="text" data-lookup-search="departamento"
                                        data-selected-id="${empleado.departamento_id || ''}"
                                        placeholder="Buscar departamento..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="departamento"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-xs text-slate-600">
                                    Supervisor
                                    <input type="text" data-lookup-search="supervisor"
                                        data-selected-id="${empleado.supervisor_id || ''}"
                                        placeholder="Buscar supervisor..."
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <div data-lookup-list="supervisor"
                                    class="mt-1 max-h-32 overflow-y-auto border border-slate-200 rounded-lg bg-white space-y-1"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2 -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos IMSS</h3>
                            <label class="block text-xs text-slate-600">
                                Número IMSS
                                <input id="swal-numero_imss" type="text"
                                    value="${empleado.numero_imss}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Registro patronal
                                <input id="swal-registro_patronal" type="text"
                                    value="${empleado.registro_patronal}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Código postal
                                    <input id="swal-codigo_postal" type="text"
                                        value="${empleado.codigo_postal}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Fecha alta IMSS
                                    <input id="swal-fecha_alta_imss" type="date"
                                        value="${empleado.fecha_alta_imss}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                            <label class="block text-xs text-slate-600">
                                CURP
                                <input id="swal-curp" type="text"
                                    value="${empleado.curp}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 uppercase focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                RFC
                                <input id="swal-rfc" type="text"
                                    value="${empleado.rfc}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 uppercase focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Datos bancarios</h3>
                            <label class="block text-xs text-slate-600">
                                Banco
                                <input id="swal-banco" type="text"
                                    value="${empleado.banco}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Cuenta bancaria
                                <input id="swal-cuenta_bancaria" type="text"
                                    value="${empleado.cuenta_bancaria}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                Tarjeta
                                <input id="swal-tarjeta" type="text"
                                    value="${empleado.tarjeta}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <label class="block text-xs text-slate-600">
                                CLABE interbancaria
                                <input id="swal-clabe_interbancaria" type="text"
                                    value="${empleado.clabe_interbancaria}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sueldos</h3>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Sueldo diario bruto
                                    <input id="swal-sueldo_diario_bruto" type="number" step="0.01"
                                        value="${empleado.sueldo_diario_bruto}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Sueldo diario neto
                                    <input id="swal-sueldo_diario_neto" type="number" step="0.01"
                                        value="${empleado.sueldo_diario_neto}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Salario diario IMSS
                                    <input id="swal-salario_diario_imss" type="number" step="0.01"
                                        value="${empleado.salario_diario_imss}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    SDI
                                    <input id="swal-sdi" type="number" step="0.01"
                                        value="${empleado.sdi}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Facturación</h3>
                            <label class="block text-xs text-slate-600">
                                Empresa a facturar
                                <input id="swal-empresa_facturar" type="text"
                                    value="${empleado.empresa_facturar}"
                                    class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="block text-xs text-slate-600">
                                    Total guardias factura
                                    <input id="swal-total_guardias_factura" type="number" step="1" min="0"
                                        value="${empleado.total_guardias_factura}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                                <label class="block text-xs text-slate-600">
                                    Importe factura mensual
                                    <input id="swal-importe_factura_mensual" type="number" step="0.01" min="0"
                                        value="${empleado.importe_factura_mensual}"
                                        class="mt-1 block w-full border rounded-lg px-2 py-1.5 text-sm border-slate-200 focus:ring-indigo-500 focus:border-indigo-500" />
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            didOpen: () => {
                const container = Swal.getHtmlContainer();

                const patronSelectedId =
                    container.querySelector('[data-lookup-search="patron"]')?.dataset.selectedId || empleado.patron_id || null;
                const sucursalSelectedId =
                    container.querySelector('[data-lookup-search="sucursal"]')?.dataset.selectedId || empleado.sucursal_id || null;
                const deptoSelectedId =
                    container.querySelector('[data-lookup-search="departamento"]')?.dataset.selectedId || empleado.departamento_id || null;
                const supervisorSelectedId =
                    container.querySelector('[data-lookup-search="supervisor"]')?.dataset.selectedId || empleado.supervisor_id || null;

                setupLookup(container, 'patron', lookups.patrones || [], patronSelectedId);
                setupLookup(container, 'sucursal', lookups.sucursales || [], sucursalSelectedId);
                setupLookup(container, 'departamento', lookups.departamentos || [], deptoSelectedId);
                setupLookup(container, 'supervisor', lookups.supervisores || [], supervisorSelectedId);
            },
            preConfirm: () => {
                return collectEmpleadoPayloadFromModal(true, empleado);
            },
        });

        if (!formValues) return;

        try {
            const response = await fetch(`${baseUrl}/empleados/${empleado.id}`, {
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
                text: data.message || 'Empleado actualizado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al actualizar el empleado.');
        }
    };

    /* ===================== Eliminar Empleado ===================== */

    window.confirmDeleteEmpleado = async function (id) {
        const result = await Swal.fire({
            title: '¿Eliminar empleado?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc2626',
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(`${baseUrl}/empleados/${id}`, {
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
                text: data.message || 'Empleado eliminado correctamente.',
                confirmButtonColor: '#4f46e5',
            });

            window.location.reload();
        } catch (error) {
            handleCrudError(error, 'Ocurrió un error al eliminar el empleado.');
        }
    };
})();
