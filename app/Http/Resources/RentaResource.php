<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'fecha_inicio' => $this->fecha_inicio?->format('Y-m-d'),
            'fecha_fin' => $this->fecha_fin?->format('Y-m-d'),

            'precio_diario' => (float) $this->precio_diario,
            'monto_total' => (float) $this->monto_total,

            'cliente' => $this->whenLoaded('cliente', function () {
                return [
                    'id' => $this->cliente->id,
                    'nombre_completo' => trim(
                        $this->cliente->nombre1 . ' ' .
                            ($this->cliente->nombre2 ?? '') . ' ' .
                            $this->cliente->apellido1 . ' ' .
                            ($this->cliente->apellido2 ?? '')
                    ),
                ];
            }),

            'vehiculo' => $this->whenLoaded('vehiculo', function () {
                return [
                    'id' => $this->vehiculo->id,
                    'matricula' => $this->vehiculo->placa,
                    'marca' => $this->vehiculo->marca,
                    'modelo' => $this->vehiculo->modelo,
                ];
            }),

            'accesorios' => $this->whenLoaded('accesorios', function () {
                return $this->accesorios->map(function ($accesorio) {
                    return [
                        'id' => $accesorio->id,
                        'nombre' => $accesorio->nombre,
                        'cantidad' => (int) $accesorio->pivot->cantidad,
                        'precio_diario' => (float) $accesorio->pivot->precio_diario,
                        'subtotal' => (float) $accesorio->pivot->subtotal,
                    ];
                });
            }),
        ];
    }
}
