<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE inscripcion_carrera MODIFY id_turno_cursada BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE inscripcion_carrera MODIFY id_turno_cursada BIGINT UNSIGNED NOT NULL');
    }
};
