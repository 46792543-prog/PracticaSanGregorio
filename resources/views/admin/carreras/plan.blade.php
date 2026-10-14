@extends('layouts.admin')

@section('titulo', 'Planes de Estudio')

@section('contenido')
    <x-sg.encabezado titulo="Plan de Estudios" :subtitulo="$carrera->nombre_carrera . ' — Oferta académica vigente e itinerarios institucionales'" eyebrow="Carreras y planes" icono="book">
        <a href="{{ route('admin.carreras.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
        <a href="{{ route('admin.carreras.materias', $carrera) }}" class="sg-btn sg-btn--suave"><x-sg.icono nombre="pencil" /> Editar materias</a>
    </x-sg.encabezado>

    <div class="sg-card sg-revelar overflow-x-auto">
        <table class="w-full text-[13px] border-collapse">
            <thead>
                <tr class="bg-[#0B3D91] text-white">
                    <th class="border border-slate-100 px-2 py-2">Año</th>
                    <th class="border border-slate-100 px-2 py-2">Nº Orden</th>
                    <th class="border border-slate-100 px-3 py-2 text-left">Espacio curricular</th>
                    <th class="border border-slate-100 px-2 py-2">Período</th>
                    <th class="border border-slate-100 px-2 py-2" colspan="2">Correlatividades</th>
                    <th class="border border-slate-100 px-3 py-2">Régimen de aprobación</th>
                </tr>
                <tr class="bg-blue-50 text-[#0B3D91]">
                    <th class="border border-slate-100"></th>
                    <th class="border border-slate-100"></th>
                    <th class="border border-slate-100"></th>
                    <th class="border border-slate-100"></th>
                    <th class="border border-slate-100 px-2 py-1">Regularizan para cursar</th>
                    <th class="border border-slate-100 px-2 py-1">Aprobadas para rendir</th>
                    <th class="border border-slate-100"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($materiasPorAnio as $anio => $materias)
                    @foreach ($materias as $i => $materia)
                        <tr class="hover:bg-blue-50/50 transition-colors">
                            @if ($i === 0)
                                <td class="border border-slate-100 text-center font-display font-bold text-[#0B3D91] bg-blue-50/60" rowspan="{{ $materias->count() }}">{{ $anio }}</td>
                            @endif
                            <td class="border border-slate-100 text-center py-2"><span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">{{ $materia->numero_orden }}</span></td>
                            <td class="border border-slate-100 px-4 py-3 font-medium text-slate-800">{{ $materia->nombre }}</td>
                            <td class="border border-slate-100 text-center text-slate-500">{{ $materia->periodo->nombre_periodo }}</td>
                            <td class="border border-slate-100 text-center text-slate-500">
                                {{ $materia->requisitos->where('pivot.requiere_regularizada', true)->pluck('numero_orden')->sort()->implode('-') ?: '—' }}
                            </td>
                            <td class="border border-slate-100 text-center text-slate-500">
                                {{ $materia->requisitos->where('pivot.requiere_aprobada', true)->pluck('numero_orden')->sort()->implode('-') ?: '—' }}
                            </td>
                            <td class="border border-slate-100 text-center px-2">
                                <span class="sg-badge sg-badge--azul sg-badge--sin-punto">{{ $materia->regimen->nombre_regimen }}</span>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
