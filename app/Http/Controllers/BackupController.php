<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EmpleadoPeriodo;
use App\Models\Patron;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class BackupController extends Controller
{
    /**
     * Vista de respaldo / restauración / importación Excel
     */
    public function index()
    {
        return view('backup.index');
    }

    /**
     * Descargar respaldo actual de la base de datos (archivo .sql)
     */
    public function download()
    {
        $path = storage_path('app/restauracion_empleados.sql');

        if (! file_exists($path)) {
            return back()->withErrors([
                'error' => 'No existe el archivo de respaldo restauracion_empleados.sql',
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

        $sqlFile = $request->file('archivo');

        if (! $sqlFile || ! $sqlFile->isValid()) {
            return back()->withErrors([
                'archivo' => 'El archivo SQL no se pudo subir correctamente. Verifica el tamaño y vuelve a intentarlo.',
            ]);
        }

        $sql = file_get_contents($sqlFile->getRealPath());

        try {
            DB::beginTransaction();

            $comandos = array_filter(
                array_map('trim', explode(';', $sql)),
                fn ($cmd) => $cmd !== ''
            );

            foreach ($comandos as $cmd) {
                $trim = ltrim($cmd);

                if (str_starts_with($trim, '--')) {
                    continue;
                }
                if (str_starts_with($trim, '/*')) {
                    continue;
                }

                DB::statement($cmd);
            }

            DB::commit();

            return back()->with('status', 'Restauración desde SQL realizada correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'archivo' => 'Error al restaurar: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Importar datos de empleados desde el Excel de BD general.
     */
    public function importExcel(Request $request)
    {
        // Por si el archivo está pesado
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $request->validate([
            'archivo' => ['required', 'mimes:xlsx,xls,xlsm'],
        ]);

        if (! $request->hasFile('archivo')) {
            return back()->withErrors([
                'archivo' => 'No se recibió ningún archivo. Revisa el tamaño y vuelve a intentarlo.',
            ]);
        }

        $file = $request->file('archivo');

        if (! $file->isValid()) {
            return back()->withErrors([
                'archivo' => 'El archivo no se pudo subir correctamente. Es probable que exceda el tamaño máximo permitido por el servidor.',
            ]);
        }

        try {
            // Cargamos todas las hojas como colección
            $sheets = Excel::toCollection(null, $file);

            if ($sheets->isEmpty()) {
                return back()->withErrors([
                    'archivo' => 'El archivo está vacío o no contiene hojas.',
                ]);
            }

            // Buscamos una hoja que tenga la cabecera "NUMERO DE TRABAJADOR NOI"
            $sheet = null;
            foreach ($sheets as $candidate) {
                if ($candidate->count() === 0) {
                    continue;
                }

                $firstRow = $candidate->first();

                if (is_array($firstRow)) {
                    $headersArray = $firstRow;
                } else {
                    $headersArray = $firstRow->toArray();
                }

                $joined = strtoupper(implode(' ', array_map('strval', $headersArray)));
                if (str_contains($joined, 'NUMERO DE TRABAJADOR NOI')) {
                    $sheet = $candidate;
                    break;
                }
            }

            // Si no encontramos, tomamos la primera hoja (compatibilidad)
            if ($sheet === null) {
                $sheet = $sheets->first();
            }

            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return back()->withErrors([
                    'archivo' => 'La hoja BD no parece tener datos (solo encabezados o menos).',
                ]);
            }

            // Primera fila: encabezados
            $headersRow = array_map(
                fn ($v) => trim((string) $v),
                $rows[0]
            );

            // Columnas mínimas que esperamos del Excel
            $requiredColumns = [
                'NUMERO DE TRABAJADOR NOI',
                'NOMBRE',
                'PATRON',
                'PLAZA',
                'IMSS',
                'NSS',
                'RFC',
                'CURP',
                'Fecha de Alta IMSS',
                'SDI',
                'FECHA DE BAJA',
                'SUPERVISOR',
                'Departamento',
            ];

            foreach ($requiredColumns as $colName) {
                if (! in_array($colName, $headersRow, true)) {
                    return back()->withErrors([
                        'archivo' => 'El archivo no tiene el formato esperado. Falta la columna: ' . $colName,
                    ]);
                }
            }

            // Mapeo: nombreColumna => índice en el array de cada fila
            $colIndex = [];
            foreach ($headersRow as $i => $colName) {
                if ($colName === '') {
                    continue;
                }
                $colIndex[$colName] = $i;
            }

            DB::beginTransaction();

            $insertados   = 0;
            $actualizados = 0;

            // Recorremos filas de datos (a partir de la fila 2)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Saltar filas completamente vacías
                $tieneAlgo = false;
                foreach ($row as $valor) {
                    if (trim((string) $valor) !== '') {
                        $tieneAlgo = true;
                        break;
                    }
                }
                if (! $tieneAlgo) {
                    continue;
                }

                $get = function (string $colName) use ($row, $colIndex) {
                    if (! array_key_exists($colName, $colIndex)) {
                        return null;
                    }
                    $pos   = $colIndex[$colName];
                    $valor = $row[$pos] ?? null;

                    if ($valor instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
                        $valor = $valor->getPlainText();
                    }

                    return is_string($valor) ? trim($valor) : $valor;
                };

                // === Datos desde Excel ===
                $numeroTrabajador   = $this->normalizarString($get('NUMERO DE TRABAJADOR NOI'));
                if (! $numeroTrabajador) {
                    continue;
                }

                $nombreCompleto     = $this->normalizarString($get('NOMBRE'));
                $patronNombre       = $this->normalizarString($get('PATRON'));
                $plazaNombre        = $this->normalizarString($get('PLAZA'));
                $departamentoNombre = $this->normalizarString($get('Departamento'));
                $supervisorNombre   = $this->normalizarString($get('SUPERVISOR'));

                $numeroImss = $this->normalizarString($get('NSS'));
                $rfc        = $this->normalizarString($get('RFC'));
                $curp       = $this->normalizarString($get('CURP'));

                $imssEstadoRaw = $this->normalizarString($get('IMSS'));
                $sdiRaw        = $get('SDI');
                $fechaAltaRaw  = $get('Fecha de Alta IMSS');
                $fechaBajaRaw  = $get('FECHA DE BAJA');

                $fechaAlta = $this->parseDateExcel($fechaAltaRaw);
                $fechaBaja = $this->parseDateExcel($fechaBajaRaw);

                $salarioSDI = is_numeric($sdiRaw) ? (float) $sdiRaw : null;

                // Estado IMSS ("Alta", "Baja", etc.) -> 'alta' / 'baja'
                $estadoImss = null;
                if ($imssEstadoRaw) {
                    $upper = mb_strtoupper($imssEstadoRaw);
                    if (str_contains($upper, 'BAJA')) {
                        $estadoImss = 'baja';
                    } elseif (str_contains($upper, 'ALTA')) {
                        $estadoImss = 'alta';
                    }
                }
                if (! $estadoImss) {
                    $estadoImss = 'alta';
                }

                // Estado general del empleado
                $estadoGeneral = $estadoImss === 'baja' ? 'inactivo' : 'activo';

                // === Tablas relacionadas con genéricos ===

                // PATRON
                if ($patronNombre) {
                    $patron = Patron::firstOrCreate(['nombre' => $patronNombre]);
                } else {
                    $patron = Patron::firstOrCreate(['nombre' => 'SIN PATRON']);
                }

                // SUCURSAL
                if ($plazaNombre) {
                    $sucursal = Sucursal::firstOrCreate(['nombre' => $plazaNombre]);
                } else {
                    $sucursal = Sucursal::firstOrCreate(['nombre' => 'SIN SUCURSAL']);
                }

                // DEPARTAMENTO
                if ($departamentoNombre) {
                    $departamento = Departamento::firstOrCreate(['nombre' => $departamentoNombre]);
                } else {
                    $departamento = Departamento::firstOrCreate(['nombre' => 'SIN DEPARTAMENTO']);
                }

                // SUPERVISOR
                $supervisor = $this->obtenerSupervisorDesdeTexto($supervisorNombre);

                // División del nombre de empleado
                [$nombres, $apellidoPaterno, $apellidoMaterno] =
                    $this->dividirNombre($nombreCompleto ?? $numeroTrabajador);

                // === Upsert del empleado (solo campos del modelo) ===
                $empleado = Empleado::firstOrNew([
                    'numero_trabajador' => $numeroTrabajador,
                ]);

                $nuevo = ! $empleado->exists;

                // Strings básicos
                $empleado->nombres         = $nombres ?? '';
                $empleado->apellidoPaterno = $apellidoPaterno ?? '';
                $empleado->apellidoMaterno = $apellidoMaterno ?? '';

                $empleado->estado_imss = $estadoImss;
                $empleado->estado      = $estadoGeneral;

                // NSS → numero_imss (UNIQUE en BD).
                if (! $numeroImss) {
                    $numeroImss = 'SINIMSS-' . $numeroTrabajador;
                }
                $empleado->numero_imss = $numeroImss;

                // === CURP (columna UNIQUE: nunca meter '' duplicado) ===
                if ($curp !== null && $curp !== '') {
                    // Trae CURP real del Excel → la usamos
                    $empleado->curp = $curp;
                } elseif ($nuevo) {
                    // Alta nueva sin CURP → lo dejamos en null (no causa conflicto UNIQUE)
                    $empleado->curp = null;
                }
                // Si no es nuevo y ya tenía algún valor, lo respetamos

                // RFC (no tiene UNIQUE, se puede dejar vacío sin problema)
                if ($rfc !== null) {
                    $empleado->rfc = $rfc;
                } elseif ($nuevo && $empleado->rfc === null) {
                    $empleado->rfc = '';
                }

                // === Fechas ===
                if ($fechaAlta) {
                    $empleado->fecha_alta_imss = $fechaAlta;

                    // Si no tiene fecha_ingreso, usamos la misma fecha de alta
                    if (! $empleado->fecha_ingreso) {
                        $empleado->fecha_ingreso = $fechaAlta;
                    }
                } else {
                    // Si NO viene fecha de alta y tampoco tiene fecha_ingreso,
                    // ponemos hoy para evitar NULL (columna NOT NULL)
                    if (! $empleado->fecha_ingreso) {
                        $empleado->fecha_ingreso = Carbon::today()->format('Y-m-d');
                    }

                    // Y aseguramos fecha_alta_imss también con algo
                    if (! $empleado->fecha_alta_imss) {
                        $empleado->fecha_alta_imss = $empleado->fecha_ingreso;
                    }
                }

                // SDI
                if ($salarioSDI !== null) {
                    $empleado->sdi = $salarioSDI;
                }
                if ($empleado->sdi === null) {
                    $empleado->sdi = 0;
                }

                // FKs siempre con algo
                $empleado->patron_id       = $patron->id;
                $empleado->sucursal_id     = $sucursal->id;
                $empleado->departamento_id = $departamento->id;
                $empleado->supervisor_id   = $supervisor->id;

                // Campos contables / bancarios que no vienen en Excel → a valores neutros
                if ($empleado->registro_patronal === null) {
                    $empleado->registro_patronal = '';
                }
                if ($empleado->codigo_postal === null) {
                    $empleado->codigo_postal = '';
                }
                if ($empleado->cuenta_bancaria === null) {
                    $empleado->cuenta_bancaria = '';
                }
                if ($empleado->tarjeta === null) {
                    $empleado->tarjeta = '';
                }
                if ($empleado->clabe_interbancaria === null) {
                    $empleado->clabe_interbancaria = '';
                }
                if ($empleado->banco === null) {
                    $empleado->banco = '';
                }
                if ($empleado->empresa_facturar === null) {
                    $empleado->empresa_facturar = '';
                }
                if ($empleado->importe_factura_mensual === null) {
                    $empleado->importe_factura_mensual = 0;
                }
                if ($empleado->color === null) {
                    $empleado->color = '';
                }

                // Reingresos
                if ($empleado->numero_reingresos === null) {
                    $empleado->numero_reingresos = 0;
                }

                $empleado->save();

                if ($nuevo) {
                    $insertados++;
                } else {
                    $actualizados++;
                }

                // Periodos: por ahora desactivado
                $this->sincronizarPeriodosDesdeExcel($empleado, $fechaAlta, $fechaBaja, $estadoImss);
            }

            DB::commit();

            return back()->with(
                'status',
                "Importación completada. Empleados nuevos: {$insertados}, actualizados: {$actualizados}."
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'archivo' => 'Error al importar el Excel: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Normaliza string: trim y null si queda vacío.
     */
    private function normalizarString($valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);

        return $valor === '' ? null : $valor;
    }

    /**
     * Divide un nombre completo en [nombres, apellidoPaterno, apellidoMaterno]
     */
    private function dividirNombre(?string $nombreCompleto): array
    {
        if (! $nombreCompleto) {
            return [null, null, null];
        }

        $nombreCompleto = preg_replace('/\s+/', ' ', trim($nombreCompleto));
        $partes         = explode(' ', $nombreCompleto);

        $apellidoPaterno = null;
        $apellidoMaterno = null;
        $nombres         = $nombreCompleto;

        if (count($partes) >= 3) {
            $apellidoPaterno = $partes[0];
            $apellidoMaterno = $partes[1];
            $nombres         = implode(' ', array_slice($partes, 2));
        } elseif (count($partes) === 2) {
            $apellidoPaterno = $partes[0];
            $nombres         = $partes[1];
        } else {
            $nombres = $partes[0];
        }

        return [$nombres, $apellidoPaterno, $apellidoMaterno];
    }

    /**
     * Supervisor desde texto. Si no hay texto, usa "SIN SUPERVISOR".
     * Nunca deja nombres/apellidoPaterno en null.
     */
    private function obtenerSupervisorDesdeTexto(?string $nombreSupervisor): Supervisor
    {
        $nombreSupervisor = $this->normalizarString($nombreSupervisor);

        if (! $nombreSupervisor) {
            return Supervisor::firstOrCreate([
                'nombres'         => 'SIN SUPERVISOR',
                'apellidoPaterno' => '',
                'apellidoMaterno' => '',
            ]);
        }

        [$nombres, $apellidoPaterno, $apellidoMaterno] = $this->dividirNombre($nombreSupervisor);

        if (! $nombres) {
            $nombres = $nombreSupervisor;
        }
        if ($apellidoPaterno === null) {
            $apellidoPaterno = '';
        }
        if ($apellidoMaterno === null) {
            $apellidoMaterno = '';
        }

        return Supervisor::firstOrCreate([
            'nombres'         => $nombres,
            'apellidoPaterno' => $apellidoPaterno,
            'apellidoMaterno' => $apellidoMaterno,
        ]);
    }

    /**
     * Intenta convertir cualquier valor de fecha de Excel a Y-m-d
     */
    private function parseDateExcel($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                $dt = ExcelDate::excelToDateTimeObject($value);
                return Carbon::instance($dt)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Periodos importados: por ahora desactivado para evitar errores de columnas.
     */
    private function sincronizarPeriodosDesdeExcel(
        Empleado $empleado,
        ?string $fechaAlta,
        ?string $fechaBaja,
        string $estadoImss
    ): void {
        // Lo dejamos vacío mientras la tabla empleado_periodos se define bien.
        return;
    }
}
