<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    use HasFactory;

    protected $table = 'supervisors';

    protected $fillable = [
        'nombres',
        'apellidoPaterno',
        'apellidoMaterno',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'supervisor_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidoPaterno} {$this->apellidoMaterno}");
    }
    
}
