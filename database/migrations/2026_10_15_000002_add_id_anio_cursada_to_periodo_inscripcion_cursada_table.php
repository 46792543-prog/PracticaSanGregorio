<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La inscripción a cursada pasa a habilitarse por año del plan de estudio
 * también (antes, abrir un período abría para 1°, 2° y 3° año a la vez).
 * Las filas que ya estaban cargadas se copian a cada año para que nada se
 * cierre al migrar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->foreignId('id_anio_cursada')->nullable()->after('id_carrera')->constrained('anio_cursada', 'id_anio_cursada');
        });

        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            // id_anio_lectivo no tenía su propio índice: el único que lo cubría
            // era el unique compuesto que estamos por tirar, y MySQL lo necesita
            // para la FK a anio_lectivo.
            $table->index('id_anio_lectivo');
        });

        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->dropUnique('periodo_insc_cursada_anio_periodo_carrera_unique');
        });

        $aniosCursada = DB::table('anio_cursada')->pluck('id_anio_cursada');

        foreach (DB::table('periodo_inscripcion_cursada')->whereNull('id_anio_cursada')->get() as $fila) {
            foreach ($aniosCursada as $idAnioCursada) {
                DB::table('periodo_inscripcion_cursada')->insert([
                    'id_anio_lectivo' => $fila->id_anio_lectivo,
                    'id_periodo' => $fila->id_periodo,
                    'id_carrera' => $fila->id_carrera,
                    'id_anio_cursada' => $idAnioCursada,
                    'abierto' => $fila->abierto,
                    'created_at' => $fila->created_at,
                    'updated_at' => now(),
                ]);
            }
        }

        DB::table('periodo_inscripcion_cursada')->whereNull('id_anio_cursada')->delete();

        DB::statement('ALTER TABLE periodo_inscripcion_cursada MODIFY id_anio_cursada BIGINT UNSIGNED NOT NULL');

        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->unique(['id_anio_lectivo', 'id_periodo', 'id_carrera', 'id_anio_cursada'], 'periodo_insc_cursada_anio_periodo_carrera_anio_cursada_unique');
        });
    }

    public function down(): void
    {
        $grupos = DB::table('periodo_inscripcion_cursada')
            ->select('id_anio_lectivo', 'id_periodo', 'id_carrera', DB::raw('MAX(abierto) as abierto'), DB::raw('MIN(id_periodo_inscripcion) as id_conservar'))
            ->groupBy('id_anio_lectivo', 'id_periodo', 'id_carrera')
            ->get();

        foreach ($grupos as $grupo) {
            DB::table('periodo_inscripcion_cursada')
                ->where('id_anio_lectivo', $grupo->id_anio_lectivo)
                ->where('id_periodo', $grupo->id_periodo)
                ->where('id_carrera', $grupo->id_carrera)
                ->where('id_periodo_inscripcion', '!=', $grupo->id_conservar)
                ->delete();

            DB::table('periodo_inscripcion_cursada')
                ->where('id_periodo_inscripcion', $grupo->id_conservar)
                ->update(['abierto' => $grupo->abierto]);
        }

        Schema::table('periodo_inscripcion_cursada', function (Blueprint $table) {
            $table->unique(['id_anio_lectivo', 'id_periodo', 'id_carrera'], 'periodo_insc_cursada_anio_periodo_carrera_unique');
            $table->dropUnique('periodo_insc_cursada_anio_periodo_carrera_anio_cursada_unique');
            $table->dropIndex(['id_anio_lectivo']);
            $table->dropConstrainedForeignId('id_anio_cursada');
        });
    }
};
