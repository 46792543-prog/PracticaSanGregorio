@extends('layouts.portal')

@section('titulo', 'Mi estado académico')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    @php
        $badges = [
            'aprobada' => 'bg-green-100 text-green-700',
            'regular' => 'bg-amber-100 text-amber-700',
            'pendiente' => 'bg-slate-100 text-slate-500',
            'no_disponible' => 'bg-red-100 text-red-600',
        ];
        $etiquetas = [
            'aprobada' => 'Aprobada',
            'regular' => 'Regular',
            'pendiente' => 'Pendiente',
            'no_disponible' => 'No disponible',
        ];
    @endphp

    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        @if ($inscripcionCarrera)
            <p class="text-sm font-semibold text-slate-600 mb-3">
                {{ $inscripcionCarrera->carrera->nombre }} · Plan 2024
            </p>
        @endif
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar materia..."
                   class="flex-1 rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <select name="condicion" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">Todas las condiciones</option>
                @foreach ($etiquetas as $valor => $texto)
                    <option value="{{ $valor }}" @selected($condicionFiltro === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-[#16305a] text-white text-sm font-semibold px-5 py-2">Buscar</button>
        </form>
    </div>

    @forelse ($materiasPorAnio as $anio => $materias)
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-block h-4 w-1 bg-[#16305a] rounded"></span>
                {{ $nombresAnio[$anio] ?? "{$anio}° Año" }} — {{ $materias->count() }} materias
                @if ($inscripcionCarrera && (int) $inscripcionCarrera->anio_actual === (int) $anio)
                    <span class="text-xs font-normal text-amber-500">(año en curso)</span>
                @endif
            </h3>

            <div class="divide-y divide-slate-100">
                @foreach ($materias as $materia)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-slate-700 text-sm">{{ $materia->nombre }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ \Illuminate\Support\Str::of($materia->cuatrimestre)->replace('_', ' ')->ucfirst() }}
                                · {{ match ($materia->regimen) {
                                    'solo_promocion' => 'Promoción',
                                    'solo_examen_final' => 'Examen Final',
                                    default => 'Promoción / Examen Final',
                                } }}
                                @if ($materia->nota_alumno)
                                    · Nota: {{ number_format($materia->nota_alumno, 2) }}
                                @elseif ($materia->condicion_alumno === 'cursando' || $materia->condicion_alumno === 'regular')
                                    · Cursando
                                @endif
                            </p>
                        </div>
                        <span class="shrink-0 text-xs font-semibold rounded-full px-3 py-1 {{ $badges[$materia->estado_visual] ?? $badges['pendiente'] }}">
                            {{ $etiquetas[$materia->estado_visual] ?? ucfirst($materia->estado_visual) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
            No se encontraron materias con ese criterio.
        </div>
    @endforelse
@endsection
