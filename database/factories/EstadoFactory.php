<?php

namespace Database\Factories;

use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estado>
 */
class EstadoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement([
                'Disponible', 
                'Alquilado', 
                'En Mantenimiento', 
                'Inactivo', 
                'Para Venta'
            ]),
        ];
    }
}