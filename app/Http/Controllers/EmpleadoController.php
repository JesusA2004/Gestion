<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpleadoRequest;
use App\Http\Resources\EmpleadoResource;
use App\Models\Empleado;
use App\Models\Patron;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\Supervisor;
use Illuminate\Http\Request;

class EmpleadoController extends Controller
{
    /**
     * Listar empleados con filtros y paginación (server-side).
     * Soporta:
     *  - search / q        : texto global (nombre, apellidos, num. trabajador, CURP, RFC)
     *  - estado_imss      : alta | inactivo
     *  - patron_id        : id patrón
     *  - sucursal_id      : id sucursal
     *  - departamento_id  : id departamento
     *  - supervisor_id    : id supervisor
     *  - ingreso_desde / ingreso_hasta : fecha_ingreso (Y-m-d)
     *  - imss_desde / imss_hasta       : fecha_alta_imss (Y-m-d)
     *
     * Si la petición es AJAX, devuelve JSON con el HTML de la tabla parcial.
     */
    public function index(Request $request)
    {
        // Aceptamos tanto ?search= como ?q= por compatibilidad
        $search = trim($request->get('search', $request->get('q', '')));

        $estadoImss     = $request->get('estado_imss');
        $patronId       = $request->get('patron_id');
        $sucursalId     = $request->get('sucursal_id');
        $departamentoId = $request->get('departamento_id');
        $supervisorId   = $request->get('supervisor_id');

        $ingresoDesde = $request->get('ingreso_desde');
        $ingresoHasta = $request->get('ingreso_hasta');
        $imssDesde    = $request->get('imss_desde');
        $imssHasta    = $request->get('imss_hasta');

        $query = Empleado::with(['patron', 'sucursal', 'departamento', 'supervisor']);

        // === Filtro texto global (nombre, apellidos, num. trabajador, CURP, RFC) ===
        if ($search !== '') {
            $like = '%' . $search . '%';

            $query->where(function ($q) use ($like) {
                $q->where('nombres', 'like', $like)
                    ->orWhere('apellidoPaterno', 'like', $like)
                    ->orWhere('apellidoMaterno', 'like', $like)
                    ->orWhere('numero_trabajador', 'like', $like)
                    ->orWhere('curp', 'like', $like)
                    ->orWhere('rfc', 'like', $like);
            });
        }

        // === Estado IMSS ===
        if ($estadoImss !== null && $estadoImss !== '') {
            $query->where('estado_imss', $estadoImss);
        }

        // === Patrón / sucursal / departamento / supervisor ===
        if ($patronId) {
            $query->where('patron_id', $patronId);
        }

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        if ($departamentoId) {
            $query->where('departamento_id', $departamentoId);
        }

        if ($supervisorId) {
            $query->where('supervisor_id', $supervisorId);
        }

        // === Rango de fechas: ingreso ===
        if ($ingresoDesde) {
            $query->whereDate('fecha_ingreso', '>=', $ingresoDesde);
        }
        if ($ingresoHasta) {
            $query->whereDate('fecha_ingreso', '<=', $ingresoHasta);
        }

        // === Rango de fechas: alta IMSS ===
        if ($imssDesde) {
            $query->whereDate('fecha_alta_imss', '>=', $imssDesde);
        }
        if ($imssHasta) {
            $query->whereDate('fecha_alta_imss', '<=', $imssHasta);
        }

        // Orden estándar por nombre
        $empleados = $query
            ->orderBy('nombres')
            ->orderBy('apellidoPaterno')
            ->orderBy('apellidoMaterno')
            ->paginate(15)
            ->withQueryString();

        // Lookups para selects (vista + JS)
        $patrones      = Patron::orderBy('nombre')->get(['id', 'nombre']);
        $sucursales    = Sucursal::orderBy('nombre')->get(['id', 'nombre']);
        $departamentos = Departamento::orderBy('nombre')->get(['id', 'nombre']);
        $supervisores  = Supervisor::orderBy('nombres')
            ->orderBy('apellidoPaterno')
            ->get(['id', 'nombres', 'apellidoPaterno', 'apellidoMaterno']);

        // Versiones "planas" para JSON (EmpleadosConfig.lookups)
        $patronesList = $patrones->map(function ($p) {
            return [
                'id'     => $p->id,
                'nombre' => $p->nombre,
            ];
        })->values();

        $sucursalesList = $sucursales->map(function ($s) {
            return [
                'id'     => $s->id,
                'nombre' => $s->nombre,
            ];
        })->values();

        $departamentosList = $departamentos->map(function ($d) {
            return [
                'id'     => $d->id,
                'nombre' => $d->nombre,
            ];
        })->values();

        $supervisoresList = $supervisores->map(function ($s) {
            return [
                'id'              => $s->id,
                'nombres'         => $s->nombres,
                'apellidoPaterno' => $s->apellidoPaterno,
                'apellidoMaterno' => $s->apellidoMaterno,
            ];
        })->values();

        // === Respuesta AJAX: solo HTML de la tabla para filtros/paginación en tiempo real ===
        if ($request->ajax()) {
            $html = view('empleados._tabla', compact('empleados'))->render();

            return response()->json([
                'html' => $html,
            ]);
        }

        // === Respuesta normal (vista completa) ===
        return view('empleados.index', [
            'empleados'         => $empleados,
            'search'            => $search,
            'estado_imss'       => $estadoImss,
            'patron_id'         => $patronId,
            'sucursal_id'       => $sucursalId,
            'departamento_id'   => $departamentoId,
            'supervisor_id'     => $supervisorId,
            'ingreso_desde'     => $ingresoDesde,
            'ingreso_hasta'     => $ingresoHasta,
            'imss_desde'        => $imssDesde,
            'imss_hasta'        => $imssHasta,
            'patrones'          => $patrones,
            'sucursales'        => $sucursales,
            'departamentos'     => $departamentos,
            'supervisores'      => $supervisores,
            'patronesList'      => $patronesList,
            'sucursalesList'    => $sucursalesList,
            'departamentosList' => $departamentosList,
            'supervisoresList'  => $supervisoresList,
        ]);
    }

    /**
     * Cambiar estado IMSS del empleado (alta / inactivo).
     * Endpoint esperado por JS: PATCH /empleados/{empleado}/estado
     */
    public function cambiarEstado(Request $request, Empleado $empleado)
    {
        $request->validate([
            'estado_imss' => 'required|in:alta,inactivo',
        ]);

        $empleado->estado_imss = $request->estado_imss;
        $empleado->save();

        return response()->json([
            'ok'          => true,
            'message'     => 'Estado IMSS actualizado correctamente.',
            'estado_imss' => $empleado->estado_imss,
        ]);
    }

    public function create()
    {
        // Se gestiona todo desde modales en la vista de índice
        return redirect()->route('empleados.index');
    }

    public function show(Empleado $empleado)
    {
        // También se ve en modal, no en página dedicada
        return redirect()->route('empleados.index');
    }

    public function edit(Empleado $empleado)
    {
        // La edición es vía modal + AJAX
        return redirect()->route('empleados.index');
    }

    /**
     * Almacenar nuevo empleado (AJAX).
     * Endpoint: POST /empleados
     */
    public function store(EmpleadoRequest $request)
    {
        $empleado = Empleado::create($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado registrado correctamente.',
            'data'    => new EmpleadoResource(
                $empleado->load(['patron', 'sucursal', 'departamento', 'supervisor', 'periodos'])
            ),
        ]);
    }

    /**
     * Actualizar empleado (AJAX).
     * Endpoint: PUT /empleados/{empleado}
     */
    public function update(EmpleadoRequest $request, Empleado $empleado)
    {
        $empleado->update($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado actualizado correctamente.',
            'data'    => new EmpleadoResource(
                $empleado->fresh()->load(['patron', 'sucursal', 'departamento', 'supervisor', 'periodos'])
            ),
        ]);
    }

    /**
     * Eliminar empleado (AJAX).
     * Endpoint: DELETE /empleados/{empleado}
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado eliminado correctamente.',
        ]);
    }
}
