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
        Schema::create('empleado_periodos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empleado_id')
                ->constrained('empleados')
                ->cascadeOnDelete();

            // Inicio del periodo laboral
            $table->date('fecha_alta');

            // Fin del periodo (NULL = sigue activo)
            $table->date('fecha_baja')->nullable();

            // Tipo de alta (texto libre, lo controlas en el front)
            $table->string('tipo_alta')->nullable();

            // Motivo de baja (opcional, texto largo)
            $table->text('motivo_baja')->nullable();

            $table->timestamps();

            $table->index(['empleado_id', 'fecha_alta']);
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_periodos');
    }
};
