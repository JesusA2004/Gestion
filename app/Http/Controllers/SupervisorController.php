<?php

// app/Http/Controllers/SupervisorController.php
namespace App\Http\Controllers;

use App\Http\Requests\SupervisorRequest;
use App\Models\Supervisor;
use Illuminate\Http\Request;

class SupervisorController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->get('q', ''));

        $supervisors = Supervisor::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nombres', 'like', "%{$search}%")
                  ->orWhere('apellidoPaterno', 'like', "%{$search}%")
                  ->orWhere('apellidoMaterno', 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        return view('supervisors.index', [
            'supervisors' => $supervisors,
            'search'       => $search,
        ]);
    }

    public function store(SupervisorRequest $request)
    {
        $supervisor = Supervisor::create($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Supervisor registrado correctamente.',
            'data'    => $supervisor,
        ]);
    }

    public function update(SupervisorRequest $request, Supervisor $supervisor)
    {
        $supervisor->update($request->validated());

        return response()->json([
            'ok'      => true,
            'message' => 'Supervisor actualizado correctamente.',
            'data'    => $supervisor,
        ]);
    }

    public function destroy(Supervisor $supervisor)
    {
        $supervisor->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Supervisor eliminado correctamente.',
        ]);
    }

    public function create() { return redirect()->route('supervisors.index'); }
    public function show()   { return redirect()->route('supervisors.index'); }
    public function edit()   { return redirect()->route('supervisors.index'); }
}

