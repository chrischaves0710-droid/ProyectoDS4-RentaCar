<?php

namespace Database\Seeders;

use App\Models\Estado;
use Illuminate\Database\Seeder;

class EstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = ['Disponible', 'Alquilado', 'En Mantenimiento', 'Inactivo'];

        foreach ($estados as $nombreEstado) {
            Estado::firstOrCreate(['nombre' => $nombreEstado]);
        }
    }
}