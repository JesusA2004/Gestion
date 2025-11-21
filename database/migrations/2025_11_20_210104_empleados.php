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
            
            // Datos personales
            $table->string('nombres');
            $table->string('apellidoPaterno');
            $table->string('apellidoMaterno')->nullable();
            
            // Datos laborales
            $table->string('numero_trabajador', 20)->unique();
            $table->foreignId('patron_id')->constrained('patrons')->onDelete('cascade');
            $table->foreignId('sucursal_id')->constrained('sucursals')->onDelete('cascade');
            $table->foreignId('departamento_id')->constrained('departamentos')->onDelete('cascade');
            $table->enum('estado', ['alta', 'baja'])->default('alta');
            
            // Datos IMSS
            $table->string('numero_imss', 20)->unique();
            $table->string('registro_patronal', 30);
            $table->string('codigo_postal', 10)->nullable();
            $table->date('fecha_alta_imss');
            
            // Identificaciones
            $table->string('curp', 18)->unique();
            $table->string('rfc', 18)->unique();
            
            // Datos bancarios
            $table->string('cuenta_bancaria', 20)->nullable();
            $table->string('tarjeta', 20)->nullable();
            $table->string('clabe_interbancaria', 20)->nullable();
            $table->string('banco')->nullable();
            
            // Sueldos
            $table->decimal('sueldo_diario_bruto', 8, 2)->default(0);
            $table->decimal('sueldo_diario_neto', 8, 2)->default(0);
            $table->decimal('salario_diario_imss', 8, 2)->default(0);
            $table->decimal('sdi', 8, 2)->default(0);
            
            // Información adicional
            $table->foreignId('supervisor_id')->nullable()->constrained('patrons')->onDelete('set null');
            $table->string('empresa_facturar')->nullable();
            $table->integer('total_guardias_factura')->default(0);
            $table->decimal('importe_factura_mensual', 12, 2)->default(0);
            
            // Fechas
            $table->date('fecha_ingreso');
            $table->date('fecha_baja')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index('numero_trabajador');
            $table->index('numero_imss');
            $table->index('curp');
            $table->index('rfc');
            $table->index('estado');
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
