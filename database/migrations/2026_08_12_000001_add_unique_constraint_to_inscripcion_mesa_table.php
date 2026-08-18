<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion_mesa', function (Blueprint $table) {
            $table->unique(['id_mesa', 'id_persona_alumno'], 'inscripcion_mesa_mesa_alumno_unique');
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion_mesa', function (Blueprint $table) {
            $table->dropUnique('inscripcion_mesa_mesa_alumno_unique');
        });
    }
};
