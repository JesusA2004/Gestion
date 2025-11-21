<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ya controlas acceso con middleware auth
        return true;
    }

    public function rules(): array
    {
        $empleado   = $this->route('empleado'); // viene del Route::resource
        $empleadoId = $empleado?->id ?? null;

        return [
            // Identidad básica
            'nombres'          => ['required', 'string', 'max:150'],
            'apellidoPaterno'  => ['required', 'string', 'max:150'],
            'apellidoMaterno'  => ['nullable', 'string', 'max:150'],

            // Datos laborales
            'numero_trabajador' => ['required', 'string', 'max:20', 'unique:empleados,numero_trabajador,' . $empleadoId],
            'patron_id'         => ['required', 'integer', 'exists:patrons,id'],
            'sucursal_id'       => ['required', 'integer', 'exists:sucursals,id'],
            'departamento_id'   => ['required', 'integer', 'exists:departamentos,id'],
            'supervisor_id'     => ['nullable', 'integer', 'exists:supervisors,id'],
            'estado'            => ['required', 'in:alta,baja'],
            'fecha_ingreso'     => ['required', 'date'],
            'fecha_baja'        => ['nullable', 'date', 'after_or_equal:fecha_ingreso'],

            // IMSS
            'numero_imss'       => ['nullable', 'string', 'max:20', 'unique:empleados,numero_imss,' . $empleadoId],
            'registro_patronal' => ['nullable', 'string', 'max:30'],
            'codigo_postal'     => ['nullable', 'string', 'max:10'],
            'fecha_alta_imss'   => ['nullable', 'date'],

            // Identificaciones
            'curp' => ['nullable', 'string', 'max:18', 'unique:empleados,curp,' . $empleadoId],
            'rfc'  => ['nullable', 'string', 'max:18', 'unique:empleados,rfc,' . $empleadoId],

            // Bancarios
            'cuenta_bancaria'     => ['nullable', 'string', 'max:20'],
            'tarjeta'             => ['nullable', 'string', 'max:20'],
            'clabe_interbancaria' => ['nullable', 'string', 'max:20'],
            'banco'               => ['nullable', 'string', 'max:100'],

            // Sueldos
            'sueldo_diario_bruto'    => ['nullable', 'numeric', 'min:0'],
            'sueldo_diario_neto'     => ['nullable', 'numeric', 'min:0'],
            'salario_diario_imss'    => ['nullable', 'numeric', 'min:0'],
            'sdi'                    => ['nullable', 'numeric', 'min:0'],

            // Facturación
            'empresa_facturar'       => ['nullable', 'string', 'max:150'],
            'total_guardias_factura' => ['nullable', 'integer', 'min:0'],
            'importe_factura_mensual'=> ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required'          => 'El nombre del empleado es obligatorio.',
            'apellidoPaterno.required'  => 'El apellido paterno es obligatorio.',
            'numero_trabajador.required'=> 'El número de trabajador es obligatorio.',
            'numero_trabajador.unique'  => 'Ya existe un empleado con ese número de trabajador.',
            'patron_id.required'        => 'Debes seleccionar un patrón / empresa.',
            'sucursal_id.required'      => 'Debes seleccionar una sucursal.',
            'departamento_id.required'  => 'Debes seleccionar un departamento.',
            'estado.in'                 => 'El estado debe ser alta o baja.',
            'fecha_ingreso.required'    => 'La fecha de ingreso es obligatoria.',
            'fecha_baja.after_or_equal' => 'La fecha de baja no puede ser anterior a la fecha de ingreso.',
            'numero_imss.unique'        => 'Este número de IMSS ya está registrado.',
            'curp.unique'               => 'Esta CURP ya está registrada.',
            'rfc.unique'                => 'Este RFC ya está registrado.',
        ];
    }
}
