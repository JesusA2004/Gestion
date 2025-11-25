<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpleadoPeriodoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'empleado_id' => $this->empleado_id,

            'fecha_alta'  => $this->fecha_alta?->format('Y-m-d'),
            'fecha_baja'  => $this->fecha_baja?->format('Y-m-d'),

            'tipo_alta'   => $this->tipo_alta,
            'motivo_baja' => $this->motivo_baja,

            'created_at'  => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'  => $this->updated_at?->format('Y-m-d H:i:s'),

            // Info ligera del empleado (opcional, sin anidar recursos completos)
            'empleado_nombre' => $this->whenLoaded('empleado', fn () => $this->empleado->nombre_completo ?? null),
            'empleado_numero_trabajador' => $this->whenLoaded('empleado', fn () => $this->empleado->numero_trabajador ?? null),
        ];
    }
}
