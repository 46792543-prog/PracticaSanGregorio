<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inscripcion_carrera', function (Blueprint $table) {
            $table->foreignId('id_anio_cursada')->nullable()->after('id_carrera')
                ->constrained('anio_cursada', 'id_anio_cursada');
        });
    }

    public function down(): void
    {
        Schema::table('inscripcion_carrera', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_anio_cursada');
        });
    }
};
