<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accesorio>
 */
class AccesorioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Simula nombres de accesorios de vehículos reales
            'nombre' => fake()->randomElement([
                'Silla de bebé para auto',
                'GPS Navegador',
                'Portabicicletas',
                'Cadena para nieve',
                'Rack de techo (Portaequipaje)',
                'Cargador rápido USB-C',
                'Seguro de colisión extra'
            ]),
            // Genera un precio decimal entre 5.00 y 50.00
            'precio_unitario' => fake()->randomFloat(2, 5, 50),
        ];
    }
}