<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('turno_examen', 'mes_desde')) {
            Schema::table('turno_examen', function (Blueprint $table) {
                $table->unsignedTinyInteger('mes_desde')->nullable()->after('nombre_turno');
                $table->unsignedTinyInteger('mes_hasta')->nullable()->after('mes_desde');
            });
        }

        DB::table('turno_examen')->where('nombre_turno', 'Turno Febrero/Marzo')->update(['mes_desde' => 2, 'mes_hasta' => 3]);
        DB::table('turno_examen')->where('nombre_turno', 'Turno Julio')->update(['mes_desde' => 6, 'mes_hasta' => 8]);
        DB::table('turno_examen')->where('nombre_turno', 'Turno Noviembre/Diciembre')->update(['mes_desde' => 11, 'mes_hasta' => 12]);
    }

    public function down(): void
    {
        Schema::table('turno_examen', function (Blueprint $table) {
            $table->dropColumn(['mes_desde', 'mes_hasta']);
        });
    }
};
