@extends('layouts.portal')

@section('titulo', 'Inscribirme a materias')
@section('subtitulo', $inscripcionCarrera?->carrera?->nombre_carrera ?? 'Sin carrera asignada')

@section('contenido')
    @if (! $inscripcionCarrera)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 text-sm text-slate-500">
            No tenés una inscripción a carrera activa.
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-5">
                <div>
                    <h2 class="font-bold text-slate-700">Materias disponibles — {{ $inscripcionCarrera->anioCursada?->nombre_anio }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Solo se muestran las materias de tu año que todavía no cursaste, y las de 2do cuatrimestre recién a partir de agosto.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($materias as $item)
                    @php [$materia, $bloqueo] = [$item['materia'], $item['bloqueo']]; @endphp
                    <div class="flex items-center justify-between py-3 gap-3 flex-wrap">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $materia->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $materia->periodo?->nombre_periodo }}</p>
                            @if ($bloqueo)
                                <p class="text-xs text-red-600 mt-1">{{ $bloqueo }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('cursada.store', $materia) }}">
                            @csrf
                            <button type="submit" @disabled($bloqueo)
                                class="rounded-lg text-sm font-semibold px-4 py-2 transition
                                       {{ $bloqueo ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-[#1E4D8C] text-white hover:bg-[#173d70]' }}">
                                Inscribirme
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 py-4">No hay materias disponibles para inscribirte por el momento.</p>
                @endforelse
            </div>
        </div>
    @endif
@endsection
