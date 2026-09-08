<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    // Inserta vehículos de prueba en la base de datos
    public function run(): void
    {
        // Obtiene las categorías y estados creados previamente
        $categorias = Categoria::all();
        $estados = Estado::all();

        // Si ya existen categorías y estados en la base de datos, los asigna al azar
        if ($categorias->isNotEmpty() && $estados->isNotEmpty()) {
            Vehiculo::factory()->count(15)->make()->each(function ($vehiculo) use ($categorias, $estados) {
                $vehiculo->categoria_id = $categorias->random()->id;
                $vehiculo->estado_id = $estados->random()->id;
                $vehiculo->save();
            });
        } else {
            // Si no existen, el factory creará categorías y estados automáticamente
            Vehiculo::factory()->count(15)->create();
        }
    }
}