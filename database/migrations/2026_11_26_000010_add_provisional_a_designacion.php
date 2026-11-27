<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Designación: se suma el tipo "Provisional" (como en SIGEDOC).
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE asignacion_profesor_materia MODIFY tipo ENUM('Titular', 'Interino', 'Suplente', 'Provisional') NOT NULL DEFAULT 'Titular'");
    }

    public function down(): void
    {
        DB::statement("UPDATE asignacion_profesor_materia SET tipo = 'Interino' WHERE tipo = 'Provisional'");
        DB::statement("ALTER TABLE asignacion_profesor_materia MODIFY tipo ENUM('Titular', 'Interino', 'Suplente') NOT NULL DEFAULT 'Titular'");
    }
};
