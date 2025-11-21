<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SucursalRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Como solo usuarios autenticados acceden al panel, 
        // aquí puedes dejar true. Si quieres roles después, se ajusta.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string'],
            'activa'    => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la sucursal es obligatorio.',
            'nombre.max'      => 'El nombre de la sucursal no debe exceder 255 caracteres.',
        ];
    }
}
