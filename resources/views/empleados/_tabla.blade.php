{{-- resources/views/empleados/_tabla.blade.php --}}

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
                    {{-- === TODO LO QUE YA TENÍAS EN LAS 4 COLUMNAS === --}}
                    {{-- Copia aquí las 4 <td> exactamente como en tu código original --}}
                    {{-- Columna empleado --}}
                    <td class="px-4 py-4 md:py-5 align-top">
                        <div class="flex flex-col gap-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900 text-sm md:text-base">
                                    {{ $nombreCompleto }}
                                </span>

                                {{-- Estado IMSS --}}
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

                    {{-- Columna fechas / estado --}}
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
                        {{-- AQUÍ deja exactamente tus botones Ver / Historial / Editar / Eliminar tal cual --}}
                        @include('empleados._acciones', ['empleado' => $empleado, 'nombreCompleto' => $nombreCompleto])
                        {{-- o pega aquí tu bloque completo de botones, como prefieras --}}
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
