<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Condición de materia "Final Desaprobado": el alumno tenía la cursada
 * aprobada (Regular), rindió el final y sacó menos de 4. Sigue regular:
 * puede volver a rendir (hasta que supere las veces permitidas o se le
 * venza la regularidad).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('condicion_alumno')->where('nombre_condicion', 'Final Desaprobado')->exists()) {
            return;
        }

        DB::table('condicion_alumno')->insert(array_merge(
            ['nombre_condicion' => 'Final Desaprobado'],
            Schema::hasColumn('condicion_alumno', 'created_at') ? ['created_at' => now(), 'updated_at' => now()] : []
        ));
    }

    public function down(): void
    {
        DB::table('condicion_alumno')->where('nombre_condicion', 'Final Desaprobado')->delete();
    }
};
