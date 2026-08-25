<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('historial_alumno', 'anios_plazo_regularidad')) {
            return;
        }

        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->unsignedTinyInteger('anios_plazo_regularidad')->nullable()->after('fecha_ultima_modificacion');
        });
    }

    public function down(): void
    {
        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->dropColumn('anios_plazo_regularidad');
        });
    }
};
