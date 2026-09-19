<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoriaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'descuento_porcentaje' => (float) $this->descuento_porcentaje,
            'activo' => (bool) $this->activo,
            'vehiculos' => $this->whenLoaded('vehiculos'),
            'creado_el' => $this->created_at?->toIso8601String(),
        ];
    }
}
