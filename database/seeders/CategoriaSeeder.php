<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Sedán',
            'SUV',
            'Hatchback',
            'Pickup',
            'Deportivo',
            'Van / Minivan',
            'Eléctrico / Híbrido'
        ];

        foreach ($categorias as $nombreCategoria) {
            Categoria::firstOrCreate(['nombre' => $nombreCategoria]);
        }
    }
}
