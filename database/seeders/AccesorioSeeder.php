<?php

namespace Database\Seeders;

use App\Models\Accesorio;
use Illuminate\Database\Seeder;

class AccesorioSeeder extends Seeder
{
    /**
     * Ejecuta las semillas para la tabla accesorios.
     */
    public function run(): void
    {
        $accesorios = [
            ['nombre' => 'Silla de bebé para auto', 'precio_unitario' => 15.00],
            ['nombre' => 'GPS Navegador', 'precio_unitario' => 10.00],
            ['nombre' => 'Portabicicletas', 'precio_unitario' => 20.00],
            ['nombre' => 'Rack de techo (Portaequipaje)', 'precio_unitario' => 25.00],
            ['nombre' => 'Cargador rápido USB-C', 'precio_unitario' => 5.00],
            ['nombre' => 'Nevera portátil 12V', 'precio_unitario' => 18.00],
            ['nombre' => 'Kit de primeros auxilios', 'precio_unitario' => 8.00],
        ];

        foreach ($accesorios as $accesorio) {
            Accesorio::firstOrCreate(
                ['nombre' => $accesorio['nombre']],
                ['precio_unitario' => $accesorio['precio_unitario']]
            );
        }
    }
}