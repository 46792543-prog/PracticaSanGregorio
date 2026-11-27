<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 4 (horarios): cada clase de una designación ocupa un módulo de la
 * grilla de la carrera y turno en un día. Si se borra el módulo, se borran sus
 * clases.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horario_asignacion', function (Blueprint $table) {
            $table->foreignId('id_horario_modulo')->nullable()->after('id_asignacion')
                ->constrained('horario_modulo', 'id_horario_modulo')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('horario_asignacion', fn (Blueprint $table) => $table->dropConstrainedForeignId('id_horario_modulo'));
    }
};
