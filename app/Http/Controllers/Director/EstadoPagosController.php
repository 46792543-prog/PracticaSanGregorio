<?php

namespace App\Http\Controllers\Director;

use App\Exports\EstadoPagosExport;
use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\CuotaAlumno;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class EstadoPagosController extends Controller
{
    public function index(Request $request): View
    {
        $totalAlumnos = Persona::whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))->count();
        $alumnosConDeuda = CuotaAlumno::where('pagado', false)->distinct('id_persona_alumno')->count('id_persona_alumno');
        $alumnosAlDia = max(0, $totalAlumnos - $alumnosConDeuda);
        $montoAdeudadoTotal = (float) (CuotaAlumno::where('pagado', false)->selectRaw('SUM(monto + recargo) as total')->value('total') ?? 0);
        $cuotasVencidas = CuotaAlumno::where('pagado', false)
            ->whereNotNull('fecha_vencimiento')
            ->where('fecha_vencimiento', '<', now())
            ->count();

        $alumnos = $this->consultaAlumnos($request)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('director.pagos.index', [
            'busqueda' => trim((string) $request->query('q')),
            'carreraId' => $request->query('carrera'),
            'estado' => $request->query('estado'),
            'carreras' => Carrera::orderBy('nombre_carrera')->get(),
            'alumnos' => $alumnos,
            'totalAlumnos' => $totalAlumnos,
            'alumnosAlDia' => $alumnosAlDia,
            'alumnosConDeuda' => $alumnosConDeuda,
            'montoAdeudadoTotal' => $montoAdeudadoTotal,
            'cuotasVencidas' => $cuotasVencidas,
        ]);
    }

    public function excel(Request $request)
    {
        $alumnos = $this->consultaAlumnos($request)
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return Excel::download(new EstadoPagosExport($alumnos), 'estado-de-pagos-' . now()->format('Y-m-d') . '.xlsx');
    }

    private function consultaAlumnos(Request $request): Builder
    {
        $busqueda = trim((string) $request->query('q'));
        $carreraId = $request->query('carrera');
        $estado = $request->query('estado');

        return Persona::query()
            ->whereHas('usuario.rol', fn ($q) => $q->where('nombre_rol', 'Alumno'))
            ->when($busqueda, fn ($q) => $q->where(fn ($w) => $w->where('nombre', 'like', "%{$busqueda}%")
                ->orWhere('apellido', 'like', "%{$busqueda}%")
                ->orWhere('dni', 'like', "%{$busqueda}%")))
            ->when($carreraId, fn ($q) => $q->whereHas('inscripcionesCarrera', fn ($w) => $w->where('id_carrera', $carreraId)))
            ->with(['inscripcionesCarrera' => fn ($q) => $q->latest('fecha_inscripcion')->with('carrera')])
            ->withCount([
                'cuotas as cuotas_pagadas_count' => fn ($q) => $q->where('pagado', true),
                'cuotas as cuotas_pendientes_count' => fn ($q) => $q->where('pagado', false),
            ])
            ->withSum(['cuotas as monto_pendiente' => fn ($q) => $q->where('pagado', false)], 'monto')
            ->withSum(['cuotas as recargo_pendiente' => fn ($q) => $q->where('pagado', false)], 'recargo')
            ->when($estado === 'al_dia', fn ($q) => $q->having('cuotas_pendientes_count', 0))
            ->when($estado === 'debe', fn ($q) => $q->having('cuotas_pendientes_count', '>', 0));
    }
}
