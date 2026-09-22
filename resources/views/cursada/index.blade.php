@extends('layouts.portal')

@section('titulo', 'Inscripción a cursada')
@section('subtitulo', $inscripcionCarrera?->carrera?->nombre_carrera ?? 'Sin carrera asignada')

@section('contenido')
    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    @if (! $inscripcionCarrera)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
            No tenés una inscripción a carrera activa.
        </div>
    @elseif (! $anioLectivo)
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
            No hay un año lectivo activo en este momento.
        </div>
    @elseif ($materias->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
            No hay materias disponibles para inscribirte a cursar en este momento. Secretaría todavía no habilitó ningún período, o ya estás cursando/aprobaste todas las materias de tu año.
        </div>
    @else
        <p class="text-sm text-slate-500 mb-6">Materias disponibles — {{ $inscripcionCarrera->anioCursada?->nombre_anio }} · {{ $anioLectivo->anio }}</p>

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($materias as $item)
                @php [$materia, $bloqueo] = [$item['materia'], $item['bloqueo']]; @endphp
                <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col {{ $bloqueo ? 'opacity-70' : '' }}">
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 rounded-full px-3 py-1 self-start mb-3">
                        {{ $materia->periodo?->nombre_periodo }}
                    </span>
                    <h3 class="font-bold text-slate-800">{{ $materia->nombre }}</h3>

                    <div class="mt-auto pt-4">
                        @if ($bloqueo)
                            <button type="button" disabled class="w-full rounded-lg bg-slate-100 text-slate-400 text-sm font-semibold py-2">
                                No disponible
                            </button>
                            <p class="text-xs text-red-500 mt-2">✕ {{ $bloqueo }}</p>
                        @else
                            <form method="POST" action="{{ route('cursada.inscribir', $materia) }}" onsubmit="return confirm('¿Confirmás la inscripción a cursar {{ $materia->nombre }}?');">
                                @csrf
                                <button type="submit" class="w-full rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2 transition">
                                    Inscribirme a cursar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
