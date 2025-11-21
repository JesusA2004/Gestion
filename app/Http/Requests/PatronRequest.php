<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatronRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Si solo usuarios autenticados acceden a este módulo,
        // el middleware auth ya se encarga. Aquí dejamos true.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre del patrón / empresa',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del patrón es obligatorio.',
        ];
    }
}
