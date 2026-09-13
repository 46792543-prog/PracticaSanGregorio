<?php

namespace App\Http\Controllers;

use App\Models\AnioLectivo;
use App\Support\CorteActivo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CorteActivoController extends Controller
{
    public function seleccionar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_anio_lectivo' => ['required', 'exists:anio_lectivo,id_anio_lectivo'],
        ]);

        CorteActivo::establecer((int) $data['id_anio_lectivo']);

        $anio = AnioLectivo::find($data['id_anio_lectivo'])->anio;

        return back()->with('status', "Corte activo cambiado a {$anio}.");
    }
}
