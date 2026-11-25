<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mesa extraordinaria: solo pueden anotarse los alumnos con condición
 * "Cursada completa" (ya terminaron de cursar y deben finales).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesa_examen', function (Blueprint $table) {
            $table->boolean('extraordinaria')->default(false)->after('cupo_maximo');
        });
    }

    public function down(): void
    {
        Schema::table('mesa_examen', function (Blueprint $table) {
            $table->dropColumn('extraordinaria');
        });
    }
};
