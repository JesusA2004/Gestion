<?php

// app/Models/Patron.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patron extends Model
{
    use HasFactory;

    protected $table = 'patrons';

    protected $fillable = [
        'nombre',
    ];

    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'patron_id');
    }
}
