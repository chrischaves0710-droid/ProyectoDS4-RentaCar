<?php

namespace Database\Seeders;

use App\Models\Accesorio;
use App\Models\Renta;
use Illuminate\Database\Seeder;

class RentaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accesorios = Accesorio::all();

        if ($accesorios->isEmpty()) {
            $accesorios = Accesorio::factory(5)->create();
        }

        $rentas = Renta::factory(15)->create();

        $rentas->each(function ($renta) use ($accesorios) {
            $accesoriosAsignados = $accesorios->random(rand(1, 3));

            foreach ($accesoriosAsignados as $accesorio) {
                $cantidad = rand(1, 2);
                $precio = $accesorio->precio_unitario;

                $renta->accesorios()->attach($accesorio->id, [
                    'cantidad' => $cantidad,
                    'precio_diario' => $precio,
                    'subtotal' => $cantidad * $precio,
                ]);
            }
        });
    }
}