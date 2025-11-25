<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpleadoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
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

            'patron_id'           => $this->patron_id,
            'sucursal_id'         => $this->sucursal_id,
            'departamento_id'     => $this->departamento_id,
            'supervisor_id'       => $this->supervisor_id,

            'estado'              => $this->estado,
            'estado_imss'         => $this->estado_imss,
            'numero_imss'         => $this->numero_imss,
            'registro_patronal'   => $this->registro_patronal,
            'codigo_postal'       => $this->codigo_postal,

            'fecha_alta_imss'     => $this->fecha_alta_imss?->format('Y-m-d'),

            'curp'                => $this->curp,
            'rfc'                 => $this->rfc,

            'cuenta_bancaria'     => $this->cuenta_bancaria,
            'tarjeta'             => $this->tarjeta,
            'clabe_interbancaria' => $this->clabe_interbancaria,
            'banco'               => $this->banco,

            'sdi'                 => $this->sdi,
            'empresa_facturar'    => $this->empresa_facturar,
            'importe_factura_mensual' => $this->importe_factura_mensual,

            'fecha_ingreso'       => $this->fecha_ingreso?->format('Y-m-d'),
            'numero_reingresos'   => $this->numero_reingresos,
            'color'               => $this->color,

            'created_at'          => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at'          => $this->updated_at?->format('Y-m-d H:i:s'),

            // ========= Relaciones opcionales (solo si se cargan con with()) =========

            'patron' => new PatronResource($this->whenLoaded('patron')),

            'sucursal' => new SucursalResource($this->whenLoaded('sucursal')),

            'departamento' => new DepartamentoResource($this->whenLoaded('departamento')),

            'supervisor' => new SupervisorResource($this->whenLoaded('supervisor')),

            'periodos' => EmpleadoPeriodoResource::collection(
                $this->whenLoaded('periodos')
            ),
        ];
    }
}
