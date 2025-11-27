<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();

            /*
             * DATOS PERSONALES
             */
            $table->string('nombres');
            $table->string('apellidoPaterno');
            $table->string('apellidoMaterno')->nullable();

            /*
             * DATOS LABORALES BÁSICOS
             */
            $table->string('numero_trabajador', 20)->unique();

            $table->foreignId('patron_id')
                ->constrained('patrons')
                ->cascadeOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursals')
                ->cascadeOnDelete();

            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->cascadeOnDelete();

            // Estado donde trabaja (geográfico, ej. 'CIUDAD DE MEXICO')
            $table->string('estado')->nullable();

            // Estado IMSS (situación laboral frente al IMSS)
            $table->enum('estado_imss', ['alta', 'inactivo'])
                ->default('alta');

            /*
             * IMSS
             */
            $table->string('numero_imss', 20)->nullable();
            $table->string('registro_patronal', 30)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->date('fecha_alta_imss')->nullable();

            /*
             * IDENTIFICACIONES
             */
            $table->string('curp', 20)->nullable();
            $table->string('rfc', 21)->nullable();

            /*
             * DATOS BANCARIOS
             */
            $table->string('cuenta_bancaria', 20)->nullable();
            $table->string('tarjeta', 20)->nullable();
            $table->string('clabe_interbancaria', 20)->nullable();
            $table->string('banco')->nullable();

            /*
             * SUELDO
             * Solo manejamos SDI como referencia principal
             */
            $table->float('sdi')->default(0);

            /*
             * SUPERVISOR
             */
            $table->foreignId('supervisor_id')
                ->nullable()
                ->constrained('supervisors')
                ->nullOnDelete();

            /*
             * FACTURACIÓN
             */
            $table->string('empresa_facturar')->nullable();
            $table->decimal('importe_factura_mensual', 12, 2)->default(0);

            /*
             * FECHAS Y CONTROL DE REINGRESOS
             */
            // Primera fecha en que ingresó a la empresa (histórica)
            $table->date('fecha_ingreso');

            // Número de veces que ha reingresado
            $table->unsignedInteger('numero_reingresos')->default(0);

            /*
             * COLOR PARA OBSERVACIONES EN EL FRONT
             */
            $table->string('color')->nullable();

            $table->timestamps();

            /*
             * ÍNDICES
             */
            $table->index('numero_trabajador');
            $table->index('numero_imss');
            $table->index('curp');
            $table->index('rfc');
            $table->index('estado_imss');
            $table->index('patron_id');
            $table->index('sucursal_id');
            $table->index('departamento_id');
            $table->index('supervisor_id');
        });
    }

    /**
     * Revierte la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
