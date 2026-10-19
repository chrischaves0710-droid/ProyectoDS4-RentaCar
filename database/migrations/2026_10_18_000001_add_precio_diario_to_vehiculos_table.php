
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verificar si la columna ya existe
        if (!Schema::hasColumn('vehiculos', 'precio_diario')) {

            Schema::table('vehiculos', function (Blueprint $table) {
                $table->decimal('precio_diario', 10, 2)->nullable();
            });

        }
    }

    public function down(): void
    {
        // Eliminar la columna al revertir la migración
        if (Schema::hasColumn('vehiculos', 'precio_diario')) {

            Schema::table('vehiculos', function (Blueprint $table) {
                $table->dropColumn('precio_diario');
            });

        }
    }
};
