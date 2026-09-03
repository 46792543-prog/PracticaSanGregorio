<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE acta MODIFY libro VARCHAR(20) NULL');
        DB::statement('ALTER TABLE acta MODIFY folio VARCHAR(20) NULL');

        DB::table('acta')->where('libro', '')->update(['libro' => null]);
        DB::table('acta')->where('folio', '')->update(['folio' => null]);
    }

    public function down(): void
    {
        DB::table('acta')->whereNull('libro')->update(['libro' => '']);
        DB::table('acta')->whereNull('folio')->update(['folio' => '']);

        DB::statement("ALTER TABLE acta MODIFY libro VARCHAR(20) NOT NULL");
        DB::statement("ALTER TABLE acta MODIFY folio VARCHAR(20) NOT NULL");
    }
};
