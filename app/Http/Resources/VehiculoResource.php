<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {


        return [
            'id' => $this->id,

            // Datos del vehículo
            'placa' => $this->placa,
            'matricula' => $this->placa,
            'marca' => $this->marca,
            'modelo' => $this->modelo,

            // Año del vehículo
            'anno' => $this->anno,
            'anio' => $this->anno,
            'anio_fabricacion' => $this->anno,

            // Kilometraje y precio
            'kilometraje' => $this->kilometraje,
            'precio_diario' => (float) $this->precio_diario,

            // Identificadores de relaciones
            'categoria_id' => $this->categoria_id,
            'estado_id' => $this->estado_id,

            // Información de las relaciones
            'categoria' => $this->whenLoaded(
                'categoria',
                fn () => $this->categoria->nombre
            ),

            'estado' => $this->whenLoaded(
                'estado',
                fn () => $this->estado->nombre
            ),
        ];
    }
}
