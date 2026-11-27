<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 3 (tomado de SIGEDOC): la asignación profesor-materia pasa a ser una
 * DESIGNACIÓN, con sus horas cátedra, tipo (Titular / Interino / Suplente),
 * fecha de alta, fecha de baja prevista, estado y número de resolución.
 * Se amplía la misma tabla para no romper los horarios ni la grilla.
 * Las asignaciones que ya existían se completan solas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignacion_profesor_materia', function (Blueprint $table) {
            $table->unsignedTinyInteger('horas_catedra')->nullable()->after('id_anio_lectivo');
            $table->enum('tipo', ['Titular', 'Interino', 'Suplente'])->default('Titular')->after('horas_catedra');
            $table->date('fecha_alta')->nullable()->after('tipo');
            $table->date('fecha_baja')->nullable()->after('fecha_alta');
            $table->boolean('activa')->default(true)->after('fecha_baja');
            $table->string('resolucion', 30)->nullable()->after('activa');
        });

        // Las que ya estaban: tipo = condición del profesor, horas = las de la materia, alta = fecha de asignación.
        DB::statement('UPDATE asignacion_profesor_materia a
            JOIN profesor p ON p.id_profesor = a.id_profesor
            JOIN materia m ON m.id_materia = a.id_materia
            SET a.tipo = p.condicion, a.horas_catedra = m.horas_catedra, a.fecha_alta = DATE(a.fecha_asignacion), a.activa = 1');
    }

    public function down(): void
    {
        Schema::table('asignacion_profesor_materia', function (Blueprint $table) {
            $table->dropColumn(['horas_catedra', 'tipo', 'fecha_alta', 'fecha_baja', 'activa', 'resolucion']);
        });
    }
};
