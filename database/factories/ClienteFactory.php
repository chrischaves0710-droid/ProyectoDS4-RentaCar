<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // unique() no repitan valores; numerify('#########') simular el formato de una cédula.
            'cedula' => fake()->unique()->numerify('#########'),
            
            // firstName() genera un nombre de pila realista.
            'nombre1' => fake()->firstName(),
            
            // optional() permite null (es opcional/nullable), y firstName() da el segundo nombre si no es null.
            'nombre2' => fake()->optional()->firstName(),
            
            // lastName() genera un apellido.
            'apellido1' => fake()->lastName(),
            
            // lastName() genera el segundo apellido.
            'apellido2' => fake()->lastName(),
            
            // numberBetween() restringe el año entre 1950 y 2008 (+18).
            'anno_nacimiento' => fake()->numberBetween(1950, 2008),
            
            // phoneNumber() número de teléfono con un formato válido.
            'telefono' => fake()->phoneNumber(),
            
            // unique() evita que dos clientes tengan el mismo correo; safeEmail() genera direcciones falsas seguras.
            'correo' => fake()->unique()->safeEmail(),
        ];
    }
}
