<div class="inline-flex flex-wrap justify-end gap-2 md:gap-3">

    {{-- Ver --}}
    <button
        type="button"
        onclick="window.openShowEmpleadoModal(this)"
        class="empleados-btn-ghost"
        data-id="{{ $empleado->id }}"
        data-nombres="{{ $empleado->nombres }}"
        data-apellido-paterno="{{ $empleado->apellidoPaterno }}"
        data-apellido-materno="{{ $empleado->apellidoMaterno }}"
        data-numero-trabajador="{{ $empleado->numero_trabajador }}"
        data-estado-laboral="{{ $empleado->estado }}"
        data-estado-imss="{{ $empleado->estado_imss }}"
        data-fecha-ingreso="{{ optional($empleado->fecha_ingreso)->format('Y-m-d') }}"
        data-supervisor-nombre="{{ optional($empleado->supervisor)->nombre_completo }}"
        data-color="{{ $empleado->color }}"
        {{-- (agrega aquí todos los data-* que tienes en tu plantilla principal) --}}
    >
        Ver
    </button>

    {{-- Historial --}}
    <button
        type="button"
        onclick="window.openPeriodosEmpleadoModal(this)"
        class="empleados-btn-ghost"
        data-empleado-id="{{ $empleado->id }}"
        data-empleado-nombre="{{ $nombreCompleto }}"
    >
        Historial de periodos
    </button>

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
            data-patron-id="{{ $empleado->patron_id }}"
            data-sucursal-id="{{ $empleado->sucursal_id }}"
            data-departamento-id="{{ $empleado->departamento_id }}"
            data-supervisor-id="{{ $empleado->supervisor_id }}"
            data-curp="{{ $empleado->curp }}"
            data-rfc="{{ $empleado->rfc }}"
            data-color="{{ $empleado->color }}"
        >
            Editar
        </button>

        {{-- Eliminar --}}
        <button
            type="button"
            onclick="window.confirmDeleteEmpleado({{ $empleado->id }})"
            class="empleados-btn-eliminar"
        >
            Eliminar
        </button>

    @endif

</div>
