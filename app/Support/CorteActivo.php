<?php

namespace App\Support;

use App\Models\AnioLectivo;
use Illuminate\Support\Facades\Session;

class CorteActivo
{
    private const SESSION_KEY = 'corte_activo_id';

    public static function actual(): ?AnioLectivo
    {
        $id = Session::get(self::SESSION_KEY);

        return ($id ? AnioLectivo::find($id) : null) ?? self::porDefecto();
    }

    public static function id(): ?int
    {
        return self::actual()?->id_anio_lectivo;
    }

    public static function establecer(int $idAnioLectivo): void
    {
        Session::put(self::SESSION_KEY, $idAnioLectivo);
    }

    private static function porDefecto(): ?AnioLectivo
    {
        return AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'))
            ->orderByDesc('anio')
            ->first()
            ?? AnioLectivo::orderByDesc('anio')->first();
    }
}
