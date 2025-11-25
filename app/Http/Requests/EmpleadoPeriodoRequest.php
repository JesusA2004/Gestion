<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpleadoPeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'empleado_id' => ['required', 'integer', 'exists:empleados,id'],

            'fecha_alta'  => ['required', 'date'],
            'fecha_baja'  => ['nullable', 'date', 'after_or_equal:fecha_alta'],

            'tipo_alta'   => ['required', 'in:alta,reingreso'],

            'motivo_baja' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'empleado_id.required' => 'El empleado es obligatorio.',
            'empleado_id.integer'  => 'El empleado seleccionado no es válido.',
            'empleado_id.exists'   => 'El empleado seleccionado no existe.',

            'fecha_alta.required' => 'La fecha de alta es obligatoria.',
            'fecha_alta.date'     => 'La fecha de alta no tiene un formato válido.',

            'fecha_baja.date'           => 'La fecha de baja no tiene un formato válido.',
            'fecha_baja.after_or_equal' => 'La fecha de baja no puede ser anterior a la fecha de alta.',

            'tipo_alta.required' => 'El tipo de alta es obligatorio.',
            'tipo_alta.in'       => 'El tipo de alta debe ser "alta" o "reingreso".',

            'motivo_baja.string' => 'El motivo de baja debe ser texto.',
        ];
    }
}
