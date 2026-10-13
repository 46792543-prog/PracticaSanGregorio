<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->unsignedTinyInteger('veces_rendida')->nullable()->after('nota_cursada');
        });
    }

    public function down(): void
    {
        Schema::table('historial_alumno', function (Blueprint $table) {
            $table->dropColumn('veces_rendida');
        });
    }
};
