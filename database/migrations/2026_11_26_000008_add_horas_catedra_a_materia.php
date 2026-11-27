<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Etapa 2 (tomado de SIGEDOC): cada materia tiene sus horas cátedra
 * semanales (1 hora cátedra = 40 minutos), su CUPOF (código del cargo) y si
 * es taller/práctica (las horas se pueden repartir entre varios docentes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materia', function (Blueprint $table) {
            $table->unsignedTinyInteger('horas_catedra')->nullable()->after('id_regimen');
            $table->string('cupof', 20)->nullable()->after('horas_catedra');
            $table->boolean('es_taller')->default(false)->after('cupof');
        });
    }

    public function down(): void
    {
        Schema::table('materia', function (Blueprint $table) {
            $table->dropColumn(['horas_catedra', 'cupof', 'es_taller']);
        });
    }
};
