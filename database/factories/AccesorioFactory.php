<?php

namespace Database\Factories;

use App\Models\Accesorio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Accesorio>
 */
class AccesorioFactory extends Factory
{
    /**
     * 
     *
     * @var string
     */
    protected $model = Accesorio::class;

    /**
     * Define el estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Genera un nombre de 2 palabras único con inicial mayúscula
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
            // Genera un precio aleatorio entre 5.00 y 50.00 con 2 decimales
            'precio_unitario' => fake()->randomFloat(2, 5, 50),
        ];
    }
}