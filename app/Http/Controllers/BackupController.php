<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    /**
     * Vista de respaldo y restauración
     */
    public function index()
    {
        return view('backup.index');
    }

    /**
     * Descargar respaldo actual de la base de datos
     */
    public function download()
    {
        // ⚠️ Este archivo debe existir o generarlo en el momento.
        // Aquí asumimos que YA tienes restauracion_empleados.sql en storage/app
        $path = storage_path('app/restauracion_empleados.sql');

        if (!file_exists($path)) {
            return back()->withErrors([
                'error' => 'No existe el archivo de respaldo restauracion_empleados.sql'
            ]);
        }

        return response()->download($path, 'respaldo_gestion.sql');
    }

    /**
     * Restaurar la base desde un archivo .sql
     */
    public function restore(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:sql,txt'],
        ]);

        $sql = file_get_contents($request->file('archivo')->getRealPath());

        try {
            DB::beginTransaction();

            $comandos = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($cmd) => $cmd !== ''
            );

            foreach ($comandos as $cmd) {
                if (str_starts_with(trim($cmd), '--')) continue;
                if (str_starts_with(trim($cmd), '/*')) continue;

                DB::statement($cmd);
            }

            DB::commit();

            return back()->with('status', 'Restauración realizada correctamente.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->withErrors([
                'archivo' => 'Error al restaurar: ' . $e->getMessage()
            ]);
        }
    }
}
