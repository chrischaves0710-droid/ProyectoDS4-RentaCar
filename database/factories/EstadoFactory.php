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
            // Usamos un elemento aleatorio o único coherente con el dominio de vehículos
            'nombre' => $this->faker->unique()->randomElement(['Disponible', 'Alquilado', 'En Mantenimiento', 'Inactivo']),
        ];
    }
}
