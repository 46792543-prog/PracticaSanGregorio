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
                <p class="text-sm text-slate-400">DNI {{ $alumno->dni }} · {{ $inscripcion?->carrera?->nombre ?? 'Sin carrera asignada' }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.documentacion.show', $alumno) }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-sm font-semibold px-4 py-2">Ver documentación</a>
            @if ($inscripcion && $inscripcion->estado === 'activo')
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
                <div class="flex justify-between"><dt>Email</dt><dd class="text-slate-700">{{ $alumno->email }}</dd></div>
                <div class="flex justify-between"><dt>Teléfono</dt><dd class="text-slate-700">{{ $alumno->telefono ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Fecha nacimiento</dt><dd class="text-slate-700">{{ \App\Support\FechaEsp::corta($alumno->fecha_nacimiento) }}</dd></div>
                <div class="flex justify-between"><dt>Localidad</dt><dd class="text-slate-700">{{ $alumno->localidad ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-700 text-sm mb-3">Situación académica</h3>
            <dl class="text-sm space-y-2 text-slate-500">
                <div class="flex justify-between"><dt>Año que cursa</dt><dd class="text-slate-700">{{ $inscripcion?->anio_actual }}°</dd></div>
                <div class="flex justify-between"><dt>Turno</dt><dd class="text-slate-700 capitalize">{{ $inscripcion?->turno }}</dd></div>
                <div class="flex justify-between"><dt>Condición</dt><dd class="text-slate-700 capitalize">{{ $inscripcion?->condicion }}</dd></div>
                <div class="flex justify-between"><dt>Estado</dt><dd>
                    <span @class(['text-xs font-semibold rounded-full px-2 py-0.5', 'bg-green-100 text-green-700' => $inscripcion?->estado === 'activo', 'bg-red-100 text-red-600' => $inscripcion?->estado === 'baja'])>
                        {{ ucfirst($inscripcion?->estado ?? '—') }}
                    </span>
                </dd></div>
            </dl>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <h3 class="font-bold text-slate-700 text-sm mb-3">Cuotas</h3>
            <dl class="text-sm space-y-2 text-slate-500">
                <div class="flex justify-between"><dt>Pagadas</dt><dd class="text-slate-700">{{ $alumno->cuotas->where('estado', 'pagado')->count() }}</dd></div>
                <div class="flex justify-between"><dt>Pendientes</dt><dd class="text-slate-700">{{ $alumno->cuotas->where('estado', 'pendiente')->count() }}</dd></div>
                <div class="flex justify-between"><dt>Vencidas</dt><dd class="text-red-600 font-semibold">{{ $alumno->cuotas->where('estado', 'vencido')->count() }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="font-bold text-slate-700 mb-4">Historial académico</h3>
        <div class="divide-y divide-slate-100">
            @forelse ($alumno->historialMaterias->sortBy('materia.numero_orden') as $historial)
                <div class="flex items-center justify-between py-2 text-sm">
                    <span class="text-slate-600">{{ $historial->materia->nombre }}</span>
                    <span @class([
                            'text-xs font-semibold rounded-full px-3 py-1',
                            'bg-green-100 text-green-700' => $historial->condicion === 'aprobada',
                            'bg-amber-100 text-amber-700' => $historial->condicion === 'regular',
                            'bg-slate-100 text-slate-500' => $historial->condicion === 'pendiente',
                            'bg-blue-100 text-blue-700' => $historial->condicion === 'cursando',
                        ])>
                        {{ ucfirst($historial->condicion) }} @if($historial->nota_cursada) · {{ $historial->nota_cursada }} @endif
                    </span>
                </div>
            @empty
                <p class="text-sm text-slate-400">Sin historial académico cargado.</p>
            @endforelse
        </div>
    </div>
@endsection
