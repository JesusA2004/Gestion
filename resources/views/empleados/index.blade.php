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
                            Gestión de empleados: alta, edición, baja y asignación a patrón, sucursal, departamento y supervisor.
                        </p>
                    </div>

                    <button
                        type="button"
                        onclick="openCreateEmpleadoModal()"
                        class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm md:text-base font-semibold text-white shadow-md shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Nuevo empleado
                    </button>
                </div>

                {{-- Filtros en tiempo real (tabla principal) --}}
                <div class="rounded-2xl bg-white/90 backdrop-blur shadow-sm border border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:items-end">
                        <div class="md:col-span-2">
                            <label for="empleado-search-text" class="block text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Buscar por nombre o número de trabajador
                            </label>
                            <div class="mt-1 relative">
                                <input
                                    id="empleado-search-text"
                                    type="text"
                                    placeholder="Escribe cualquier parte del nombre, apellidos o número de trabajador..."
                                    value="{{ $search }}"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 pr-9 text-sm md:text-base text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                                    <svg class="h-4 w-4 md:h-5 md:w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div>
                            <label for="empleado-filter-estado" class="block text-[11px] md:text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                Estado
                            </label>
                            <div class="mt-1">
                                <select
                                    id="empleado-filter-estado"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2.5 text-sm md:text-base text-slate-800 shadow-sm focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="alta" {{ ($estado ?? '') === 'alta' ? 'selected' : '' }}>Alta</option>
                                    <option value="baja" {{ ($estado ?? '') === 'baja' ? 'selected' : '' }}>Baja</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla --}}
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
                                    $searchData = strtolower($nombreCompleto . ' ' . $empleado->numero_trabajador);
                                @endphp
                                <tr
                                    class="empleado-row hover:bg-indigo-50/60 transition-colors"
                                    data-empleado-row
                                    data-search="{{ $searchData }}"
                                    data-estado="{{ $empleado->estado }}"
                                >
                                    {{-- Columna empleado --}}
                                    <td class="px-4 py-4 md:py-5 align-top">
                                        <div class="flex flex-col gap-1.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-semibold text-slate-900 text-sm md:text-base">
                                                    {{ $nombreCompleto }}
                                                </span>
                                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-medium
                                                    {{ $empleado->estado === 'alta' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-rose-50 text-rose-700 border border-rose-100' }}">
                                                    <span class="h-1.5 w-1.5 rounded-full {{ $empleado->estado === 'alta' ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                                    {{ ucfirst($empleado->estado) }}
                                                </span>
                                            </div>
                                            <p class="text-xs md:text-sm text-slate-600">
                                                No. trabajador:
                                                <span class="font-mono font-semibold">
                                                    {{ $empleado->numero_trabajador }}
                                                </span>
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
                                                <span class="font-semibold text-slate-800">Baja:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->fecha_baja)->format('d-m-Y') ?? '—' }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Creado:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->created_at)->format('d-m-y H:i') }}
                                                </span>
                                            </div>
                                            <div>
                                                <span class="font-semibold text-slate-800">Modificado:</span>
                                                <span class="ml-1">
                                                    {{ optional($empleado->updated_at)->format('d-m-y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-4 md:py-5 align-top text-right">
                                        <div class="inline-flex flex-wrap justify-end gap-2 md:gap-3">
                                            <button
                                                type="button"
                                                onclick="openEditEmpleadoModal(this)"
                                                data-id="{{ $empleado->id }}"
                                                data-nombres="{{ $empleado->nombres }}"
                                                data-apellido-paterno="{{ $empleado->apellidoPaterno }}"
                                                data-apellido-materno="{{ $empleado->apellidoMaterno }}"
                                                data-numero-trabajador="{{ $empleado->numero_trabajador }}"
                                                data-estado="{{ $empleado->estado }}"
                                                data-fecha-ingreso="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}"
                                                data-fecha-baja="{{ optional($empleado->fecha_baja)->format('Y-m-d') }}"
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
                                                data-cuenta-bancaria="{{ $empleado->cuenta_bancaria }}"
                                                data-tarjeta="{{ $empleado->tarjeta }}"
                                                data-clabe-interbancaria="{{ $empleado->clabe_interbancaria }}"
                                                data-banco="{{ $empleado->banco }}"
                                                data-sueldo-diario-bruto="{{ $empleado->sueldo_diario_bruto }}"
                                                data-sueldo-diario-neto="{{ $empleado->sueldo_diario_neto }}"
                                                data-salario-diario-imss="{{ $empleado->salario_diario_imss }}"
                                                data-sdi="{{ $empleado->sdi }}"
                                                data-empresa-facturar="{{ $empleado->empresa_facturar }}"
                                                data-total-guardias-factura="{{ $empleado->total_guardias_factura }}"
                                                data-importe-factura-mensual="{{ $empleado->importe_factura_mensual }}"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-amber-400/90 px-3.5 md:px-4 py-1.5 md:py-2 text-[11px] md:text-xs font-semibold text-white shadow-sm hover:bg-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M16.862 4.487l1.687-1.687a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                                                </svg>
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                onclick="confirmDeleteEmpleado({{ $empleado->id }})"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-rose-500/90 px-3.5 md:px-4 py-1.5 md:py-2 text-[11px] md:text-xs font-semibold text-white shadow-sm hover:bg-rose-600 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                            >
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M6 7h12M10 11v6m4-6v6M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2M6 7l1 11a2 2 0 002 2h6a2 2 0 002-2l1-11"/>
                                                </svg>
                                                Eliminar
                                            </button>
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
        @php
            $patronesLookup = $patrones->map(fn($p) => [
                'id'     => $p->id,
                'nombre' => $p->nombre,
            ])->values();

            $sucursalesLookup = $sucursales->map(fn($s) => [
                'id'     => $s->id,
                'nombre' => $s->nombre,
            ])->values();

            $departamentosLookup = $departamentos->map(fn($d) => [
                'id'     => $d->id,
                'nombre' => $d->nombre,
            ])->values();

            $supervisoresLookup = $supervisores->map(fn($s) => [
                'id'              => $s->id,
                'nombres'         => $s->nombres,
                'apellidoPaterno' => $s->apellidoPaterno,
                'apellidoMaterno' => $s->apellidoMaterno,
            ])->values();
        @endphp

        <script>
            window.EmpleadosConfig = {
                baseUrl: '{{ url('') }}',
                csrfToken: '{{ csrf_token() }}',
                lookups: {
                    patrones: @json($patronesLookup),
                    sucursales: @json($sucursalesLookup),
                    departamentos: @json($departamentosLookup),
                    supervisores: @json($supervisoresLookup),
                }
            };
        </script>
        <script src="{{ asset('js/empleados.js') }}"></script>
    @endpush
</x-app-layout>
