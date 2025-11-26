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
        'estado_imss',
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
        'sdi',
        'supervisor_id',
        'empresa_facturar',
        'importe_factura_mensual',
        'fecha_ingreso',
        'numero_reingresos',
        'color',
    ];

    protected $casts = [
        'fecha_alta_imss'       => 'date',
        'fecha_ingreso'         => 'date',
        'importe_factura_mensual' => 'decimal:2',
        'sdi'                   => 'decimal:2',
        'patron_id'                => 'integer',
        'sucursal_id'              => 'integer',
        'departamento_id'          => 'integer',
        'supervisor_id'            => 'integer',
        'numero_reingresos'        => 'integer',
        'estado_imss'              => 'string',
    ];

    public function periodos()
    {
        return $this->hasMany(EmpleadoPeriodo::class, 'empleado_id');
    }

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
