<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Amplía la columna "concepto" de cuota_alumno (100) y configuracion_cuota (20)
 * a 120 caracteres. Sin esto, escribir un concepto largo en Configuración de
 * Cuotas tiraría error de la base de datos al guardar, o se cortaría solo.
 */
return new class extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE cuota_alumno MODIFY concepto VARCHAR(120) NULL');
        DB::statement('ALTER TABLE configuracion_cuota MODIFY concepto VARCHAR(120) NULL');
    }

    public function down()
    {
        DB::statement('ALTER TABLE cuota_alumno MODIFY concepto VARCHAR(100) NULL');
        DB::statement('ALTER TABLE configuracion_cuota MODIFY concepto VARCHAR(20) NULL');
    }
};
