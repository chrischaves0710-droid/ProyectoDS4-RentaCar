<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Estado;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehiculoFactory extends Factory
{
    //  datos ficticios para la tabla 
    public function definition(): array
    {
        return [
            // formato de 3 letras y 3 numeros en mayuscula
            'placa' => strtoupper(fake()->unique()->bothify('???-###')),

            // lista de marcas
            'marca' => fake()->randomElement(['Toyota', 'Hyundai', 'Nissan', 'Suzuki', 'Honda', 'Mitsubishi']),

            // lista de modelos
            'modelo' => fake()->randomElement(['Corolla', 'Elantra', 'Yaris', 'Civic', 'Tucson', 'RAV4', 'Jimny', 'Swift', 'Outlander', 'Versa  ']),

            // Años entre 2018 y 2025
            'anno' => fake()->numberBetween(2018, 2025),

            // Kilometraje aleatorio entre 5,000 y 120,000 km
            'kilometraje' => fake()->numberBetween(5000, 120000),

            // crea o asocia automáticamente una categoría
            'categoria_id' => Categoria::factory(),

            //crea o asocia automáticamente un estado
            'estado_id' => Estado::factory(),
        ];
    }
}