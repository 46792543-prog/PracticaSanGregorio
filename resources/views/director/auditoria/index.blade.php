@extends('layouts.director')

@section('titulo', 'Auditoría')
@section('subtitulo', 'Registro de quién hizo qué en el sistema')

@section('contenido')
    @php
        $estilos = [
            'inscripcion' => ['verde', 'user-plus'],
            'baja_alumno' => ['rojo', 'user-minus'],
            'caja' => ['amarillo', 'banknotes'],
            'documentacion' => ['azul', 'folder'],
            'baja_mesa' => ['rojo', 'x-circle'],
            'acta' => ['violeta', 'document-check'],
            'configuracion' => ['gris', 'settings'],
            'baja_profesor' => ['rojo', 'briefcase'],
            'reactivacion_profesor' => ['verde', 'refresh'],
        ];
    @endphp

    <x-sg.encabezado titulo="Auditoría" subtitulo="Registro de quién hizo qué en el sistema" eyebrow="Control" icono="shield" />

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="clock" /></span>
                <div><h3>Línea de tiempo de acciones</h3><p>Las más recientes primero</p></div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div>
                <label class="sg-label">Tipo</label>
                <select name="tipo" onchange="this.form.submit()" class="sg-input w-auto min-w-[200px]">
                    <option value="">Todos los tipos</option>
                    @foreach ($tipos as $clave => $label)
                        <option value="{{ $clave }}" @selected($filtros['tipo'] === $clave)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Desde</label>
                <input type="date" name="desde" value="{{ $filtros['desde'] }}" placeholder="Desde" class="sg-input w-auto">
            </div>
            <div>
                <label class="sg-label">Hasta</label>
                <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" placeholder="Hasta" class="sg-input w-auto">
            </div>
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Filtrar</button>
            @if ($filtros['tipo'] || $filtros['desde'] || $filtros['hasta'])
                <a href="{{ route('director.auditoria.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <ol class="px-6 py-5 sg-filas">
            @forelse ($eventos as $evento)
                @php [$tono, $icono] = $estilos[$evento['tipo']] ?? ['gris', 'info']; @endphp
                <li class="relative flex gap-4 pb-6 last:pb-1">
                    @unless ($loop->last)
                        <span class="absolute left-[19px] top-11 bottom-0 w-px bg-gradient-to-b from-slate-200 to-slate-100"></span>
                    @endunless
                    <span class="sg-chip sg-chip--{{ $tono }} relative z-10 ring-4 ring-white"><x-sg.icono :nombre="$icono" class="h-[18px] w-[18px]" /></span>
                    <div class="flex-1 min-w-0 rounded-2xl border border-slate-100 bg-white hover:border-blue-200 hover:shadow-sm transition-all px-4 py-3">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <span class="sg-badge sg-badge--{{ $tono }}">{{ $evento['tipoLabel'] }}</span>
                            <span class="text-xs text-slate-400 flex items-center gap-1.5">
                                <x-sg.icono nombre="clock" class="h-3.5 w-3.5" />
                                {{ $evento['fecha']?->format('d/m/Y') }} · {{ $evento['fecha']?->format('H:i') }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-700 mt-2">{{ $evento['descripcion'] }}</p>
                        @if ($evento['actor'])
                            <div class="flex items-center gap-2 mt-2.5">
                                <span class="sg-avatar !h-6 !w-6 !rounded-md !text-[9px]">{{ mb_substr($evento['actor']->nombre, 0, 1) }}{{ mb_substr($evento['actor']->apellido, 0, 1) }}</span>
                                <span class="text-slate-500 text-xs">{{ $evento['actor']->nombre }} {{ $evento['actor']->apellido }}</span>
                                <span class="sg-badge sg-badge--sin-punto !py-0.5 {{ $evento['actor']->usuario?->esDirector() ? 'sg-badge--amarillo' : 'sg-badge--gris' }}">
                                    {{ $evento['actor']->usuario?->esDirector() ? 'Dir.' : 'Sec.' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </li>
            @empty
                <x-sg.vacio icono="shield" titulo="Sin registros">No hay acciones registradas con estos filtros.</x-sg.vacio>
            @endforelse
        </ol>

        @if ($eventos->hasPages())
            <div class="sg-card__foot">{{ $eventos->links() }}</div>
        @endif
    </div>
@endsection
