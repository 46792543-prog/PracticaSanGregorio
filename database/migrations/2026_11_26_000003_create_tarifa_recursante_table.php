<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Precio de la cuota de los alumnos recursantes, por año lectivo y carrera,
 * según cuántas materias cursan (ej. 1 materia $80.000, 2 materias $140.000).
 * Los aumentos de Configuración de Cuotas no los afectan: este precio se
 * cambia acá aparte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifa_recursante', function (Blueprint $table) {
            $table->id('id_tarifa_recursante');
            $table->foreignId('id_anio_lectivo')->constrained('anio_lectivo', 'id_anio_lectivo');
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');
            $table->unsignedTinyInteger('cantidad_materias');
            $table->decimal('monto', 10, 2);
            $table->timestamps();

            $table->unique(['id_anio_lectivo', 'id_carrera', 'cantidad_materias'], 'tarifa_recursante_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifa_recursante');
    }
};
