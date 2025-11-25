<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupervisorResource extends JsonResource
{
    /**
     * Transformación del modelo Supervisor a JSON.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'nombres'          => $this->nombres,
            'apellidoPaterno'  => $this->apellidoPaterno,
            'apellidoMaterno'  => $this->apellidoMaterno,
            'nombre_completo'  => trim($this->nombres . ' ' . $this->apellidoPaterno . ' ' . $this->apellidoMaterno),
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'       => $this->updated_at?->format('Y-m-d H:i:s'),

            // Relación opcional futura:
            'empleados' => EmpleadoResource::collection($this->whenLoaded('empleados')),
        ];
    }
}
