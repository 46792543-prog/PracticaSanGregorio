<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La configuración de matrícula pasa a poder ser por carrera: si se elige
 * una carrera puntual, ese monto rige solo para ella; si se deja sin elegir
 * (id_carrera null), rige como monto general para todas las carreras que no
 * tengan su propia configuración para ese año (ver ConfiguracionMatricula::de()).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_matricula', function (Blueprint $table) {
            $table->foreignId('id_carrera')->nullable()->after('id_anio_lectivo')->constrained('carrera', 'id_carrera');
            // Primero el índice nuevo y después se borra el viejo (la FK de id_anio_lectivo lo necesita).
            $table->unique(['id_anio_lectivo', 'id_carrera'], 'configuracion_matricula_anio_carrera_unique');
            $table->dropUnique('configuracion_matricula_id_anio_lectivo_unique');
        });
    }

    public function down(): void
    {
        // Deja una sola configuración por año (la general, o si no había, la primera con carrera).
        $idsAConservar = \Illuminate\Support\Facades\DB::table('configuracion_matricula')
            ->selectRaw('MIN(id_configuracion_matricula) as id')
            ->groupBy('id_anio_lectivo')
            ->pluck('id');
        \Illuminate\Support\Facades\DB::table('configuracion_matricula')->whereNotIn('id_configuracion_matricula', $idsAConservar)->delete();

        Schema::table('configuracion_matricula', function (Blueprint $table) {
            $table->unique('id_anio_lectivo', 'configuracion_matricula_id_anio_lectivo_unique');
            $table->dropUnique('configuracion_matricula_anio_carrera_unique');
            $table->dropConstrainedForeignId('id_carrera');
        });
    }
};
