@extends('layouts.admin')

@section('titulo', 'Planes de Estudio')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Planes de Estudio</h1>
        <p class="text-sm text-slate-400">{{ $carrera->nombre }} — Oferta académica vigente e itinerarios institucionales</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-x-auto">
        <table class="w-full text-xs border-collapse">
            <thead>
                <tr class="bg-slate-50 text-slate-600">
                    <th class="border border-slate-200 px-2 py-2">Año</th>
                    <th class="border border-slate-200 px-2 py-2">Nº Orden</th>
                    <th class="border border-slate-200 px-3 py-2 text-left">Espacio curricular</th>
                    <th class="border border-slate-200 px-2 py-2">Régimen</th>
                    <th class="border border-slate-200 px-2 py-2" colspan="2">Correlatividades</th>
                    <th class="border border-slate-200 px-3 py-2">Régimen de aprobación</th>
                </tr>
                <tr class="bg-slate-50 text-slate-500">
                    <th class="border border-slate-200"></th>
                    <th class="border border-slate-200"></th>
                    <th class="border border-slate-200"></th>
                    <th class="border border-slate-200"></th>
                    <th class="border border-slate-200 px-2 py-1">Regularizan para cursar</th>
                    <th class="border border-slate-200 px-2 py-1">Aprobadas para rendir</th>
                    <th class="border border-slate-200"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($materiasPorAnio as $anio => $materias)
                    @foreach ($materias as $i => $materia)
                        <tr class="hover:bg-slate-50">
                            @if ($i === 0)
                                <td class="border border-slate-200 text-center font-bold text-slate-700" rowspan="{{ $materias->count() }}">{{ $anio }}°</td>
                            @endif
                            <td class="border border-slate-200 text-center text-slate-500">{{ $materia->numero_orden }}</td>
                            <td class="border border-slate-200 px-3 py-2 text-slate-700">{{ $materia->nombre }}</td>
                            <td class="border border-slate-200 text-center text-slate-500">
                                {{ $materia->cuatrimestre === 'anual' ? 'A' : ($materia->cuatrimestre === '1er_cuatrimestre' ? '1C' : '2C') }}
                            </td>
                            <td class="border border-slate-200 text-center text-slate-500">
                                {{ $materia->requisitos->where('pivot.requiere_regularizada', true)->pluck('numero_orden')->sort()->implode('-') ?: '—' }}
                            </td>
                            <td class="border border-slate-200 text-center text-slate-500">
                                {{ $materia->requisitos->where('pivot.requiere_aprobada', true)->pluck('numero_orden')->sort()->implode('-') ?: '—' }}
                            </td>
                            <td class="border border-slate-200 text-center text-slate-600">
                                {{ match($materia->regimen) { 'solo_promocion' => 'Promoción', 'solo_examen_final' => 'Examen Final', default => 'Promoción / Examen Final' } }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <a href="{{ route('admin.carreras.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Volver</a>
    </div>
@endsection
