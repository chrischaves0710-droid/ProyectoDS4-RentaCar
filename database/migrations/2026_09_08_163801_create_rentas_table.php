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
        Schema::create('rentas', function (Blueprint $table) {
            $table->id(); // Primary Key automática (id)
            
            // Llaves foráneas
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('vehiculo_id')->constrained('vehiculos')->onDelete('cascade');
            
            // Campos de fechas
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            
            // Campos numéricos/decimales
            $table->decimal('precio_diario', 8, 2);
            $table->decimal('monto_total', 10, 2);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentas');
    }
};