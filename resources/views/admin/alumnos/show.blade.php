@extends('layouts.admin')

@section('titulo', 'Ficha del alumno')

@section('contenido')
    @php $inscripcion = $alumno->inscripcionesCarrera->first(); @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6 flex items-center justify-between flex-wrap gap-4">
        <div class="flex items-center gap-4">
            <span class="h-14 w-14 rounded-full bg-blue-100 text-blue-700 text-lg font-bold grid place-items-center">
                {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
            </span>
            <div>
                <h1 class="font-bold text-lg text-slate-800">{{ $alumno->apellido }}, {{ $alumno->nombre }}</h1>
                <p class="text-sm text-slate-400">DNI {{ $alumno->dni }} · {{ $inscripcion?->carrera?->nombre_carrera ?? 'Sin carrera asignada' }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.documentacion.show', $alumno) }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-sm font-semibold px-4 py-2">Ver documentación</a>
            @if ($inscripcion && $inscripcion->estadoInscripcion->nombre_estado === 'Activo')
                <form method="POST" action="{{ route('admin.alumnos.baja', $alumno) }}" onsubmit="return confirm('¿Confirmás dar de baja a este alumno?');">
                    @csrf @method('PUT')
                    <button type="submit" class="rounded-lg bg-red-50 text-red-600 text-sm font-semibold px-4 py-2">Dar de baja</button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-700 text-sm mb-3">Datos personales</h3>
            <dl class="text-sm space-y-2 text-slate-500">
                <div class="flex justify-between"><dt>Email</dt><dd class="text-slate-700">{{ $alumno->usuario->email ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Teléfono</dt><dd class="text-slate-700">{{ $alumno->telefono ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Fecha nacimiento</dt><dd class="text-slate-700">{{ \App\Support\FechaEsp::corta($alumno->fecha_nacimiento) }}</dd></div>
                <div class="flex justify-between"><dt>Localidad</dt><dd class="text-slate-700">{{ $alumno->localidad ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-700 text-sm mb-3">Situación académica</h3>
            <dl class="text-sm space-y-2 text-slate-500">
                <div class="flex justify-between"><dt>Año que cursa</dt><dd class="text-slate-700">{{ $inscripcion?->anioCursada?->nombre_anio ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Turno</dt><dd class="text-slate-700">{{ $inscripcion?->turnoCursada?->nombre_turno ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Condición</dt><dd class="text-slate-700">{{ $inscripcion?->condicion?->nombre_condicion ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Estado</dt><dd>
                    @php $estadoInscNombre = $inscripcion?->estadoInscripcion?->nombre_estado; @endphp
                    <span @class(['text-xs font-semibold rounded-full px-2 py-0.5', 'bg-green-100 text-green-700' => $estadoInscNombre === 'Activo', 'bg-red-100 text-red-600' => $estadoInscNombre === 'Baja'])>
                        {{ $estadoInscNombre ?? '—' }}
                    </span>
                </dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-700 text-sm mb-3">Cuotas</h3>
            <dl class="text-sm space-y-2 text-slate-500">
                <div class="flex justify-between"><dt>Pagadas</dt><dd class="text-slate-700">{{ $alumno->cuotas->where('pagado', true)->count() }}</dd></div>
                <div class="flex justify-between"><dt>Pendientes</dt><dd class="text-slate-700">{{ $alumno->cuotas->where('pagado', false)->count() }}</dd></div>
                <div class="flex justify-between"><dt>Vencidas</dt><dd class="text-red-600 font-semibold">{{ $alumno->cuotas->filter(fn ($c) => $c->vencida)->count() }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="font-bold text-slate-700 mb-4">Historial académico</h3>
        <div class="divide-y divide-slate-100">
            @forelse ($alumno->historialAlumno->sortBy('materia.nombre') as $historial)
                @php $condicionNombre = $historial->condicion->nombre_condicion ?? '—'; @endphp
                <div class="flex items-center justify-between py-2 text-sm gap-3">
                    <span class="text-slate-600">{{ $historial->materia->nombre }}</span>
                    <div class="flex items-center gap-3">
                        @if ($condicionNombre === 'Regular')
                            <form method="POST" action="{{ route('admin.alumnos.historial.plazo', [$alumno, $historial]) }}" class="flex items-center gap-1.5">
                                @csrf @method('PUT')
                                <label class="text-[11px] text-slate-400">Plazo para rendir:</label>
                                <select name="anios_plazo_regularidad" class="text-xs rounded-lg border border-slate-300 px-2 py-1 focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30">
                                    <option value="" @selected(! $historial->anios_plazo_regularidad)>Sin límite</option>
                                    @foreach ([1, 2, 3] as $anios)
                                        <option value="{{ $anios }}" @selected($historial->anios_plazo_regularidad == $anios)>{{ $anios }} {{ $anios === 1 ? 'año' : 'años' }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-xs font-semibold text-[#1E4D8C] hover:underline">Guardar</button>
                                @if ($historial->fecha_limite_calculada)
                                    <span class="text-[11px] text-slate-400">(vence {{ \App\Support\FechaEsp::corta($historial->fecha_limite_calculada) }})</span>
                                @endif
                            </form>
                        @endif
                        <span @class([
                                'text-xs font-semibold rounded-full px-3 py-1 whitespace-nowrap',
                                'bg-green-100 text-green-700' => $condicionNombre === 'Aprobada',
                                'bg-amber-100 text-amber-700' => $condicionNombre === 'Regular' && ! $historial->regularidad_vencida,
                                'bg-red-100 text-red-600' => $historial->regularidad_vencida,
                                'bg-slate-100 text-slate-500' => $condicionNombre === 'Pendiente',
                                'bg-blue-100 text-blue-700' => $condicionNombre === 'Cursando',
                            ])>
                            @if ($historial->regularidad_vencida)
                                Venció el {{ \App\Support\FechaEsp::corta($historial->fecha_limite_calculada) }}
                            @else
                                {{ $condicionNombre }} @if($historial->nota_cursada) · {{ $historial->nota_cursada }} @endif
                            @endif
                        </span>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Sin historial académico cargado.</p>
            @endforelse
        </div>
    </div>
@endsection
