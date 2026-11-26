<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La configuración de cuotas pasa a ser por año lectivo Y por carrera: cada
 * carrera puede tener su propio monto, período, vencimiento, mora y aumentos
 * (ej. la Licenciatura cuesta distinto que la Tecnicatura).
 * Las configuraciones que ya existían se copian a cada carrera (con sus
 * aumentos), así ninguna carrera queda sin precio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->foreignId('id_carrera')->nullable()->after('id_anio_lectivo')->constrained('carrera', 'id_carrera');
            // Primero el índice nuevo y después se borra el viejo (la FK de id_anio_lectivo lo necesita).
            $table->unique(['id_anio_lectivo', 'id_carrera'], 'configuracion_cuota_anio_carrera_unique');
            $table->dropUnique('configuracion_cuota_id_anio_lectivo_unique');
        });

        $carreras = DB::table('carrera')->orderBy('id_carrera')->pluck('id_carrera');

        foreach (DB::table('configuracion_cuota')->whereNull('id_carrera')->get() as $config) {
            foreach ($carreras as $i => $idCarrera) {
                if ($i === 0) {
                    DB::table('configuracion_cuota')->where('id_configuracion_cuota', $config->id_configuracion_cuota)->update(['id_carrera' => $idCarrera]);
                    continue;
                }

                // Copia para las demás carreras, con sus aumentos.
                $copia = (array) $config;
                unset($copia['id_configuracion_cuota']);
                $idCopia = DB::table('configuracion_cuota')->insertGetId(['id_carrera' => $idCarrera] + $copia);

                foreach (DB::table('aumento_cuota')->where('id_configuracion_cuota', $config->id_configuracion_cuota)->get() as $aumento) {
                    $datos = (array) $aumento;
                    unset($datos['id_aumento_cuota']);
                    DB::table('aumento_cuota')->insert(['id_configuracion_cuota' => $idCopia] + $datos);
                }
            }
        }
    }

    public function down(): void
    {
        // Deja una sola configuración por año (la de la primera carrera).
        $idsAConservar = DB::table('configuracion_cuota')->selectRaw('MIN(id_configuracion_cuota) as id')->groupBy('id_anio_lectivo')->pluck('id');
        DB::table('configuracion_cuota')->whereNotIn('id_configuracion_cuota', $idsAConservar)->delete();

        Schema::table('configuracion_cuota', function (Blueprint $table) {
            $table->unique('id_anio_lectivo', 'configuracion_cuota_id_anio_lectivo_unique');
            $table->dropUnique('configuracion_cuota_anio_carrera_unique');
            $table->dropConstrainedForeignId('id_carrera');
        });
    }
};
