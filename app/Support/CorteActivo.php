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

    /**
     * Sin uno elegido: el año activo del año calendario en curso. Si se abrió el ciclo
     * siguiente por adelantado (ej. 2027 activo en octubre de 2026), no se lo toma por
     * defecto porque todavía no tiene cuotas, cobros ni alumnos cursando.
     */
    private static function porDefecto(): ?AnioLectivo
    {
        $activos = AnioLectivo::whereHas('estadoAnio', fn ($q) => $q->where('nombre_estado', 'Activo'));

        return (clone $activos)->where('anio', now()->year)->first()
            ?? (clone $activos)->where('anio', '<=', now()->year)->orderByDesc('anio')->first()
            ?? (clone $activos)->orderBy('anio')->first()
            ?? AnioLectivo::where('anio', '<=', now()->year)->orderByDesc('anio')->first()
            ?? AnioLectivo::orderByDesc('anio')->first();
    }
}
