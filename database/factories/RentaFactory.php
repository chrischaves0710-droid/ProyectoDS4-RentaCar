<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Vehiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Renta>
 */
class RentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generar fecha de inicio y una fecha de fin posterior
        $fechaInicio = fake()->dateTimeBetween('-1 month', '+1 month');
        $diasRenta = fake()->numberBetween(1, 15);
        $fechaFin = (clone $fechaInicio)->modify("+{$diasRenta} days");

        $precioDiario = fake()->randomFloat(2, 25, 120); // Precio por día entre 25 y 120
        $montoTotal = $precioDiario * $diasRenta;

        return [
            // Toma un ID aleatorio existente o crea uno si no hay registros
            'cliente_id' => Cliente::inRandomOrder()->first()?->id ?? Cliente::factory(),
            'vehiculo_id' => Vehiculo::inRandomOrder()->first()?->id ?? Vehiculo::factory(),
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'precio_diario' => $precioDiario,
            'monto_total' => $montoTotal,
        ];
    }
}