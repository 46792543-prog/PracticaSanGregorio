@extends('layouts.admin')

@section('titulo', 'Inscribir a materias')
@section('subtitulo', $alumno->apellido . ', ' . $alumno->nombre)

@section('contenido')
    <x-sg.encabezado titulo="Inscribir a materias" :subtitulo="$alumno->apellido . ', ' . $alumno->nombre . ' · ' . $inscripcion->carrera->nombre_carrera" eyebrow="Alumnos · Cursada" icono="cap">
        <a href="{{ route('admin.alumnos.show', $alumno) }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver a la ficha</a>
    </x-sg.encabezado>

    <div class="sg-aviso sg-aviso--alerta mb-6 sg-revelar">
        <x-sg.icono nombre="info" class="h-5 w-5 shrink-0" />
        <span>Los avisos en rojo son solo informativos — secretaría puede inscribir igual en casos excepcionales.</span>
    </div>

    <form method="POST" action="{{ route('admin.alumnos.materias.store', $alumno) }}">
        @csrf
        <div class="grid gap-6 lg:grid-cols-2" data-sg-escalonar>
            @foreach ($materias->groupBy('materia.anioCursada.nombre_anio') as $anio => $items)
                <section class="sg-card overflow-hidden">
                    <header class="sg-card__head">
                        <div class="sg-card__titulo">
                            <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="book" /></span>
                            <h3>{{ $anio }}</h3>
                        </div>
                        <span class="sg-badge sg-badge--gris sg-badge--sin-punto">{{ count($items) }} materias</span>
                    </header>
                    <div class="p-3 space-y-1.5">
                        @foreach ($items as $item)
                            @php [$materia, $bloqueo, $yaCursando] = [$item['materia'], $item['bloqueo'], $item['yaCursando']]; @endphp
                            <label class="flex items-center justify-between gap-3 rounded-xl border border-transparent px-3 py-2.5 transition-colors has-[:checked]:border-[#4FC3F7] has-[:checked]:bg-blue-50 {{ $yaCursando ? 'opacity-50' : 'cursor-pointer hover:bg-slate-50' }}">
                                <span class="flex items-center gap-3 min-w-0">
                                    <input type="checkbox" name="materias[]" value="{{ $materia->id_materia }}"
                                           @disabled($yaCursando)
                                           class="h-4 w-4 rounded border-slate-300 text-[#0B3D91] focus:ring-[#0B3D91]">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-medium text-slate-700">{{ $materia->nombre }}</span>
                                        <span class="block text-xs text-slate-400">{{ $materia->periodo?->nombre_periodo }}</span>
                                    </span>
                                </span>
                                @if ($yaCursando)
                                    <span class="sg-badge sg-badge--gris">Ya tiene: {{ $yaCursando->condicion->nombre_condicion ?? '—' }}</span>
                                @elseif ($bloqueo)
                                    <span class="text-xs text-red-600 text-right max-w-[45%]">{{ $bloqueo }}</span>
                                @endif
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="sticky bottom-4 mt-6 flex justify-end gap-3 rounded-2xl bg-white/90 backdrop-blur border border-slate-200 p-3 shadow-lg sg-revelar">
            <a href="{{ route('admin.alumnos.show', $alumno) }}" class="sg-btn sg-btn--borde">Cancelar</a>
            <button type="submit" class="sg-btn sg-btn--primario">
                <x-sg.icono nombre="check-circle" /> Inscribir a las materias seleccionadas
            </button>
        </div>
    </form>
@endsection
