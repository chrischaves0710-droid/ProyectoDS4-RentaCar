<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => 'required|integer|exists:clientes,id',
            'vehiculo_id' => 'required|integer|exists:vehiculos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'precio_diario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.integer' => 'El identificador del cliente debe ser un número entero.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',

            'vehiculo_id.required' => 'El vehículo es obligatorio.',
            'vehiculo_id.integer' => 'El identificador del vehículo debe ser un número entero.',
            'vehiculo_id.exists' => 'El vehículo seleccionado no existe.',

            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',

            'fecha_fin.required' => 'La fecha de finalización es obligatoria.',
            'fecha_fin.date' => 'La fecha de finalización debe ser una fecha válida.',
            'fecha_fin.after' => 'La fecha de finalización debe ser posterior a la fecha de inicio.',

            'precio_diario.required' => 'El precio diario es obligatorio.',
            'precio_diario.numeric' => 'El precio diario debe ser un valor numérico.',
            'precio_diario.min' => 'El precio diario no puede ser negativo.',
        ];
    }
}