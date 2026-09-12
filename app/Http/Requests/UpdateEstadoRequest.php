<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Ignora el ID actual para la regla unique
            'nombre' => 'required|string|min:3|max:50|unique:estados,nombre,' . $this->route('estado'),
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del estado es obligatorio.',
            'nombre.string'   => 'El formato del nombre debe ser texto.',
            'nombre.min'      => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max'      => 'El nombre no puede superar los 50 caracteres.',
            'nombre.unique'   => 'Ya existe otro estado registrado con este nombre.',
        ];
    }
}
