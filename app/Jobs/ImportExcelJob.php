<?php

namespace App\Jobs;

use App\Events\ExcelProgressEvent;
use App\Models\Empleado;
use App\Models\EmpleadoPeriodo;
use App\Models\Patron;
use App\Models\Sucursal;
use App\Models\Departamento;
use App\Models\Supervisor;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportExcelJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $userId;

    public function __construct($path, $userId)
    {
        $this->path   = $path;
        $this->userId = $userId;
    }

    public function handle()
    {
        broadcast(new ExcelProgressEvent($this->userId, 1, "Procesando archivo..."));

        $sheets = Excel::toCollection(null, storage_path("app/{$this->path}"))->first();

        $total = $sheets->count() - 1;
        $processed = 0;

        $rows = $sheets->toArray();

        DB::beginTransaction();

        for ($i = 1; $i < count($rows); $i++) {

            $row = $rows[$i];

            // ... ← AQUI PEGAS TODO TU PROCESO DE UNA SOLA FILA EXACTO COMO LO TIENES

            $processed++;

            $percent = intval(($processed / $total) * 100);

            broadcast(new ExcelProgressEvent(
                $this->userId,
                $percent,
                "Procesadas {$processed} de {$total} filas"
            ));
        }

        DB::commit();

        broadcast(new ExcelProgressEvent($this->userId, 100, "Importación completada"));
    }
}
