<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehiculoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // transforma el modelo en arreglo
        return [
            'id' => $this->id,
            'matricula' => $this->placa, // cambia nombre
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio_fabricacion' => $this->anno, // cambia nombre
            'kilometraje' => $this->kilometraje,
            'precio_diario' => (float) $this->precio_diario,
            'categoria' => $this->whenLoaded('categoria', fn () => $this->categoria->nombre), // carga relacion
            'estado' => $this->whenLoaded('estado', fn () => $this->estado->nombre), // carga relacion
        ];
    }
}