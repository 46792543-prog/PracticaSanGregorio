<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Nuevo rol del personal: el secretario administrativo entra, igual que el
 * director, tanto al panel de dirección como al de secretaría.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('rol')->where('nombre_rol', 'Secretario Administrativo')->exists()) {
            DB::table('rol')->insert(['nombre_rol' => 'Secretario Administrativo']);
        }
    }

    public function down(): void
    {
        DB::table('rol')->where('nombre_rol', 'Secretario Administrativo')->delete();
    }
};
