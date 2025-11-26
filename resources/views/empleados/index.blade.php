{{-- resources/views/empleados/index.blade.php --}}
<x-app-layout>
    <div class="py-8">
        <div class="w-full px-3 sm:px-4 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Encabezado + botón --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 tracking-tight">
                            Empleados
                        </h1>
                        <p class="mt-2 text-sm md:text-base text-gray-500 max-w-xl">
                            Gestión integral de empleados: alta, edición, historial de periodos, filtros avanzados y asignación a patrón, sucursal,
                            departamento y supervisor.
                        </p>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <button
                            type="button"
                            onclick="window.openCreateEmpleadoModal()"
                            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm md:text-base font-semibold text-white shadow-md shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 empleados-btn-cta"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Nuevo empleado
                        </button>
                    @endif
                </div>

                {{-- Filtros en tiempo real --}}
                <div class="empleados-filtros rounded-2xl bg-white/90 backdrop-blur shadow-lg border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-3 gap-2">
                        <h2 class="text-xs md:text-sm font-semibold tracking-wide text-slate-600 uppercase">
                            Filtros rápidos de empleados
                        </h2>
                        <span class="text-[11px] md:text-xs text-slate-400 hidden sm:inline-block">
                            Se aplican en tiempo real al listado, sin recargar la página.
                        </span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 md:gap-4 md:items-end text-[13px] md:text-sm">
                        {{-- Buscador global --}}
                        <div class="lg:col-span-6">
                            <label for="empleado-search-text" class="empleados-label">
                                Buscar (nombre, número trabajador, CURP, RFC)
                            </label>
                            <div class="mt-1 relative">
                                <input
                                    id="empleado-search-text"
                                    type="text"
                                    placeholder="Escribe cualquier parte del nombre, apellidos, número de trabajador, CURP o RFC..."
                                    class="empleados-input pl-3 pr-9"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Estado IMSS --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-estado" class="empleados-label">
                                Estado IMSS
                            </label>
                            <select
                                id="empleado-filter-estado"
                                class="empleados-select"
                            >
                                <option value="">Todos</option>
                                <option value="alta">Alta</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>

                        {{-- Patrón --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-patron" class="empleados-label">
                                Patrón (empresa)
                            </label>
                            <select id="empleado-filter-patron" class="empleados-select">
                                <option value="">Todos</option>
                                @foreach($patrones as $p)
                                    <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Sucursal --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-sucursal" class="empleados-label">
                                Sucursal
                            </label>
                            <select id="empleado-filter-sucursal" class="empleados-select">
                                <option value="">Todas</option>
                                @foreach($sucursales as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Departamento --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-departamento" class="empleados-label">
                                Departamento
                            </label>
                            <select id="empleado-filter-departamento" class="empleados-select">
                                <option value="">Todos</option>
                                @foreach($departamentos as $d)
                                    <option value="{{ $d->id }}">{{ $d->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Supervisor --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-supervisor" class="empleados-label">
                                Supervisor
                            </label>
                            <select id="empleado-filter-supervisor" class="empleados-select">
                                <option value="">Todos</option>
                                @foreach($supervisores as $s)
                                    <option value="{{ $s->id }}">
                                        {{ $s->nombres }} {{ $s->apellidoPaterno }} {{ $s->apellidoMaterno }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Fecha ingreso desde --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-ingreso-desde" class="empleados-label">
                                Fecha ingreso desde
                            </label>
                            <input
                                id="empleado-filter-ingreso-desde"
                                type="date"
                                class="empleados-input"
                            >
                        </div>

                        {{-- Fecha ingreso hasta --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-ingreso-hasta" class="empleados-label">
                                Fecha ingreso hasta
                            </label>
                            <input
                                id="empleado-filter-ingreso-hasta"
                                type="date"
                                class="empleados-input"
                            >
                        </div>

                        {{-- Alta IMSS desde --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-imss-desde" class="empleados-label">
                                Alta IMSS desde
                            </label>
                            <input
                                id="empleado-filter-imss-desde"
                                type="date"
                                class="empleados-input"
                            >
                        </div>

                        {{-- Alta IMSS hasta --}}
                        <div class="lg:col-span-2">
                            <label for="empleado-filter-imss-hasta" class="empleados-label">
                                Alta IMSS hasta
                            </label>
                            <input
                                id="empleado-filter-imss-hasta"
                                type="date"
                                class="empleados-input"
                            >
                        </div>

                        {{-- Botón limpiar --}}
                        <div class="lg:col-span-2 flex items-end">
                            <button
                                type="button"
                                id="empleado-filter-clear"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-full border border-slate-200 bg-white/80 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm hover:bg-slate-50 hover:border-slate-300 focus:outline-none focus:ring-1 focus:ring-slate-300"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Limpiar filtros
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabla / listado --}}
                <div class="overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/60 border border-slate-100">
                    <table class="min-w-full divide-y divide-slate-200 text-[13px] md:text-sm">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-4 py-3 md:py-4 text-left text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Empleado
                                </th>
                                <th class="px-4 py-3 md:py-4 text-left text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Relación laboral
                                </th>
                                <th class="px-4 py-3 md:py-4 text-left text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Fechas / estado
                                </th>
                                <th class="px-4 py-3 md:py-4 text-right text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 bg-white"
                            data-empleados
                        >
                            @forelse($empleados as $empleado)
                                @php
                                    $nombreCompleto = $empleado->nombre_completo;
                                    $searchData = strtolower(
                                        $nombreCompleto . ' ' .
                                        $empleado->numero_trabajador . ' ' .
                                        $empleado->curp . ' ' .
                                        $empleado->rfc
                                    );
                                @endphp
                                <tr
                                    class="empleado-row hover:bg-indigo-50/60 transition-colors"
                                    data-empleado-row
                                    data-search="{{ $searchData }}"
                                    data-estado-imss="{{ $empleado->estado_imss }}"
                                    data-patron-id="{{ $empleado->patron_id }}"
                                    data-sucursal-id="{{ $empleado->sucursal_id }}"
                                    data-departamento-id="{{ $empleado->departamento_id }}"
                                    data-supervisor-id="{{ $empleado->supervisor_id }}"
                                    data-fecha-ingreso="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}"
                                    data-fecha-alta-imss="{{ optional($empleado->fecha_alta_imss)->format('Y-m-d') }}"
                                >
                                    {{-- Columna empleado --}}
                                    <td class="px-4 py-4 md:py-5 align-top">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-slate-900 text-sm md:text-base">
                                                    {{ $nombreCompleto }}
                                                </span>

                                                {{-- Estado IMSS: admin puede cambiar, colaborador solo ver --}}
                                                @if(auth()->user()->role === 'admin')
                                                    <button
                                                        type="button"
                                                        onclick="window.openToggleEstadoEmpleado({{ $empleado->id }}, '{{ $empleado->estado_imss }}')"
                                                        class="empleados-pill-estado {{ $empleado->estado_imss === 'alta' ? 'empleados-pill-estado-alta' : 'empleados-pill-estado-baja' }}"
                                                    >
                                                        <span class="empleados-pill-dot {{ $empleado->estado_imss === 'alta' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                                        {{ ucfirst($empleado->estado_imss) }}
                                                        <span class="text-[9px] opacity-80 ml-1">(cambiar)</span>
                                                    </button>
                                                @else
                                                    <span
                                                        class="empleados-pill-estado {{ $empleado->estado_imss === 'alta' ? 'empleados-pill-estado-alta' : 'empleados-pill-estado-baja' }}"
                                                    >
                                                        <span class="empleados-pill-dot {{ $empleado->estado_imss === 'alta' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                                        {{ ucfirst($empleado->estado_imss) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs md:text-sm text-slate-600">
                                                No. trabajador:
                                                <span class="font-mono font-semibold">{{ $empleado->numero_trabajador }}</span>
                                            </p>
                                            <p class="text-[11px] text-slate-400">
                                                CURP: {{ $empleado->curp }} · RFC: {{ $empleado->rfc }}
                                            </p>
                                        </div>
                                    </td>

                                    {{-- Columna relación laboral --}}
                                    <td class="px-4 py-4 md:py-5 align-top text-xs md:text-sm text-slate-700">
                                        <div class="space-y-1">
                                            <div>
                                                <span class="font-semibold text-slate-800">Patrón:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->patron)->nombre ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Sucursal:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->sucursal)->nombre ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Departamento:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->departamento)->nombre ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Supervisor:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->supervisor)->nombre_completo ?? '—' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Columna fechas --}}
                                    <td class="px-4 py-4 md:py-5 align-top text-xs md:text-sm text-slate-700">
                                        <div class="space-y-1.5">
                                            <div>
                                                <span class="font-semibold text-slate-800">Ingreso:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->fecha_ingreso)->format('d-m-Y') ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Alta IMSS:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->fecha_alta_imss)->format('d-m-Y') ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Reingresos:</span>
                                                <span class="ml-1">
                                                    {{ $empleado->numero_reingresos ?? 0 }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-slate-400 pt-1">
                                                Creado: {{ optional($empleado->created_at)->format('d-m-y H:i') }}
                                                · Modificado: {{ optional($empleado->updated_at)->format('d-m-y H:i') }}
                                            </p>
                                        </div>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-4 md:py-5 align-top text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2 md:gap-3">
                                            {{-- Ver (todos los roles) --}}
                                            <button
                                                type="button"
                                                onclick="window.openShowEmpleadoModal(this)"
                                                class="empleados-btn-ghost"
                                                data-id="{{ $empleado->id }}"
                                                data-nombres="{{ $empleado->nombres }}"
                                                data-apellido-paterno="{{ $empleado->apellidoPaterno }}"
                                                data-apellido-materno="{{ $empleado->apellidoMaterno }}"
                                                data-numero-trabajador="{{ $empleado->numero_trabajador }}"

                                                {{-- Estado donde labora (estado de la república) --}}
                                                data-estado-laboral="{{ $empleado->estado }}"

                                                {{-- Estado IMSS (alta / inactivo) --}}
                                                data-estado-imss="{{ $empleado->estado_imss }}"

                                                data-fecha-ingreso="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}"
                                                data-numero-reingresos="{{ $empleado->numero_reingresos ?? 0 }}"

                                                data-patron-id="{{ $empleado->patron_id }}"
                                                data-patron-nombre="{{ optional($empleado->patron)->nombre }}"

                                                data-sucursal-id="{{ $empleado->sucursal_id }}"
                                                data-sucursal-nombre="{{ optional($empleado->sucursal)->nombre }}"

                                                data-departamento-id="{{ $empleado->departamento_id }}"
                                                data-departamento-nombre="{{ optional($empleado->departamento)->nombre }}"

                                                data-supervisor-id="{{ $empleado->supervisor_id }}"
                                                data-supervisor-nombre="{{ optional($empleado->supervisor)->nombre_completo }}"

                                                data-numero-imss="{{ $empleado->numero_imss }}"
                                                data-registro-patronal="{{ $empleado->registro_patronal }}"
                                                data-codigo-postal="{{ $empleado->codigo_postal }}"
                                                data-fecha-alta-imss="{{ optional($empleado->fecha_alta_imss)->format('Y-m-d') }}"
                                                data-curp="{{ $empleado->curp }}"
                                                data-rfc="{{ $empleado->rfc }}"

                                                {{-- Bancarios --}}
                                                data-banco="{{ $empleado->banco }}"
                                                data-cuenta-bancaria="{{ $empleado->cuenta_bancaria }}"
                                                data-tarjeta="{{ $empleado->tarjeta }}"
                                                data-clabe="{{ $empleado->clabe_interbancaria }}"

                                                {{-- Facturación --}}
                                                data-empresa-facturar="{{ $empleado->empresa_facturar }}"
                                                data-importe-factura-mensual="{{ $empleado->importe_factura_mensual }}"

                                                {{-- SDI y color --}}
                                                data-sdi="{{ $empleado->sdi }}"
                                                data-color="{{ $empleado->color }}"
                                            >
                                                Ver
                                            </button>

                                            {{-- Historial de periodos (todos pueden ver) --}}
                                            <button
                                                type="button"
                                                onclick="window.openPeriodosEmpleadoModal(this)"
                                                class="empleados-btn-ghost"
                                                data-empleado-id="{{ $empleado->id }}"
                                                data-empleado-nombre="{{ $nombreCompleto }}"
                                            >
                                                Historial de periodos
                                            </button>

                                            {{-- Acciones solo para admin --}}
                                            @if(auth()->user()->role === 'admin')
                                                {{-- Editar --}}
                                                <button
                                                    type="button"
                                                    onclick="window.openEditEmpleadoModal(this)"
                                                    class="empleados-btn-editar"
                                                    data-id="{{ $empleado->id }}"
                                                    data-nombres="{{ $empleado->nombres }}"
                                                    data-apellido-paterno="{{ $empleado->apellidoPaterno }}"
                                                    data-apellido-materno="{{ $empleado->apellidoMaterno }}"
                                                    data-numero-trabajador="{{ $empleado->numero_trabajador }}"
                                                    data-estado="{{ $empleado->estado_imss }}"
                                                    data-fecha-ingreso="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}"
                                                    data-fecha-baja=""
                                                    data-patron-id="{{ $empleado->patron_id }}"
                                                    data-sucursal-id="{{ $empleado->sucursal_id }}"
                                                    data-departamento-id="{{ $empleado->departamento_id }}"
                                                    data-supervisor-id="{{ $empleado->supervisor_id }}"
                                                    data-numero-imss="{{ $empleado->numero_imss }}"
                                                    data-registro-patronal="{{ $empleado->registro_patronal }}"
                                                    data-codigo-postal="{{ $empleado->codigo_postal }}"
                                                    data-fecha-alta-imss="{{ optional($empleado->fecha_alta_imss)->format('Y-m-d') }}"
                                                    data-curp="{{ $empleado->curp }}"
                                                    data-rfc="{{ $empleado->rfc }}"
                                                    data-banco="{{ $empleado->banco }}"
                                                    data-cuenta-bancaria="{{ $empleado->cuenta_bancaria }}"
                                                    data-tarjeta="{{ $empleado->tarjeta }}"
                                                    data-clabe="{{ $empleado->clabe_interbancaria }}"
                                                    data-sueldo-bruto=""
                                                    data-sueldo-neto=""
                                                    data-salario-imss=""
                                                    data-sdi="{{ $empleado->sdi }}"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M16.862 4.487l1.687-1.687a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                                    </svg>
                                                    Editar
                                                </button>

                                                {{-- Eliminar --}}
                                                <button
                                                    type="button"
                                                    onclick="window.confirmDeleteEmpleado({{ $empleado->id }})"
                                                    class="empleados-btn-eliminar"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M6 7h12M10 11v6m4-6v6M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M6 7l1 11a2 2 0 002 2h6a2 2 0 002-2l1-11"/>
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm md:text-base text-slate-500">
                                        No hay empleados registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/60">
                        {{ $empleados->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="{{ asset('css/empleados.css') }}" rel="stylesheet">
    @endpush

    @push('scripts')
        <script>
            window.EmpleadosConfig = {
                baseUrl: '{{ url('') }}',
                csrfToken: '{{ csrf_token() }}',
                canManage: {{ auth()->user()->role === 'admin' ? 'true' : 'false' }},
                lookups: {
                    patrones: @json($patronesList),
                    sucursales: @json($sucursalesList),
                    departamentos: @json($departamentosList),
                    supervisores: @json($supervisoresList),
                }
            };
        </script>
        <script src="{{ asset('js/empleados.js') }}"></script>
    @endpush

</x-app-layout>
