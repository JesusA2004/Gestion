<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmpleadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $empleado   = $this->route('empleado');
        $empleadoId = $empleado?->id ?? null;

        return [
            // IDENTIDAD
            'nombres'           => ['required', 'string', 'max:150'],
            'apellidoPaterno'   => ['required', 'string', 'max:150'],
            'apellidoMaterno'   => ['nullable', 'string', 'max:150'],

            // DATOS LABORALES
            'numero_trabajador' => ['required', 'string', 'max:20', 'unique:empleados,numero_trabajador,' . $empleadoId],
            'patron_id'         => ['required', 'integer', 'exists:patrons,id'],
            'sucursal_id'       => ['required', 'integer', 'exists:sucursals,id'],
            'departamento_id'   => ['required', 'integer', 'exists:departamentos,id'],
            'supervisor_id'     => ['nullable', 'integer', 'exists:supervisors,id'],

            'estado'            => ['required', 'string', 'max:150'],
            'estado_imss'       => ['required', 'in:alta,inactivo'],

            'numero_imss'       => ['nullable', 'string', 'max:30'],
            'registro_patronal' => ['nullable', 'string', 'max:40'],
            'codigo_postal'     => ['nullable', 'string', 'max:10'],

            'fecha_alta_imss'   => ['nullable', 'date'],

            // DOCUMENTACIÓN
            'curp' => ['required', 'string', 'max:18'],
            'rfc'  => ['required', 'string', 'max:13'],

            // DATOS BANCARIOS
            'cuenta_bancaria'     => ['nullable', 'string', 'max:30'],
            'tarjeta'             => ['nullable', 'string', 'max:20'],
            'clabe_interbancaria' => ['nullable', 'string', 'max:20'],
            'banco'               => ['nullable', 'string', 'max:50'],
            'sdi'                 => ['required', 'numeric'],

            // FACTURACIÓN
            'empresa_facturar'        => ['nullable', 'string', 'max:255'],
            'importe_factura_mensual' => ['required', 'numeric'],

            // OTROS
            'fecha_ingreso'     => ['required', 'date'],
            'numero_reingresos' => ['required', 'integer', 'min:0'],
            'color'             => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            // IDENTIDAD
            'nombres.required'         => 'El campo nombres es obligatorio.',
            'nombres.string'           => 'Los nombres deben ser texto.',
            'nombres.max'              => 'Los nombres no deben exceder 150 caracteres.',

            'apellidoPaterno.required' => 'El apellido paterno es obligatorio.',
            'apellidoPaterno.string'   => 'El apellido paterno debe ser texto.',
            'apellidoPaterno.max'      => 'El apellido paterno no debe exceder 150 caracteres.',

            'apellidoMaterno.string'   => 'El apellido materno debe ser texto.',
            'apellidoMaterno.max'      => 'El apellido materno no debe exceder 150 caracteres.',

            // DATOS LABORALES
            'numero_trabajador.required' => 'El número de trabajador es obligatorio.',
            'numero_trabajador.string'   => 'El número de trabajador debe ser texto.',
            'numero_trabajador.max'      => 'El número de trabajador no debe exceder 20 caracteres.',
            'numero_trabajador.unique'   => 'El número de trabajador ya está registrado.',

            'patron_id.required' => 'El patrón es obligatorio.',
            'patron_id.integer'  => 'El patrón seleccionado no es válido.',
            'patron_id.exists'   => 'El patrón seleccionado no existe.',

            'sucursal_id.required' => 'La sucursal es obligatoria.',
            'sucursal_id.integer'  => 'La sucursal seleccionada no es válida.',
            'sucursal_id.exists'   => 'La sucursal seleccionada no existe.',

            'departamento_id.required' => 'El departamento es obligatorio.',
            'departamento_id.integer'  => 'El departamento seleccionado no es válido.',
            'departamento_id.exists'   => 'El departamento seleccionado no existe.',

            'supervisor_id.integer' => 'El supervisor seleccionado no es válido.',
            'supervisor_id.exists'  => 'El supervisor seleccionado no existe.',

            'estado.required' => 'El estado donde labora el empleado es obligatorio.',
            'estado.string'   => 'El estado debe ser texto.',
            'estado.max'      => 'El estado no debe exceder 150 caracteres.',

            'estado_imss.required' => 'El estado ante el IMSS es obligatorio.',
            'estado_imss.in'       => 'El estado IMSS debe ser "alta" o "inactivo".',

            'numero_imss.string' => 'El número de IMSS debe ser texto.',
            'numero_imss.max'    => 'El número de IMSS no debe exceder 30 caracteres.',

            'registro_patronal.string' => 'El registro patronal debe ser texto.',
            'registro_patronal.max'    => 'El registro patronal no debe exceder 40 caracteres.',

            'codigo_postal.string' => 'El código postal debe ser texto.',
            'codigo_postal.max'    => 'El código postal no debe exceder 10 caracteres.',

            'fecha_alta_imss.date' => 'La fecha de alta en IMSS no tiene un formato válido.',

            // DOCUMENTACIÓN
            'curp.required' => 'La CURP es obligatoria.',
            'curp.string'   => 'La CURP debe ser texto.',
            'curp.max'      => 'La CURP no debe exceder 18 caracteres.',

            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.string'   => 'El RFC debe ser texto.',
            'rfc.max'      => 'El RFC no debe exceder 13 caracteres.',

            // DATOS BANCARIOS
            'cuenta_bancaria.string' => 'La cuenta bancaria debe ser texto.',
            'cuenta_bancaria.max'    => 'La cuenta bancaria no debe exceder 30 caracteres.',

            'tarjeta.string' => 'La tarjeta debe ser texto.',
            'tarjeta.max'    => 'La tarjeta no debe exceder 20 caracteres.',

            'clabe_interbancaria.string' => 'La CLABE interbancaria debe ser texto.',
            'clabe_interbancaria.max'    => 'La CLABE interbancaria no debe exceder 20 caracteres.',

            'banco.string' => 'El banco debe ser texto.',
            'banco.max'    => 'El banco no debe exceder 50 caracteres.',

            'sdi.numeric' => 'El SDI debe ser un valor numérico.',

            // FACTURACIÓN
            'empresa_facturar.string' => 'La empresa a facturar debe ser texto.',
            'empresa_facturar.max'    => 'La empresa a facturar no debe exceder 255 caracteres.',

            'importe_factura_mensual.numeric' => 'El importe de factura mensual debe ser numérico.',

            // OTROS
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria.',
            'fecha_ingreso.date'     => 'La fecha de ingreso no tiene un formato válido.',

            'numero_reingresos.required' => 'El número de reingresos es obligatorio.',
            'numero_reingresos.integer'  => 'El número de reingresos debe ser un número entero.',
            'numero_reingresos.min'      => 'El número de reingresos no puede ser negativo.',

            'color.string' => 'El color debe ser texto.',
            'color.max'    => 'El color no debe exceder 20 caracteres.',
        ];
    }
}
