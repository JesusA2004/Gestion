<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmpleadoPeriodoRequest;
use App\Http\Resources\EmpleadoPeriodoResource;
use App\Models\Empleado;
use App\Models\EmpleadoPeriodo;
use Illuminate\Http\Request;

class EmpleadoPeriodoController extends Controller
{
    /**
     * Listar periodos de un empleado
     */
    public function index(Empleado $empleado)
    {
        $periodos = $empleado->periodos()
            ->orderBy('fecha_alta', 'desc')
            ->get();

        return EmpleadoPeriodoResource::collection($periodos);
    }

    /**
     * Crear un nuevo periodo (alta / reingreso / baja)
     */
    public function store(EmpleadoPeriodoRequest $request)
    {
        $data = $request->validated();

        $periodo = EmpleadoPeriodo::create($data);

        // Incrementar número de reingresos si corresponde
        if ($data['tipo_alta'] === 'reingreso') {
            $empleado = Empleado::find($data['empleado_id']);
            $empleado->increment('numero_reingresos');
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Periodo registrado correctamente.',
            'data'    => new EmpleadoPeriodoResource(
                $periodo->load('empleado')
            ),
        ]);
    }

    /**
     * Mostrar un periodo
     */
    public function show(EmpleadoPeriodo $periodo)
    {
        return new EmpleadoPeriodoResource($periodo->load('empleado'));
    }

    /**
     * Actualizar un periodo existente
     */
    public function update(EmpleadoPeriodoRequest $request, EmpleadoPeriodo $periodo)
    {
        $oldTipo = $periodo->tipo_alta;

        $periodo->update($request->validated());

        // Ajustar reingresos si el tipo cambió
        if ($oldTipo !== $periodo->tipo_alta) {
            $empleado = $periodo->empleado;

            // Si antes era reingreso y ya no → restar
            if ($oldTipo === 'reingreso') {
                $empleado->decrement('numero_reingresos');
            }

            // Si ahora es reingreso → sumar
            if ($periodo->tipo_alta === 'reingreso') {
                $empleado->increment('numero_reingresos');
            }
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Periodo actualizado correctamente.',
            'data'    => new EmpleadoPeriodoResource(
                $periodo->fresh()->load('empleado')
            ),
        ]);
    }

    /**
     * Eliminar un periodo
     */
    public function destroy(EmpleadoPeriodo $periodo)
    {
        $empleado = $periodo->empleado;
        $tipo     = $periodo->tipo_alta;

        $periodo->delete();

        // Ajustar reingresos al borrar un reingreso
        if ($tipo === 'reingreso') {
            $empleado->decrement('numero_reingresos');
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Periodo eliminado correctamente.',
        ]);
    }
    
}
