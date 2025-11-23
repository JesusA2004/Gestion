<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Patron;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        // Datos agregados para las gráficas / tablas
        $reportData = $this->buildReportData($request);

        // Catálogos para filtros (solo para la vista Blade)
        $patrones      = Patron::orderBy('nombre')->get();
        $sucursales    = Sucursal::orderBy('nombre')->get();
        $departamentos = Departamento::orderBy('nombre')->get();
        $supervisores  = Supervisor::orderBy('nombres')->get();
        $empleadosList = Empleado::orderBy('nombres')
            ->orderBy('apellidoPaterno')
            ->get(['id', 'nombres', 'apellidoPaterno', 'apellidoMaterno']);

        if ($request->wantsJson()) {
            // Respuesta para AJAX (JS en tiempo real)
            return response()->json($reportData);
        }

        return view('reportes.index', [
            'patrones'       => $patrones,
            'sucursales'     => $sucursales,
            'departamentos'  => $departamentos,
            'supervisores'   => $supervisores,
            'empleadosList'  => $empleadosList,
            'filters'        => $request->all(),
            'metrics'        => $reportData['metrics'],
            'reportes'       => $reportData['reportes'],
        ]);
    }

    /**
     * Exportar a Excel el reporte seleccionado (usa mismos filtros que la vista).
     */
    public function export(Request $request)
    {
        $tipo = $request->input('tipo_reporte', 'guardias_estado');

        // Reutilizamos la misma lógica que la vista
        $reportData = $this->buildReportData($request);
        $reportes   = $reportData['reportes'] ?? [];

        $rawDataset = $reportes[$tipo] ?? [];

        // Normalizar dataset a filas simples (para el Excel)
        $rows = [];

        if ($tipo === 'guardias_estado' || $tipo === 'guardias_supervisor') {
            // Cada fila viene como ['label' => ..., 'total' => ...]
            foreach ($rawDataset as $row) {
                $rows[] = [
                    'label' => $row['label'] ?? '',
                    'total' => $row['total'] ?? 0,
                ];
            }
        } elseif ($tipo === 'guardias_imss') {
            // Cada fila viene como ['empresa','tipo','total','porcentaje']
            foreach ($rawDataset as $row) {
                $rows[] = [
                    'empresa'    => $row['empresa']    ?? '',
                    'tipo'       => $row['tipo']       ?? '',
                    'total'      => $row['total']      ?? 0,
                    'porcentaje' => $row['porcentaje'] ?? 0,
                ];
            }
        } else {
            // Fallback por si se envía un tipo raro
            foreach ($rawDataset as $row) {
                $rows[] = (array) $row;
            }
        }

        $fileName = 'reporte_' . $tipo . '_' . now()->format('Ymd_His') . '.xlsx';

        // Clase anónima válida en Laravel-Excel moderno
        $export = new class($rows, $tipo) implements FromCollection, WithHeadings {

            public function __construct(
                protected array $rows,
                protected string $tipo
            ) {}

            public function collection()
            {
                // Convertir array normal → colección
                return collect($this->rows);
            }

            public function headings(): array
            {
                return match ($this->tipo) {
                    'guardias_estado'     => ['Estado', 'Total'],
                    'guardias_supervisor' => ['Supervisor', 'Total'],
                    'guardias_imss'       => ['Empresa', 'Tipo', 'Total', 'Porcentaje'],
                    default               => ['Columna 1', 'Columna 2'],
                };
            }
        };

        return Excel::download($export, $fileName);
    }

    /**
     * Construye todos los datos agregados de los 3 reportes en base a los filtros.
     */
    protected function buildReportData(Request $request): array
    {
        $query = Empleado::with([
            'patron',
            'sucursal',
            'departamento',
            'supervisor',
        ]);

        $this->applyFilters($query, $request);

        $empleados = $query->get();

        // ========== Guardias x Estado (usamos nombre de sucursal como "estado") ==========
        $guardiasEstado = $empleados
            ->groupBy(function ($emp) {
                return optional($emp->sucursal)->nombre ?: 'Sin plaza';
            })
            ->map(function ($group, $label) {
                return [
                    'label' => $label,
                    'total' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->values();

        // ========== Guardias x Supervisor ==========
        $guardiasSupervisor = $empleados
            ->groupBy(function ($emp) {
                $sup = $emp->supervisor;
                if (!$sup) {
                    return 'Sin supervisor';
                }
                return trim($sup->nombres . ' ' . $sup->apellidoPaterno . ' ' . $sup->apellidoMaterno);
            })
            ->map(function ($group, $label) {
                return [
                    'label' => $label,
                    'total' => $group->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->values();

        // ========== Guardias CON IMSS / SIN IMSS por empresa ==========
        $guardiasImss = $empleados
            ->groupBy('patron_id')
            ->flatMap(function ($group, $patronId) {
                $empresa      = optional($group->first()->patron)->nombre ?: 'Sin empresa';
                $totalEmpresa = $group->count();

                $altaCount    = $group->where('tiene_imss', 1)->count();
                $sinImssCount = $group->where('tiene_imss', 0)->count();

                $rows = [];

                if ($altaCount > 0) {
                    $rows[] = [
                        'empresa'    => $empresa,
                        'tipo'       => 'ALTA',
                        'total'      => $altaCount,
                        'porcentaje' => $totalEmpresa ? round($altaCount * 100 / $totalEmpresa, 2) : 0,
                    ];
                }

                if ($sinImssCount > 0) {
                    $rows[] = [
                        'empresa'    => $empresa,
                        'tipo'       => 'SIN IMSS',
                        'total'      => $sinImssCount,
                        'porcentaje' => $totalEmpresa ? round($sinImssCount * 100 / $totalEmpresa, 2) : 0,
                    ];
                }

                return $rows;
            })
            ->values();

        // Métricas generales
        $metrics = [
            'total_guardias'     => $empleados->count(),
            'total_plazas'       => $guardiasEstado->count(),
            'total_supervisores' => $guardiasSupervisor->count(),
        ];

        return [
            'metrics' => $metrics,
            'reportes' => [
                'guardias_estado'     => $guardiasEstado->values()->all(),
                'guardias_supervisor' => $guardiasSupervisor->values()->all(),
                'guardias_imss'       => $guardiasImss->values()->all(),
            ],
        ];
    }

    /**
     * Filtros base usados en todos los reportes.
     */
    protected function applyFilters(Builder $query, Request $request): void
    {
        // Patrón
        if ($request->filled('patron_id')) {
            $query->where('patron_id', $request->input('patron_id'));
        }

        // Plaza / sucursal
        if ($request->filled('sucursal_id')) {
            $query->where('sucursal_id', $request->input('sucursal_id'));
        }

        // Departamento
        if ($request->filled('departamento_id')) {
            $query->where('departamento_id', $request->input('departamento_id'));
        }

        // Supervisor
        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->input('supervisor_id'));
        }

        // Empleado específico
        if ($request->filled('empleado_id')) {
            $query->where('id', $request->input('empleado_id'));
        }

        // Búsqueda global por nombre / número
        if ($request->filled('q')) {
            $term = trim($request->input('q'));

            $query->where(function (Builder $sub) use ($term) {
                $sub->where('nombres', 'like', "%{$term}%")
                    ->orWhere('apellidoPaterno', 'like', "%{$term}%")
                    ->orWhere('apellidoMaterno', 'like', "%{$term}%")
                    ->orWhere('numero_trabajador', 'like', "%{$term}%");
            });
        }

        // Solo con IMSS / sin IMSS (filtros rápidos)
        if ($request->filled('imss_estado')) {
            if ($request->input('imss_estado') === 'alta') {
                $query->where('tiene_imss', 1);
            } elseif ($request->input('imss_estado') === 'sin_imss') {
                $query->where('tiene_imss', 0);
            }
        }

        // Rango de fechas por fecha_ingreso
        $desde = $request->input('fecha_desde');
        $hasta = $request->input('fecha_hasta');

        if ($desde) {
            try {
                $from = Carbon::parse($desde)->startOfDay();
                $query->whereDate('fecha_ingreso', '>=', $from);
            } catch (\Throwable $e) {
                // ignorar fecha inválida
            }
        }

        if ($hasta) {
            try {
                $to = Carbon::parse($hasta)->endOfDay();
                $query->whereDate('fecha_ingreso', '<=', $to);
            } catch (\Throwable $e) {
                // ignorar fecha inválida
            }
        }
    }
}
