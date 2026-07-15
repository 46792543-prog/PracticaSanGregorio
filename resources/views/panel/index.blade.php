@extends('layouts.portal')

@section('titulo', 'Hola, ' . $alumno->nombre . ' 👋')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    @if ($inscripcionCarrera)
        <div class="bg-[#16305a] text-white rounded-2xl p-6 md:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 mb-6">
            <div>
                <h2 class="text-lg font-bold">{{ $inscripcionCarrera->carrera->nombre }}</h2>
                <p class="text-blue-200 text-sm mt-1">
                    {{ $inscripcionCarrera->anio_actual }}° Año · Plan {{ $inscripcionCarrera->carrera->materias->first()?->version_plan ?? '2024' }}
                    · Condición: <span class="capitalize">{{ $inscripcionCarrera->condicion }}</span>
                </p>
            </div>
            <div class="flex items-center gap-4 shrink-0">
                <div class="relative h-20 w-20 rounded-full grid place-items-center"
                     style="background: conic-gradient(#fbbf24 {{ $porcentaje }}%, rgba(255,255,255,.15) 0)">
                    <div class="h-14 w-14 rounded-full bg-[#16305a] grid place-items-center text-sm font-bold">
                        {{ $porcentaje }}%
                    </div>
                </div>
                <p class="text-sm text-blue-100 leading-tight">
                    {{ $materiasAprobadas }} de {{ $totalMaterias }}<br>materias aprobadas
                </p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase">Materias aprobadas</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $materiasAprobadas }}<span class="text-base text-slate-400">/{{ $totalMaterias }}</span></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase">Mesas disponibles</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $mesasDisponibles }}</p>
            <p class="text-xs text-slate-400 mt-1">Inscripción abierta</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase">Inscripciones activas</p>
            <p class="text-2xl font-bold text-slate-800 mt-1">{{ $inscripcionesActivas }}</p>
            <p class="text-xs {{ $inscripcionesActivas > 0 ? 'text-amber-500' : 'text-slate-400' }} mt-1">
                {{ $inscripcionesActivas > 0 ? 'Pendiente de aprobación' : 'Sin pendientes' }}
            </p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase">Cuotas</p>
            <p class="text-2xl font-bold {{ $cuotaVencida ? 'text-red-600' : 'text-green-600' }} mt-1">
                {{ $cuotaVencida ? 'Vencida' : 'Al día' }}
            </p>
            <p class="text-xs text-slate-400 mt-1">
                @if ($proximaCuota)
                    {{ $proximaCuota->concepto }} pendiente
                @else
                    Sin cuotas pendientes
                @endif
            </p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4">Próximas mesas de examen</h3>
            <div class="space-y-3">
                @forelse ($proximasMesas as $item)
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $item['materia']->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ \App\Support\FechaEsp::corta($item['mesa']->fecha_examen) }} · Turno {{ ucwords(str_replace('_', '/', $item['mesa']->turno), ' /') }}</p>
                        </div>
                        @if ($item['bloqueo'])
                            <span class="text-xs font-semibold text-red-600 bg-red-50 rounded-full px-3 py-1">✕ Falta correlativa</span>
                        @else
                            <span class="text-xs font-semibold text-green-600 bg-green-50 rounded-full px-3 py-1">✓ Habilitada</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400">No tenés mesas próximas.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-bold text-slate-800 mb-4">Actividad reciente</h3>
            <div class="space-y-4">
                @forelse ($actividad as $evento)
                    <div class="flex gap-3">
                        <span class="mt-1.5 h-2 w-2 rounded-full bg-amber-400 shrink-0"></span>
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $evento['titulo'] }}</p>
                            <p class="text-xs text-slate-400">{{ $evento['detalle'] }} · {{ \App\Support\FechaEsp::corta(\Illuminate\Support\Carbon::parse($evento['fecha'])) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Todavía no hay actividad registrada.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
