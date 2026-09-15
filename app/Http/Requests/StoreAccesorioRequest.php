<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccesorioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    
    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:100|unique:accesorios,nombre',
            'precio_unitario' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del accesorio es obligatorio.',
            'nombre.string' => 'El nombre del accesorio debe ser una cadena de texto.',
            'nombre.max' => 'El nombre del accesorio no puede tener más de 100 caracteres.',
            'nombre.unique' => 'Ya existe un accesorio con ese nombre.',
            'precio_unitario.required' => 'El precio unitario es obligatorio.',
            'precio_unitario.numeric' => 'El precio unitario debe ser un número.',
            'precio_unitario.min' => 'El precio unitario debe ser mayor que 0.',
        ];
    }
}