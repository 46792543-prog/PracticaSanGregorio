<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La condición genérica "Aprobada" se separa en tres formas concretas de
 * aprobación (Promocionada / Examen Final / Equivalencia), para que el
 * historial académico se pueda mostrar como un analítico oficial. Las
 * filas de historial_alumno que ya estaban en "Aprobada" pasan a
 * "Examen Final" (es como se aprobó la gran mayoría hasta ahora, vía
 * mesa de examen). inscripcion_carrera nunca usó "Aprobada", así que no
 * se ve afectada.
 */
return new class extends Migration
{
    public function up(): void
    {
        $idExamenFinal = DB::table('condicion_alumno')->insertGetId(['nombre_condicion' => 'Examen Final']);
        DB::table('condicion_alumno')->insert(['nombre_condicion' => 'Promocionada']);
        DB::table('condicion_alumno')->insert(['nombre_condicion' => 'Equivalencia']);

        $idAprobada = DB::table('condicion_alumno')->where('nombre_condicion', 'Aprobada')->value('id_condicion');

        if ($idAprobada) {
            DB::table('historial_alumno')->where('id_condicion', $idAprobada)->update(['id_condicion' => $idExamenFinal]);
            DB::table('condicion_alumno')->where('id_condicion', $idAprobada)->delete();
        }
    }

    public function down(): void
    {
        $idAprobada = DB::table('condicion_alumno')->insertGetId(['nombre_condicion' => 'Aprobada']);

        $ids = DB::table('condicion_alumno')
            ->whereIn('nombre_condicion', ['Promocionada', 'Examen Final', 'Equivalencia'])
            ->pluck('id_condicion');

        DB::table('historial_alumno')->whereIn('id_condicion', $ids)->update(['id_condicion' => $idAprobada]);
        DB::table('condicion_alumno')->whereIn('id_condicion', $ids)->delete();
    }
};
