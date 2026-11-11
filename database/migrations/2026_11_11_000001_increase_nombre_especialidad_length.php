<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE especialidad_profesor MODIFY nombre_especialidad VARCHAR(120) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE especialidad_profesor MODIFY nombre_especialidad VARCHAR(45) NOT NULL');
    }
};
