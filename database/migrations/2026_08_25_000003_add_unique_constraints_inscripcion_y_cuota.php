<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->indiceExiste('inscripcion_carrera', 'inscripcion_carrera_persona_carrera_unique')) {
            Schema::table('inscripcion_carrera', function (Blueprint $table) {
                $table->unique(['id_persona_alumno', 'id_carrera'], 'inscripcion_carrera_persona_carrera_unique');
            });
        }

        if (! $this->indiceExiste('cuota_alumno', 'cuota_alumno_persona_anio_mes_unique')) {
            Schema::table('cuota_alumno', function (Blueprint $table) {
                $table->unique(['id_persona_alumno', 'id_anio_lectivo', 'id_mes'], 'cuota_alumno_persona_anio_mes_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('inscripcion_carrera', function (Blueprint $table) {
            $table->dropUnique('inscripcion_carrera_persona_carrera_unique');
        });

        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->dropUnique('cuota_alumno_persona_anio_mes_unique');
        });
    }

    private function indiceExiste(string $tabla, string $nombreIndice): bool
    {
        return DB::select("SHOW INDEX FROM {$tabla} WHERE Key_name = ?", [$nombreIndice]) !== [];
    }
};
