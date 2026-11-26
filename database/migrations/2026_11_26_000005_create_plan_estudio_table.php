<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Planes de estudio por carrera, con vigencia: cada carrera puede tener
 * varios planes (Plan 2020, Plan 2026...). Las materias (y sus correlativas)
 * pertenecen a un plan, y cada alumno queda en el plan con el que se inscribió
 * hasta que ese plan caduca.
 *
 * Lo que ya existía pasa a ser el primer plan de cada carrera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_estudio', function (Blueprint $table) {
            $table->id('id_plan_estudio');
            $table->foreignId('id_carrera')->constrained('carrera', 'id_carrera');
            $table->string('nombre', 40);                                    // ej. "Plan 2026"
            $table->string('resolucion_ministerial', 25)->nullable();
            $table->unsignedTinyInteger('duracion_anos');
            $table->unsignedSmallInteger('anio_desde');                      // año lectivo en que empieza a regir
            $table->unsignedSmallInteger('anio_cierre_inscripcion')->nullable(); // último año en que entran alumnos nuevos
            $table->unsignedSmallInteger('anio_caducidad')->nullable();      // año en que caduca (los que quedan ya no pueden seguir)
            $table->timestamps();

            $table->unique(['id_carrera', 'nombre']);
        });

        Schema::table('materia', function (Blueprint $table) {
            $table->foreignId('id_plan_estudio')->nullable()->after('id_carrera')->constrained('plan_estudio', 'id_plan_estudio');
        });

        Schema::table('inscripcion_carrera', function (Blueprint $table) {
            $table->foreignId('id_plan_estudio')->nullable()->after('id_carrera')->constrained('plan_estudio', 'id_plan_estudio');
        });

        // Primer plan de cada carrera con lo que ya estaba cargado.
        foreach (DB::table('carrera')->get() as $carrera) {
            $anio = (int) (DB::table('materia')->where('id_carrera', $carrera->id_carrera)->min('version_plan') ?: now()->year);
            // Rige desde el año del alumno más antiguo de la carrera (si es anterior).
            $anioAlumnos = DB::table('inscripcion_carrera')->join('anio_lectivo', 'anio_lectivo.id_anio_lectivo', '=', 'inscripcion_carrera.id_anio_lectivo')
                ->where('id_carrera', $carrera->id_carrera)->min('anio_lectivo.anio');

            $idPlan = DB::table('plan_estudio')->insertGetId([
                'id_carrera' => $carrera->id_carrera,
                'nombre' => "Plan {$anio}",
                'resolucion_ministerial' => $carrera->resolucion_ministerial,
                'duracion_anos' => $carrera->duracion_anos,
                'anio_desde' => min($anio, (int) now()->year, (int) ($anioAlumnos ?: $anio)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('materia')->where('id_carrera', $carrera->id_carrera)->update(['id_plan_estudio' => $idPlan]);
            DB::table('inscripcion_carrera')->where('id_carrera', $carrera->id_carrera)->update(['id_plan_estudio' => $idPlan]);
        }
    }

    public function down(): void
    {
        Schema::table('inscripcion_carrera', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_plan_estudio'));
        Schema::table('materia', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_plan_estudio'));
        Schema::dropIfExists('plan_estudio');
    }
};
