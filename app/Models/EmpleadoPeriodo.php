<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmpleadoPeriodo extends Model
{
    
    use HasFactory;

    protected $table = 'empleado_periodos';

    protected $fillable = [
        'empleado_id',
        'fecha_alta',
        'fecha_baja',
        'tipo_alta',
        'motivo_baja',
    ];

    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_baja' => 'date',
        'tipo_alta' => 'string',
    ];

    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

}
