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
            // Se usa un elemento aleatorio 
            'nombre' => $this->faker->randomElement(['Disponible', 'Rentado', 'En Mantenimiento', 'Fuera de Servicio']),
        ];
    }
}
