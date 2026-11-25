<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dos condiciones nuevas para la inscripción a la carrera (además de
 * "Regular"):
 *  - Recursante: vuelve a cursar algunas materias; su cuota depende de
 *    cuántas cursa y se la genera el director desde Registrar Cuotas.
 *  - Cursada completa: ya terminó de cursar (3er año) y solo debe finales.
 *    No paga cuotas mensuales: solo la matrícula del año en que rinde y la
 *    cuota del mes de la mesa. Es el único que puede anotarse a mesas
 *    extraordinarias.
 */
return new class extends Migration
{
    private const CONDICIONES = ['Recursante', 'Cursada completa'];

    public function up(): void
    {
        $conTimestamps = Schema::hasColumn('condicion_alumno', 'created_at');

        foreach (self::CONDICIONES as $nombre) {
            if (DB::table('condicion_alumno')->where('nombre_condicion', $nombre)->exists()) {
                continue;
            }

            DB::table('condicion_alumno')->insert(array_merge(
                ['nombre_condicion' => $nombre],
                $conTimestamps ? ['created_at' => now(), 'updated_at' => now()] : []
            ));
        }
    }

    public function down(): void
    {
        DB::table('condicion_alumno')->whereIn('nombre_condicion', self::CONDICIONES)->delete();
    }
};
