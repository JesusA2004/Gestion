<?php

namespace App\Http\Controllers;

use App\Http\Requests\SucursalRequest;
use App\Http\Resources\SucursalResource;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class SucursalController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));

        $sucursales = Sucursal::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('direccion', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('sucursals.index', [
            'sucursales' => $sucursales,
            'search'     => $search,
        ]);
    }

    public function store(SucursalRequest $request)
    {
        $data = $request->validated();

        // Normaliza boolean
        $data['activa'] = isset($data['activa'])
            ? (bool) $data['activa']
            : true;

        $sucursal = Sucursal::create($data);

        return response()->json([
            'ok'      => true,
            'message' => 'Plaza registrada correctamente.',
            'data'    => new SucursalResource($sucursal),
        ]);
    }

    public function update(SucursalRequest $request, Sucursal $sucursal)
    {
        $data = $request->validated();

        if (isset($data['activa'])) {
            $data['activa'] = (bool) $data['activa'];
        }

        $sucursal->update($data);

        return response()->json([
            'ok'      => true,
            'message' => 'Plaza actualizada correctamente.',
            'data'    => new SucursalResource($sucursal),
        ]);
    }

    public function destroy(Sucursal $sucursal)
    {
        $sucursal->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Plaza eliminada correctamente.',
        ]);
    }

    public function create()
    {
        return redirect()->route('sucursals.index');
    }

    public function show()
    {
        return redirect()->route('sucursals.index');
    }

    public function edit()
    {
        return redirect()->route('sucursals.index');
    }
}
