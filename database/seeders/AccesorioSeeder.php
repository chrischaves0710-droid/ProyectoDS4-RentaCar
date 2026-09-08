<?php

namespace Database\Seeders;

use App\Models\Accesorio;
use Illuminate\Database\Seeder;

class AccesorioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Accesorio::factory(10)->create();
    }
}