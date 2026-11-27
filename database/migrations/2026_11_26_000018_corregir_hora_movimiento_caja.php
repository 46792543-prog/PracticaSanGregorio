<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Movimientos de caja guardados solo con la fecha (hora 00:00): se les pone la
 * hora en que realmente se registraron (created_at), si es del mismo día.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE movimiento_caja
            SET fecha_movimiento = TIMESTAMP(DATE(fecha_movimiento), TIME(created_at))
            WHERE TIME(fecha_movimiento) = '00:00:00' AND created_at IS NOT NULL AND DATE(created_at) = DATE(fecha_movimiento)");
    }

    public function down(): void
    {
        // No se puede saber cuáles estaban en 00:00: no se revierte.
    }
};
