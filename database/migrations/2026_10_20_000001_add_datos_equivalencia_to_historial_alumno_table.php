<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->string('institucion_equivalencia')->nullable()->after('veces_rendida');
            $table->string('resolucion_equivalencia')->nullable()->after('institucion_equivalencia');
        });
    }

    public function down(): void
    {
        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->dropColumn(['institucion_equivalencia', 'resolucion_equivalencia']);
        });
    }
};
