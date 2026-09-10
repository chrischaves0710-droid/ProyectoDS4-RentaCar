<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    /**
     * Define los datos ficticios para la tabla vehiculos.
     */
    public function definition(): array
    {
        return [
            // Formato de 3 letras y 3 números en mayúscula
            'placa' => strtoupper(fake()->unique()->bothify('???-###')),

            // Lista de marcas
            'marca' => fake()->randomElement(['Toyota', 'Hyundai', 'Nissan', 'Suzuki', 'Honda', 'Mitsubishi']),

            // Lista de modelos
            'modelo' => fake()->randomElement(['Corolla', 'Elantra', 'Yaris', 'Civic', 'Tucson', 'RAV4', 'Jimny', 'Swift', 'Outlander', 'Versa']),

            // Años entre 2018 y 2025
            'anno' => fake()->numberBetween(2018, 2025),

            // Kilometraje aleatorio entre 5,000 y 120,000 km
            'kilometraje' => fake()->numberBetween(5000, 120000),

            // Reutiliza categorías y estados existentes creados por sus Seeders
            'categoria_id' => Categoria::inRandomOrder()->first()?->id ?? Categoria::factory(),
            'estado_id' => Estado::inRandomOrder()->first()?->id ?? Estado::factory(),
        ];
    }
}