<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El modal de "Nuevo título" dejó de pedir el nivel, así que la columna no
 * puede seguir siendo NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE titulo MODIFY nivel VARCHAR(20) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE titulo MODIFY nivel VARCHAR(20) NOT NULL DEFAULT 'Terciario'");
    }
};
