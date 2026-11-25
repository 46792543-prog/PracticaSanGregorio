<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Un alumno puede cursar dos carreras: cada carrera tiene sus propias cuotas
 * (una por mes) y su propia matrícula (una por año). Se agrega id_carrera a
 * las dos tablas y la regla de "una por mes / una por año" pasa a ser por
 * carrera. Las cuotas y matrículas que ya existían quedan asignadas a la
 * carrera del alumno (la de su inscripción más reciente).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['cuota_alumno', 'matricula_alumno'] as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('id_carrera')->nullable()->after('id_anio_lectivo')->constrained('carrera', 'id_carrera');
            });

            DB::statement("UPDATE {$tabla} t SET id_carrera = (
                SELECT ic.id_carrera FROM inscripcion_carrera ic
                WHERE ic.id_persona_alumno = t.id_persona_alumno
                ORDER BY ic.id_inscripcion_carrera DESC LIMIT 1)");
        }

        // Primero el índice nuevo (incluye la carrera) y después se borra el viejo,
        // así la clave foránea de id_persona_alumno nunca se queda sin índice.
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->unique(['id_persona_alumno', 'id_anio_lectivo', 'id_mes', 'id_carrera'], 'cuota_alumno_persona_anio_mes_carrera_unique');
            $table->dropUnique('cuota_alumno_persona_anio_mes_unique');
        });

        Schema::table('matricula_alumno', function (Blueprint $table) {
            $table->unique(['id_persona_alumno', 'id_anio_lectivo', 'id_carrera'], 'matricula_alumno_persona_anio_carrera_unique');
            $table->dropUnique(['id_persona_alumno', 'id_anio_lectivo']);
        });
    }

    public function down(): void
    {
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->unique(['id_persona_alumno', 'id_anio_lectivo', 'id_mes'], 'cuota_alumno_persona_anio_mes_unique');
            $table->dropUnique('cuota_alumno_persona_anio_mes_carrera_unique');
            $table->dropConstrainedForeignId('id_carrera');
        });

        Schema::table('matricula_alumno', function (Blueprint $table) {
            $table->unique(['id_persona_alumno', 'id_anio_lectivo']);
            $table->dropUnique('matricula_alumno_persona_anio_carrera_unique');
            $table->dropConstrainedForeignId('id_carrera');
        });
    }
};
