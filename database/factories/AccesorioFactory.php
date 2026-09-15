<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AccesorioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'precio_unitario' => fake()->randomFloat(2, 500, 10000),
        ];
    }
}