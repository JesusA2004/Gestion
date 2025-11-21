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
                        ->orWhere('numero_trabajador', 'like', "%{$search}%");
                });
            })
            ->when($estado !== null && $estado !== '', function ($q) use ($estado) {
                $q->where('estado', $estado);
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        // Lookups para selects con buscador
        $patrones = Patron::orderBy('nombre')->get(['id', 'nombre']);
        $sucursales = Sucursal::orderBy('nombre')->get(['id', 'nombre']);
        $departamentos = Departamento::orderBy('nombre')->get(['id', 'nombre']);
        $supervisores = Supervisor::orderBy('nombres')
            ->orderBy('apellidoPaterno')
            ->get(['id', 'nombres', 'apellidoPaterno', 'apellidoMaterno']);

        return view('empleados.index', [
            'empleados'     => $empleados,
            'search'        => $search,
            'estado'        => $estado,
            'patrones'      => $patrones,
            'sucursales'    => $sucursales,
            'departamentos' => $departamentos,
            'supervisores'  => $supervisores,
        ]);
    }

    // create/show/edit no se usan (todo es en index con SweetAlert)
    public function create() { return redirect()->route('empleados.index'); }
    public function show(Empleado $empleado) { return redirect()->route('empleados.index'); }
    public function edit(Empleado $empleado) { return redirect()->route('empleados.index'); }

    public function store(EmpleadoRequest $request)
    {
        $empleado = Empleado::create($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado registrado correctamente.',
            'data'    => new EmpleadoResource($empleado->load(['patron', 'sucursal', 'departamento', 'supervisor'])),
        ]);
    }

    public function update(EmpleadoRequest $request, Empleado $empleado)
    {
        $empleado->update($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado actualizado correctamente.',
            'data'    => new EmpleadoResource($empleado->fresh()->load(['patron', 'sucursal', 'departamento', 'supervisor'])),
        ]);
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Empleado eliminado correctamente.',
        ]);
    }
}
