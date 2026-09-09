<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profesor', function (Blueprint $table) {
            $table->foreignId('id_secretario_baja')->nullable()->after('activo')
                ->constrained('persona', 'id_persona')->nullOnDelete();
            $table->dateTime('fecha_baja')->nullable()->after('id_secretario_baja');
            $table->foreignId('id_secretario_reactiva')->nullable()->after('fecha_baja')
                ->constrained('persona', 'id_persona')->nullOnDelete();
            $table->dateTime('fecha_reactivacion')->nullable()->after('id_secretario_reactiva');
        });
    }

    public function down(): void
    {
        Schema::table('profesor', function (Blueprint $table) {
            $table->dropConstrainedForeignId('id_secretario_baja');
            $table->dropColumn('fecha_baja');
            $table->dropConstrainedForeignId('id_secretario_reactiva');
            $table->dropColumn('fecha_reactivacion');
        });
    }
};
