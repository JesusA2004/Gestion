<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'nombres',
        'apellidoPaterno',
        'apellidoMaterno',
        'numero_trabajador',
        'patron_id',
        'sucursal_id',
        'departamento_id',
        'estado',
        'numero_imss',
        'registro_patronal',
        'codigo_postal',
        'fecha_alta_imss',
        'curp',
        'rfc',
        'cuenta_bancaria',
        'tarjeta',
        'clabe_interbancaria',
        'banco',
        'sueldo_diario_bruto',
        'sueldo_diario_neto',
        'salario_diario_imss',
        'sdi',
        'supervisor_id',
        'empresa_facturar',
        'total_guardias_factura',
        'importe_factura_mensual',
        'fecha_ingreso',
        'fecha_baja',
    ];

    protected $casts = [
        'fecha_alta_imss'       => 'date',
        'fecha_ingreso'         => 'date',
        'fecha_baja'            => 'date',
        'activa'                => 'boolean',
        'total_guardias_factura'=> 'integer',
        'importe_factura_mensual' => 'decimal:2',
        'sueldo_diario_bruto'   => 'decimal:2',
        'sueldo_diario_neto'    => 'decimal:2',
        'salario_diario_imss'   => 'decimal:2',
        'sdi'                   => 'decimal:2',
    ];

    public function patron()
    {
        return $this->belongsTo(Patron::class, 'patron_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidoPaterno} {$this->apellidoMaterno}");
    }
    
}
