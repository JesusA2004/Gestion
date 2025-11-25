<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepartamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Todos los usuarios autenticados que pasen por el middleware 'auth'
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'    => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del departamento es obligatorio.',
            'nombre.max'      => 'El nombre no debe superar los 255 caracteres.',
            'direccion.max'   => 'La dirección no debe superar los 255 caracteres.',
        ];
    }
}
