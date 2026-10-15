<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La inscripción a cursada pasa a habilitarse por carrera (antes un período
 * abierto valía para todas). Los períodos que ya estaban cargados se copian a
 * cada carrera para que nada se cierre al migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->foreignId('id_carrera')->nullable()->after('id_periodo')->constrained('carrera', 'id_carrera');
        });

        // El índice único pasa a ser año + período + carrera (antes de copiar las filas, si no chocan).
        // Primero se crea el nuevo: también empieza por id_anio_lectivo, así la FK lo sigue usando.
        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->unique(['id_anio_lectivo', 'id_periodo', 'id_carrera'], 'periodo_insc_cursada_anio_periodo_carrera_unique');
            $table->dropUnique(['id_anio_lectivo', 'id_periodo']);
        });

        $carreras = DB::table('carrera')->pluck('id_carrera');

        foreach (DB::table('periodo_inscripcion_cursada')->whereNull('id_carrera')->get() as $fila) {
            foreach ($carreras as $idCarrera) {
                DB::table('periodo_inscripcion_cursada')->insert([
                    'id_anio_lectivo' => $fila->id_anio_lectivo,
                    'id_periodo' => $fila->id_periodo,
                    'id_carrera' => $idCarrera,
                    'abierto' => $fila->abierto,
                    'created_at' => $fila->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('periodo_inscripcion_cursada')->whereNull('id_carrera')->delete();

        DB::statement('ALTER TABLE periodo_inscripcion_cursada MODIFY id_carrera BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        // Vuelve a una fila por año + período: queda abierto si estaba abierto para alguna carrera.
        $grupos = DB::table('periodo_inscripcion_cursada')
            ->select('id_anio_lectivo', 'id_periodo', DB::raw('MAX(abierto) as abierto'), DB::raw('MIN(id_periodo_inscripcion) as id_conservar'))
            ->groupBy('id_anio_lectivo', 'id_periodo')
            ->get();

        foreach ($grupos as $grupo) {
            DB::table('periodo_inscripcion_cursada')
                ->where('id_anio_lectivo', $grupo->id_anio_lectivo)
                ->where('id_periodo', $grupo->id_periodo)
                ->where('id_periodo_inscripcion', '!=', $grupo->id_conservar)
                ->delete();

            DB::table('periodo_inscripcion_cursada')
                ->where('id_periodo_inscripcion', $grupo->id_conservar)
                ->update(['abierto' => $grupo->abierto]);
        }

        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->unique(['id_anio_lectivo', 'id_periodo']);
            $table->dropUnique('periodo_insc_cursada_anio_periodo_carrera_unique');
            $table->dropConstrainedForeignId('id_carrera');
        });
    }
};
