<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        // Crear la tabla 'patrons'
        Schema::create('patrons', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            // timestamps sirve para crear las columnas created_at y updated_at
            $table->timestamps();
        });
    }

    /**
     * Revertir las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('patrons');
    }
};
