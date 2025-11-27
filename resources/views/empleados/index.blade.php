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

                {{-- Filtros en tiempo real (server-side) --}}
                <div class="empleados-filtros rounded-2xl bg-white/90 backdrop-blur shadow-lg border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex items-center justify-between mb-3 gap-2">
                        <h2 class="text-xs md:text-sm font-semibold tracking-wide text-slate-600 uppercase">
                            Filtros rápidos de empleados
                        </h2>
                        <span class="text-[11px] md:text-xs text-slate-400 hidden sm:inline-block">
                            Se aplican en tiempo real al listado, consultando toda la base de datos.
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
                                    value="{{ $search }}"
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
                                <option value="" {{ $estado_imss === null || $estado_imss === '' ? 'selected' : '' }}>Todos</option>
                                <option value="alta" {{ $estado_imss === 'alta' ? 'selected' : '' }}>Alta</option>
                                <option value="inactivo" {{ $estado_imss === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
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
                                    <option value="{{ $p->id }}" {{ (int)$patron_id === $p->id ? 'selected' : '' }}>
                                        {{ $p->nombre }}
                                    </option>
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
                                    <option value="{{ $s->id }}" {{ (int)$sucursal_id === $s->id ? 'selected' : '' }}>
                                        {{ $s->nombre }}
                                    </option>
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
                                    <option value="{{ $d->id }}" {{ (int)$departamento_id === $d->id ? 'selected' : '' }}>
                                        {{ $d->nombre }}
                                    </option>
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
                                    <option value="{{ $s->id }}" {{ (int)$supervisor_id === $s->id ? 'selected' : '' }}>
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
                                value="{{ $ingreso_desde }}"
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
                                value="{{ $ingreso_hasta }}"
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
                                value="{{ $imss_desde }}"
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
                                value="{{ $imss_hasta }}"
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

                {{-- Tabla / listado (wrapper para AJAX) --}}
                <div id="empleados-table-wrapper">
                    @include('empleados._tabla', ['empleados' => $empleados])
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
