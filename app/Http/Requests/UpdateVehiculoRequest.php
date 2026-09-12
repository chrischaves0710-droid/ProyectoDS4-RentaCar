<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Se obtiene el ID del vehículo desde la ruta para ignorar su propia placa en el unique
        $vehiculoId = $this->route('vehiculo')?->id ?? $this->route('vehiculo');

        return [
            'placa' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehiculos', 'placa')->ignore($vehiculoId),
            ],
            'marca' => 'required|string|max:50',
            'modelo' => 'required|string|max:50',
            'anno' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kilometraje' => 'required|integer|min:0',
            'precio_diario' => 'required|numeric|min:0',
            'categoria_id' => 'required|integer|exists:categorias,id',
        ];
    }

    public function messages(): array
    {
        return [
            'placa.required' => 'La placa es obligatoria.',
            'placa.string' => 'La placa debe ser una cadena de texto.',
            'placa.max' => 'La placa no puede exceder los 20 caracteres.',
            'placa.unique' => 'La placa ya se encuentra asignada a otro vehículo.',

            'marca.required' => 'La marca es obligatoria.',
            'marca.string' => 'La marca debe ser una cadena de texto.',
            'marca.max' => 'La marca no puede exceder los 50 caracteres.',

            'modelo.required' => 'El modelo es obligatorio.',
            'modelo.string' => 'El modelo debe ser una cadena de texto.',
            'modelo.max' => 'El modelo no puede exceder los 50 caracteres.',

            'anno.required' => 'El año es obligatorio.',
            'anno.integer' => 'El año debe ser un número entero.',
            'anno.min' => 'El año no puede ser menor a 1900.',
            'anno.max' => 'El año no puede ser superior al año en curso.',

            'kilometraje.required' => 'El kilometraje es obligatorio.',
            'kilometraje.integer' => 'El kilometraje debe ser un número entero.',
            'kilometraje.min' => 'El kilometraje no puede ser negativo.',

            'precio_diario.required' => 'El precio diario es obligatorio.',
            'precio_diario.numeric' => 'El precio diario debe ser un valor numérico.',
            'precio_diario.min' => 'El precio diario no puede ser menor a 0.',

            'categoria_id.required' => 'La categoría es obligatoria.',
            'categoria_id.integer' => 'El ID de la categoría debe ser un número entero.',
            'categoria_id.exists' => 'La categoría seleccionada no existe en la base de datos.',
        ];
    }
}