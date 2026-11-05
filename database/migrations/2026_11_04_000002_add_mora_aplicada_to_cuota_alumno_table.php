<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marca si a esta cuota ya se le aplicó el recargo por mora automático, para
 * no volver a calcularlo cada vez que se aprieta "Calcular moras".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->boolean('mora_aplicada')->default(false)->after('recargo');
        });
    }

    public function down(): void
    {
        Schema::table('cuota_alumno', function (Blueprint $table) {
            $table->dropColumn('mora_aplicada');
        });
    }
};
