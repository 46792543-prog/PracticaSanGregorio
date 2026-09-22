@extends('layouts.admin')

@section('titulo', 'Inscribir a materias')
@section('subtitulo', $alumno->apellido . ', ' . $alumno->nombre)

@section('contenido')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between flex-wrap gap-2 mb-5">
            <div>
                <h2 class="font-bold text-slate-700">Materias de {{ $inscripcion->carrera->nombre_carrera }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">Los avisos en rojo son solo informativos — secretaría puede inscribir igual en casos excepcionales.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.alumnos.materias.store', $alumno) }}">
            @csrf
            <div class="divide-y divide-slate-100">
                @foreach ($materias->groupBy('materia.anioCursada.nombre_anio') as $anio => $items)
                    <div class="py-3">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wide mb-2">{{ $anio }}</p>
                        @foreach ($items as $item)
                            @php [$materia, $bloqueo, $yaCursando] = [$item['materia'], $item['bloqueo'], $item['yaCursando']]; @endphp
                            <label class="flex items-center justify-between gap-3 py-2 {{ $yaCursando ? 'opacity-50' : '' }}">
                                <span class="flex items-center gap-3">
                                    <input type="checkbox" name="materias[]" value="{{ $materia->id_materia }}"
                                           @disabled($yaCursando)
                                           class="rounded border-slate-300 text-[#1E4D8C] focus:ring-[#1E4D8C]">
                                    <span class="text-sm text-slate-700">{{ $materia->nombre }}</span>
                                    <span class="text-xs text-slate-400">{{ $materia->periodo?->nombre_periodo }}</span>
                                </span>
                                @if ($yaCursando)
                                    <span class="text-xs font-semibold text-slate-500">Ya tiene: {{ $yaCursando->condicion->nombre_condicion ?? '—' }}</span>
                                @elseif ($bloqueo)
                                    <span class="text-xs text-red-600">{{ $bloqueo }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3 mt-5">
                <a href="{{ route('admin.alumnos.show', $alumno) }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Cancelar</a>
                <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow-md hover:bg-[#173d70] transition text-white font-semibold text-sm px-6 py-2.5">
                    Inscribir a las materias seleccionadas
                </button>
            </div>
        </form>
    </div>
@endsection
