<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Llama al factory del modelo Cliente para generar e insertar los datos en la base de datos.
        // El número 50 especifica la cantidad de clientes ficticios que se crearán.
        Cliente::factory(50)->create();
    }
}