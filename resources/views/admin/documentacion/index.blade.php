@extends('layouts.admin')

@section('titulo', 'Documentación requerida')

@section('contenido')
    <x-sg.encabezado titulo="Documentación requerida" subtitulo="Revisión de la documentación entregada por los alumnos" eyebrow="Documentación" icono="folder">
        <a href="{{ route('admin.documentacion.requisitos') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="settings" /> Requisitos</a>
    </x-sg.encabezado>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" data-sg-escalonar>
        <x-sg.stat etiqueta="Alumnos totales" :valor="$totales['total']" icono="users" tono="azul" />
        <x-sg.stat etiqueta="Pendientes" :valor="$totales['pendientes']" icono="alert" tono="rojo" />
        <x-sg.stat etiqueta="En revisión" :valor="$totales['en_revision']" icono="clock" tono="amarillo" />
        <x-sg.stat etiqueta="Completos" :valor="$totales['completos']" icono="check-circle" tono="verde" />
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="flex flex-wrap items-center gap-3 p-4 border-b border-slate-100 bg-slate-50/60">
            <form method="GET" class="flex-1 min-w-[220px] flex gap-2">
                <div class="sg-campo-icono flex-1">
                    <x-sg.icono nombre="search" />
                    <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar alumno..." maxlength="25"
                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                           class="sg-input w-full">
                </div>
                <input type="hidden" name="filtro" value="{{ $filtro }}">
                <button type="submit" class="sg-btn sg-btn--azul whitespace-nowrap">Buscar</button>
            </form>
            <div class="inline-flex gap-1 rounded-xl bg-white border border-slate-200 p-1">
                @foreach (['todos' => 'Todos', 'pendientes' => 'Pendientes', 'completos' => 'Completos'] as $valor => $texto)
                    <a href="{{ route('admin.documentacion.index', ['filtro' => $valor, 'q' => $busqueda]) }}"
                       class="text-sm font-semibold px-4 py-1.5 rounded-lg transition-all {{ $filtro === $valor ? 'bg-[#0B3D91] text-white shadow-sm' : 'text-slate-500 hover:text-[#0B3D91]' }}">
                        {{ $texto }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Documentos</th>
                    <th>Estado</th>
                    <th class="text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($alumnos as $alumno)
                    @php
                        $inscripcion = $alumno->inscripcionesCarrera->first();
                        $badges = ['Pendiente' => 'bg-red-50 text-red-600 ring-red-100', 'Entregado' => 'bg-amber-50 text-amber-700 ring-amber-100', 'Aprobado' => 'bg-emerald-50 text-emerald-700 ring-emerald-100', 'Rechazado' => 'bg-red-50 text-red-600 ring-red-100'];
                        $estadoTexto = ['pendiente' => 'Pendiente', 'revision' => 'En revisión', 'sin_enviar' => 'Sin enviar', 'completo' => 'Completo'];
                        $estadoBadge = ['pendiente' => 'sg-badge--amarillo', 'revision' => 'sg-badge--azul', 'sin_enviar' => 'sg-badge--rojo', 'completo' => 'sg-badge--verde'];
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="sg-avatar">{{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $alumno->nombre }} {{ $alumno->apellido }}</p>
                                    <p class="text-xs text-slate-400">{{ $inscripcion?->anioCursada?->nombre_anio }} · {{ $inscripcion?->carrera?->nombre }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex gap-1.5 flex-wrap">
                                @foreach ($alumno->documentacion as $doc)
                                    @php $nombreEstado = $doc->estadoDocumento->nombre_estado ?? 'Pendiente'; @endphp
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-1 rounded-lg ring-1 {{ $badges[$nombreEstado] ?? 'bg-slate-50 text-slate-500 ring-slate-100' }}" title="{{ $doc->documentoRequisito->nombre }}">
                                        <x-sg.icono :nombre="$nombreEstado === 'Aprobado' ? 'check' : (in_array($nombreEstado, ['Pendiente', 'Rechazado']) ? 'x' : 'clock')" class="h-3 w-3" trazo="2.5" />
                                        {{ \Illuminate\Support\Str::limit($doc->documentoRequisito->nombre, 8, '') }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <span class="sg-badge {{ $estadoBadge[$alumno->estado_documentacion] }}">
                                {{ $estadoTexto[$alumno->estado_documentacion] }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.documentacion.show', $alumno) }}" class="sg-btn sg-btn--suave sg-btn--chico">Revisar <x-sg.icono nombre="arrow-right" class="sg-flecha" /></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-sg.vacio icono="folder" titulo="Sin resultados">No hay alumnos para mostrar.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($alumnos->hasPages())
            <div class="sg-card__foot">{{ $alumnos->links() }}</div>
        @endif
    </div>
@endsection
