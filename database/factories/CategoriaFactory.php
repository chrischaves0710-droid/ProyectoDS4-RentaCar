<?php

namespace Database\Factories;

use App\Models\Categoria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Categoria>
 */
class CategoriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Sedán', 
                'SUV', 
                'Hatchback', 
                'Pickup', 
                'Deportivo', 
                'Van / Minivan', 
                'Eléctrico / Híbrido'
            ]),
        ];
    }
}
