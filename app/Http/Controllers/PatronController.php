<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatronRequest;
use App\Models\Patron;
use Illuminate\Http\Request;

class PatronController extends Controller
{
    /**
     * Listado de patrones (empresas).
     */
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));

        $patrones = Patron::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%");
            })
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('patrons.index', compact('patrones', 'search'));
    }

    /**
     * Crear patrón (se gestiona desde index, redirigimos).
     */
    public function create()
    {
        return redirect()->route('patrons.index');
    }

    /**
     * Guardar nuevo patrón.
     */
    public function store(PatronRequest $request)
    {
        Patron::create($request->validated());

        return redirect()
            ->route('patrons.index')
            ->with('status', 'Patrón registrado correctamente.');
    }

    /**
     * Mostrar patrón (no se usa, regresamos al listado).
     */
    public function show(Patron $patron)
    {
        return redirect()->route('patrons.index');
    }

    /**
     * Editar patrón (se hace inline en index, redirigimos).
     */
    public function edit(Patron $patron)
    {
        return redirect()->route('patrons.index');
    }

    /**
     * Actualizar patrón.
     */
    public function update(PatronRequest $request, Patron $patron)
    {
        $patron->update($request->validated());

        return redirect()
            ->route('patrons.index')
            ->with('status', 'Patrón actualizado correctamente.');
    }

    /**
     * Eliminar patrón.
     */
    public function destroy(Patron $patron)
    {
        $patron->delete();

        return redirect()
            ->route('patrons.index')
            ->with('status', 'Patrón eliminado correctamente.');
    }
}
