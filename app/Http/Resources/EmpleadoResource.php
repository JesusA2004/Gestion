<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpleadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'nombres'             => $this->nombres,
            'apellidoPaterno'     => $this->apellidoPaterno,
            'apellidoMaterno'     => $this->apellidoMaterno,
            'nombre_completo'     => $this->nombre_completo,

            'numero_trabajador'   => $this->numero_trabajador,
            'estado'              => $this->estado,

            'patron_id'           => $this->patron_id,
            'patron_nombre'       => optional($this->patron)->nombre,

            'sucursal_id'         => $this->sucursal_id,
            'sucursal_nombre'     => optional($this->sucursal)->nombre,

            'departamento_id'     => $this->departamento_id,
            'departamento_nombre' => optional($this->departamento)->nombre,

            'supervisor_id'       => $this->supervisor_id,
            'supervisor_nombre'   => optional($this->supervisor)->nombre_completo ?? null,

            'fecha_ingreso'       => optional($this->fecha_ingreso)->format('Y-m-d'),
            'fecha_baja'          => optional($this->fecha_baja)->format('Y-m-d'),

            'created_at'          => optional($this->created_at)->format('Y-m-d H:i'),
            'updated_at'          => optional($this->updated_at)->format('Y-m-d H:i'),
        ];
    }
}
