<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartamentoRequest;
use App\Models\Departamento;
use Illuminate\Http\Request;

class DepartamentoController extends Controller
{
    /**
     * Listado de departamentos.
     */
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));

        $departamentos = Departamento::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('departamentos.index', [
            'departamentos' => $departamentos,
            'search'        => $search,
        ]);
    }

    /**
     * No usamos las vistas create/show/edit: regresan al index.
     */
    public function create()
    {
        return redirect()->route('departamentos.index');
    }

    public function show(Departamento $departamento)
    {
        return redirect()->route('departamentos.index');
    }

    public function edit(Departamento $departamento)
    {
        return redirect()->route('departamentos.index');
    }

    /**
     * Guardar nuevo departamento (JSON para SweetAlert).
     */
    public function store(DepartamentoRequest $request)
    {
        $departamento = Departamento::create($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Departamento registrado correctamente.',
            'data'    => $departamento,
        ]);
    }

    /**
     * Actualizar departamento (JSON para SweetAlert).
     */
    public function update(DepartamentoRequest $request, Departamento $departamento)
    {
        $departamento->update($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Departamento actualizado correctamente.',
            'data'    => $departamento,
        ]);
    }

    /**
     * Eliminar departamento (JSON para SweetAlert).
     */
    public function destroy(Departamento $departamento)
    {
        $departamento->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Departamento eliminado correctamente.',
        ]);
    }
}
