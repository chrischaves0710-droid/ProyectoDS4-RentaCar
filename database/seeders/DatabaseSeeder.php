<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * se ejecutan los seeders en el orden correcto de relaciones.
     */
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
            EstadoSeeder::class,
            ClienteSeeder::class,
            AccesorioSeeder::class,
            VehiculoSeeder::class,
            RentaSeeder::class,
        ]);
    }
}