<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Libro de Caja: "gastos" pasan a ser "egresos" con categoría.
 *  - Las categorías de egreso son conceptos de caja de tipo Egreso (misma tabla de siempre).
 *  - Nuevo campo beneficiario / proveedor en cada movimiento.
 *  - Los egresos manuales viejos (un concepto por texto escrito) pasan a su categoría;
 *    el texto que tenían queda en el detalle (no se pierde nada).
 *  - "Pago a docente" pasa a llamarse "Pago de honorarios docentes".
 *  - Pagos a docentes ya pagados: cada uno queda con su egreso en la caja (se vinculan
 *    los que estaban sueltos y se crean los que faltaban), sin duplicar.
 */
return new class extends Migration
{
    private const CATEGORIAS = [
        'Compra de materiales de librería',
        'Artículos de limpieza',
        'Servicios (luz, agua, internet)',
        'Mantenimiento y reparaciones',
        'Pago de honorarios docentes',
        'Compra de mobiliario',
        'Otros',
    ];

    public function up(): void
    {
        Schema::table('movimiento_caja', function (Blueprint $table) {
            $table->string('beneficiario', 100)->nullable()->after('descripcion_detalle');
        });

        $ahora = now();
        $egreso = DB::table('tipo_movimiento')->where('nombre_tipo', 'Egreso')->value('id_tipo_movimiento')
            ?? DB::table('tipo_movimiento')->insertGetId(['nombre_tipo' => 'Egreso', 'created_at' => $ahora, 'updated_at' => $ahora]);

        // "Pago a docente" se renombra (conserva sus movimientos).
        if (! DB::table('concepto_caja')->where('nombre_concepto', 'Pago de honorarios docentes')->exists()) {
            DB::table('concepto_caja')->where('nombre_concepto', 'Pago a docente')->update(['nombre_concepto' => 'Pago de honorarios docentes', 'updated_at' => $ahora]);
        }
        foreach (self::CATEGORIAS as $nombre) {
            if (! DB::table('concepto_caja')->where('nombre_concepto', $nombre)->exists()) {
                DB::table('concepto_caja')->insert(['nombre_concepto' => $nombre, 'id_tipo_movimiento' => $egreso, 'created_at' => $ahora, 'updated_at' => $ahora]);
            }
        }
        $categorias = DB::table('concepto_caja')->whereIn('nombre_concepto', self::CATEGORIAS)->pluck('id_concepto', 'nombre_concepto');

        // Egresos manuales viejos: a su categoría, con el texto original en el detalle.
        $viejos = DB::table('movimiento_caja as m')->join('concepto_caja as c', 'c.id_concepto', '=', 'm.id_concepto')
            ->where('c.id_tipo_movimiento', $egreso)->whereNotIn('c.nombre_concepto', self::CATEGORIAS)
            ->select('m.id_movimiento', 'm.descripcion_detalle', 'c.nombre_concepto')->get();
        foreach ($viejos as $m) {
            $categoria = str_contains(mb_strtolower($m->nombre_concepto), 'internet') ? 'Servicios (luz, agua, internet)' : 'Otros';
            DB::table('movimiento_caja')->where('id_movimiento', $m->id_movimiento)->update([
                'id_concepto' => $categorias[$categoria],
                'descripcion_detalle' => $m->descripcion_detalle ? "{$m->nombre_concepto} — {$m->descripcion_detalle}" : $m->nombre_concepto,
            ]);
        }

        // Pagos a docentes ya pagados: cada uno con su egreso (vincular sueltos o crear los que faltan).
        $honorarios = $categorias['Pago de honorarios docentes'];
        $pagos = DB::table('pago_docente as p')
            ->join('estado_pago_docente as e', 'e.id_estado_pago_docente', '=', 'p.id_estado_pago_docente')
            ->join('profesor as pr', 'pr.id_profesor', '=', 'p.id_profesor')
            ->join('persona as pe', 'pe.id_persona', '=', 'pr.id_persona')
            ->whereRaw('LOWER(e.nombre_estado) = ?', ['pagado'])
            ->select('p.*', 'pe.apellido', 'pe.nombre')->get();
        $meses = [1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        foreach ($pagos as $p) {
            $beneficiario = "{$p->apellido}, {$p->nombre}";
            $periodo = $meses[(int) substr($p->periodo, 5, 2)] . ' ' . substr($p->periodo, 0, 4);
            $vinculado = $p->id_movimiento_caja && DB::table('movimiento_caja')->where('id_movimiento', $p->id_movimiento_caja)->exists();

            if (! $vinculado) {
                // ¿Hay un egreso de docente suelto con ese docente, período y monto? Se vincula en vez de duplicar.
                $suelto = DB::table('movimiento_caja')->where('id_concepto', $honorarios)->where('monto', $p->monto)
                    ->where('descripcion_detalle', 'like', "%{$p->apellido}, {$p->nombre}%{$periodo}%")
                    ->whereNotIn('id_movimiento', DB::table('pago_docente')->whereNotNull('id_movimiento_caja')->pluck('id_movimiento_caja'))
                    ->value('id_movimiento');

                $idMovimiento = $suelto ?? DB::table('movimiento_caja')->insertGetId([
                    'id_concepto' => $honorarios,
                    'monto' => $p->monto,
                    'fecha_movimiento' => substr($p->fecha_pago, 0, 10) . ' ' . substr((string) $p->created_at, 11, 8),
                    'id_secretario_registra' => $p->id_director_registra ?? DB::table('movimiento_caja')->value('id_secretario_registra'),
                    'id_medio_pago' => DB::table('medio_pago')->where('nombre_medio', 'Efectivo')->value('id_medio_pago') ?? 1,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ]);
                DB::table('pago_docente')->where('id_pago_docente', $p->id_pago_docente)->update(['id_movimiento_caja' => $idMovimiento]);
                $p->id_movimiento_caja = $idMovimiento;
            }

            // Mismo formato para todos: docente como beneficiario y el período en el detalle.
            DB::table('movimiento_caja')->where('id_movimiento', $p->id_movimiento_caja)->update([
                'id_concepto' => $honorarios,
                'beneficiario' => $beneficiario,
                'descripcion_detalle' => "Honorarios de {$beneficiario} — período {$periodo}",
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('movimiento_caja', function (Blueprint $table) {
            $table->dropColumn('beneficiario');
        });
        DB::table('concepto_caja')->where('nombre_concepto', 'Pago de honorarios docentes')->update(['nombre_concepto' => 'Pago a docente']);
    }
};
