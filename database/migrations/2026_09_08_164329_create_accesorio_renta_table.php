<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accesorio_renta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renta_id')->constrained('rentas')->onDelete('cascade');
            $table->foreignId('accesorio_id')->constrained('accesorios')->onDelete('cascade');
            $table->integer('cantidad');
            $table->decimal('precio_diario', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesorio_renta');
    }
};
