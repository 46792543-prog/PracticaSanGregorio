<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profesor_especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_profesor')->constrained('profesor', 'id_profesor')->cascadeOnDelete();
            $table->foreignId('id_especialidad')->constrained('especialidad_profesor', 'id_especialidad')->cascadeOnDelete();
            $table->unique(['id_profesor', 'id_especialidad']);
            $table->timestamps();
        });

        // Cada profesor ya tenía exactamente una especialidad (columna profesor.id_especialidad);
        // esa relación se traslada tal cual a la tabla pivote antes de borrar la columna vieja.
        DB::statement('
            INSERT INTO profesor_especialidad (id_profesor, id_especialidad, created_at, updated_at)
            SELECT id_profesor, id_especialidad, NOW(), NOW() FROM profesor
        ');

        Schema::table('profesor', function (Blueprint $table) {
            $table->dropForeign(['id_especialidad']);
            $table->dropColumn('id_especialidad');
        });
    }

    public function down(): void
    {
        Schema::table('profesor', function (Blueprint $table) {
            $table->foreignId('id_especialidad')->nullable()->after('id_persona')
                ->constrained('especialidad_profesor', 'id_especialidad');
        });

        // Al volver atrás, cada profesor recupera una sola especialidad (la de id más bajo
        // si llegó a tener varias cargadas durante el tiempo que estuvo la tabla pivote).
        DB::statement('
            UPDATE profesor p
            JOIN (
                SELECT id_profesor, MIN(id_especialidad) AS id_especialidad
                FROM profesor_especialidad
                GROUP BY id_profesor
            ) pe ON pe.id_profesor = p.id_profesor
            SET p.id_especialidad = pe.id_especialidad
        ');

        Schema::dropIfExists('profesor_especialidad');
    }
};
