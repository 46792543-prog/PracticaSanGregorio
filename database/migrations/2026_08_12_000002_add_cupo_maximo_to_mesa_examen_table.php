<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mesa_examen', function (Blueprint $table) {
            // Nulo = sin límite de cupo (comportamiento actual conservado por defecto).
            $table->unsignedInteger('cupo_maximo')->nullable()->after('id_estado_mesa');
        });
    }

    public function down(): void
    {
        Schema::table('mesa_examen', function (Blueprint $table) {
            $table->dropColumn('cupo_maximo');
        });
    }
};
