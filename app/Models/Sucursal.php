<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursals';

    protected $fillable = [
        'nombre',
        'direccion',
        'activa',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }
    
}
