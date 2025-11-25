<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Patron;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\EmpleadoPeriodo;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // === Métricas generales ===
        $totalEmpleados     = Empleado::count();
        $empleadosActivos   = Empleado::where('estado_imss', 'alta')->count();
        $empleadosInactivos = $totalEmpleados - $empleadosActivos;

        $totalPatrones      = Patron::count();
        $totalSucursales    = Sucursal::count();
        $totalDepartamentos = Departamento::count();

        // === Top 5 patrones por empleados ===
        $topPatrones = Patron::withCount('empleados')
            ->orderByDesc('empleados_count')
            ->take(5)
            ->get(['id', 'nombre']);

        // === Top 5 sucursales por empleados ===
        $topSucursales = Sucursal::withCount('empleados')
            ->orderByDesc('empleados_count')
            ->take(5)
            ->get(['id', 'nombre']);

        // === Altas / reingresos últimos 6 meses ===
        $inicio = Carbon::now()->subMonths(5)->startOfMonth();

        $periodos = EmpleadoPeriodo::selectRaw('DATE_FORMAT(fecha_alta, "%Y-%m") as ym, COUNT(*) as total')
            ->where('fecha_alta', '>=', $inicio)
            ->whereIn('tipo_alta', ['alta', 'reingreso'])
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Normalizar últimos 6 meses para el gráfico
        $labelsMeses = [];
        $altasPorMes = [];
        $cursor = $inicio->copy();

        $mapPeriodos = $periodos->pluck('total', 'ym'); // ['2025-07' => 10, ...]

        for ($i = 0; $i < 6; $i++) {
            $ym = $cursor->format('Y-m');
            $labelsMeses[] = $cursor->translatedFormat('M Y'); // Ej: "Nov 2025"
            $altasPorMes[] = (int) ($mapPeriodos[$ym] ?? 0);
            $cursor->addMonth();
        }

        // === Datos empaquetados para Blade / JS ===
        $dashboardData = [
            'cards' => [
                'totalEmpleados'     => $totalEmpleados,
                'empleadosActivos'   => $empleadosActivos,
                'empleadosInactivos' => $empleadosInactivos,
                'totalPatrones'      => $totalPatrones,
                'totalSucursales'    => $totalSucursales,
                'totalDepartamentos' => $totalDepartamentos,
            ],
            'charts' => [
                'empleadosEstado' => [
                    'labels' => ['Alta IMSS', 'Inactivos'],
                    'data'   => [$empleadosActivos, $empleadosInactivos],
                ],
                'altasMensuales' => [
                    'labels' => $labelsMeses,
                    'data'   => $altasPorMes,
                ],
            ],
            'tables' => [
                'topPatrones'   => $topPatrones,
                'topSucursales' => $topSucursales,
            ],
        ];

        return view('dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }
}
