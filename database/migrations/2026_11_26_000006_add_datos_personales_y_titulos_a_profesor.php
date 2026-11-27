<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha del docente (etapa 1, tomando como modelo SIGEDOC):
 *  - CUIL (validado), sexo y fecha de ingreso (para la antigüedad).
 *  - Condición: se suma "Interino" a Titular y Suplente.
 *  - Títulos: varios por docente, cada uno con su nivel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profesor', function (Blueprint $table) {
            $table->string('cuil', 11)->nullable()->unique()->after('id_persona');
            $table->string('sexo', 1)->nullable()->after('cuil');              // F, M o X
            $table->date('fecha_ingreso')->nullable()->after('sexo');
        });

        DB::statement("ALTER TABLE profesor MODIFY condicion ENUM('Titular', 'Interino', 'Suplente') NOT NULL DEFAULT 'Titular'");

        Schema::create('titulo_profesor', function (Blueprint $table) {
            $table->id('id_titulo');
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor')->cascadeOnDelete();
            $table->string('nombre', 150);
            $table->string('nivel', 20);                                         // Secundario, Terciario, Universitario, Posgrado
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('titulo_profesor');
        DB::statement("UPDATE profesor SET condicion = 'Suplente' WHERE condicion = 'Interino'");
        DB::statement("ALTER TABLE profesor MODIFY condicion ENUM('Titular', 'Suplente') NOT NULL DEFAULT 'Titular'");

        Schema::table('profesor', function (Blueprint $table) {
            $table->dropUnique(['cuil']);
            $table->dropColumn(['cuil', 'sexo', 'fecha_ingreso']);
        });
    }
};
