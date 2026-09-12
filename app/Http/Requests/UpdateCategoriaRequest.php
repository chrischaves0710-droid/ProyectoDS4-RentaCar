<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoriaId = $this->route('categoria')?->id ?? $this->route('categoria');

        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categorias', 'nombre')->ignore($categoriaId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El campo nombre es obligatorio.',
            'nombre.string'   => 'El campo nombre debe ser una cadena de texto.',
            'nombre.max'      => 'El campo nombre no debe superar los 255 caracteres.',
            'nombre.unique'   => 'El nombre de la categoría ya se encuentra registrado.',
        ];
    }
}
