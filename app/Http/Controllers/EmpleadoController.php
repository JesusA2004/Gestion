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

    // Listar empleados con filtros y paginación
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));
        $estado = $request->get('estado');

        $empleados = Empleado::with(['patron', 'sucursal', 'departamento', 'supervisor'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidoPaterno', 'like', "%{$search}%")
                        ->orWhere('apellidoMaterno', 'like', "%{$search}%")
                        ->orWhere('numero_trabajador', 'like', "%{$search}%")
                        ->orWhere('curp', 'like', "%{$search}%")
                        ->orWhere('rfc', 'like', "%{$search}%");
                });
            })
            ->when($estado !== null && $estado !== '', function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Lookups para selects
        $patrones      = Patron::orderBy('nombre')->get(['id', 'nombre']);
        $sucursales    = Sucursal::orderBy('nombre')->get(['id', 'nombre']);
        $departamentos = Departamento::orderBy('nombre')->get(['id', 'nombre']);
        $supervisores  = Supervisor::orderBy('nombres')
            ->orderBy('apellidoPaterno')
            ->get(['id', 'nombres', 'apellidoPaterno', 'apellidoMaterno']);

        // Versiones "planas" para JSON (sin closures en Blade)
        $patronesList = $patrones->map(function ($p) {
            return ['id' => $p->id, 'nombre' => $p->nombre];
        })->values();

        $sucursalesList = $sucursales->map(function ($s) {
            return ['id' => $s->id, 'nombre' => $s->nombre];
        })->values();

        $departamentosList = $departamentos->map(function ($d) {
            return ['id' => $d->id, 'nombre' => $d->nombre];
        })->values();

        $supervisoresList = $supervisores->map(function ($s) {
            return [
                'id'              => $s->id,
                'nombres'         => $s->nombres,
                'apellidoPaterno' => $s->apellidoPaterno,
                'apellidoMaterno' => $s->apellidoMaterno,
            ];
        })->values();

        return view('empleados.index', [
            'empleados'        => $empleados,
            'search'           => $search,
            'estado'           => $estado,
            'patrones'         => $patrones,
            'sucursales'       => $sucursales,
            'departamentos'    => $departamentos,
            'supervisores'     => $supervisores,
            // nuevos:
            'patronesList'     => $patronesList,
            'sucursalesList'   => $sucursalesList,
            'departamentosList'=> $departamentosList,
            'supervisoresList' => $supervisoresList,
        ]);
    }

    public function cambiarEstado(Request $request, Empleado $empleado)
    {
        $request->validate([
            'estado' => 'required|in:alta,baja'
        ]);

        $empleado->estado = $request->estado;
        $empleado->save();

        return response()->json([
            'ok' => true,
            'message' => 'Estado actualizado correctamente.',
            'estado' => $empleado->estado
        ]);
    }

    public function create() { return redirect()->route('empleados.index'); }
    public function show(Empleado $empleado) { return redirect()->route('empleados.index'); }
    public function edit(Empleado $empleado) { return redirect()->route('empleados.index'); }

    // Almacenar nuevo empleado
    public function store(EmpleadoRequest $request)
    {
        $empleado = Empleado::create($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado registrado correctamente.',
            'data'    => new EmpleadoResource($empleado->load(['patron', 'sucursal', 'departamento', 'supervisor'])),
        ]);
    }

    // Actualizar empleado
    public function update(EmpleadoRequest $request, Empleado $empleado)
    {
        $empleado->update($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado actualizado correctamente.',
            'data'    => new EmpleadoResource($empleado->fresh()->load(['patron', 'sucursal', 'departamento', 'supervisor'])),
        ]);
    }

    // Eliminar empleado
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado eliminado correctamente.',
        ]);
    }

}
