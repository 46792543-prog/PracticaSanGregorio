<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El valor de la hora cátedra pasa a ser por carrera y tipo de designación
 * (no cobra lo mismo un docente de la Licenciatura que uno de la Tecnicatura).
 * Los valores que ya estaban cargados se copian a cada carrera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('valor_hora_catedra', function (Blueprint $table) {
            $table->unsignedBigInteger('id_carrera')->nullable()->after('id_valor_hora_catedra');
            $table->dropUnique(['tipo', 'vigente_desde']); // antes de copiar a cada carrera
        });

        $carreras = DB::table('carrera')->pluck('id_carrera');
        foreach (DB::table('valor_hora_catedra')->whereNull('id_carrera')->get() as $valor) {
            foreach ($carreras as $i => $idCarrera) {
                $i === 0
                    ? DB::table('valor_hora_catedra')->where('id_valor_hora_catedra', $valor->id_valor_hora_catedra)->update(['id_carrera' => $idCarrera])
                    : DB::table('valor_hora_catedra')->insert(['id_carrera' => $idCarrera] + collect((array) $valor)->except('id_valor_hora_catedra', 'id_carrera')->all());
            }
        }
        DB::table('valor_hora_catedra')->whereNull('id_carrera')->delete(); // sin carreras no hay a qué asignarlos

        DB::statement('ALTER TABLE valor_hora_catedra MODIFY id_carrera BIGINT UNSIGNED NOT NULL'); // sin doctrine/dbal
        Schema::table('valor_hora_catedra', function (Blueprint $table) {
            $table->unique(['id_carrera', 'tipo', 'vigente_desde']);
            $table->foreign('id_carrera')->references('id_carrera')->on('carrera')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('valor_hora_catedra', function (Blueprint $table) {
            $table->dropForeign(['id_carrera']);
            $table->dropUnique(['id_carrera', 'tipo', 'vigente_desde']);
            $table->dropColumn('id_carrera');
        });
        // Puede haber duplicados por tipo y mes: se deja uno solo.
        DB::statement('DELETE v1 FROM valor_hora_catedra v1 JOIN valor_hora_catedra v2 ON v1.tipo = v2.tipo AND v1.vigente_desde = v2.vigente_desde AND v1.id_valor_hora_catedra > v2.id_valor_hora_catedra');
        Schema::table('valor_hora_catedra', function (Blueprint $table) {
            $table->unique(['tipo', 'vigente_desde']);
        });
    }
};
