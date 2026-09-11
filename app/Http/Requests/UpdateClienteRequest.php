<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    /**
     * Determine si el usuario está autorizado.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para actualizar un cliente.
     */
    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'cedula' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clientes', 'cedula')->ignore($cliente->id),
            ],
            'nombre1' => 'required|string|max:255',
            'nombre2' => 'nullable|string|max:255',
            'apellido1' => 'required|string|max:255',
            'apellido2' => 'nullable|string|max:255',
            'anno_nacimiento' => 'required|integer|min:1900|max:' . date('Y'),
            'telefono' => 'required|string|max:255',
            'correo' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clientes', 'correo')->ignore($cliente->id),
            ],
        ];
    }

    /**
     * Mensajes de validación en español.
     */
    public function messages(): array
    {
        return [
            'cedula.required' => 'La cédula es obligatoria.',
            'cedula.string' => 'La cédula debe ser un texto.',
            'cedula.max' => 'La cédula no puede superar los 255 caracteres.',
            'cedula.unique' => 'La cédula ya está registrada.',

            'nombre1.required' => 'El primer nombre es obligatorio.',
            'nombre1.string' => 'El primer nombre debe ser un texto.',
            'nombre1.max' => 'El primer nombre no puede superar los 255 caracteres.',

            'nombre2.string' => 'El segundo nombre debe ser un texto.',
            'nombre2.max' => 'El segundo nombre no puede superar los 255 caracteres.',

            'apellido1.required' => 'El primer apellido es obligatorio.',
            'apellido1.string' => 'El primer apellido debe ser un texto.',
            'apellido1.max' => 'El primer apellido no puede superar los 255 caracteres.',

            'apellido2.string' => 'El segundo apellido debe ser un texto.',
            'apellido2.max' => 'El segundo apellido no puede superar los 255 caracteres.',

            'anno_nacimiento.required' => 'El año de nacimiento es obligatorio.',
            'anno_nacimiento.integer' => 'El año de nacimiento debe ser un número entero.',
            'anno_nacimiento.min' => 'El año de nacimiento no puede ser menor a 1900.',
            'anno_nacimiento.max' => 'El año de nacimiento no puede ser mayor al año actual.',

            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.string' => 'El teléfono debe ser un texto.',
            'telefono.max' => 'El teléfono no puede superar los 255 caracteres.',

            'correo.required' => 'El correo electrónico es obligatorio.',
            'correo.email' => 'El correo electrónico debe tener un formato válido.',
            'correo.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'correo.unique' => 'El correo electrónico ya está registrado.',
        ];
    }
}