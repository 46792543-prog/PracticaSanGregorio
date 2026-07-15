@extends('layouts.admin')

@section('titulo', 'Régimen de Correlatividades')

@section('contenido')
    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.carreras.index') }}" class="hover:underline">Carreras y planes</a> /
        <span class="text-blue-600 font-semibold">{{ $carrera->nombre }}</span> / Régimen de Correlatividades
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <h2 class="font-bold text-slate-800 mb-1">Matriz de Correlatividades de Materias</h2>
        <p class="text-sm text-slate-400 mb-6">Defina qué asignaturas son prerrequisitos de aprobación o regularidad para avanzar.</p>

        <form method="POST" action="{{ route('admin.carreras.correlativas.store', $carrera) }}" class="grid sm:grid-cols-3 gap-4 items-end mb-8">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MATERIA CURSANTE / DESTINO</label>
                <select name="materia_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    @foreach ($materias as $materia)
                        <option value="{{ $materia->id }}">{{ $materia->numero_orden }}. {{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MATERIA CORRELATIVA / REQUERIDA</label>
                <select name="materia_requisito_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    <option value="">Seleccione la materia previa...</option>
                    @foreach ($materias as $materia)
                        <option value="{{ $materia->id }}">{{ $materia->numero_orden }}. {{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                    <input type="checkbox" name="requiere_regularizada" value="1" class="h-4 w-4 rounded"> Para cursar
                </label>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                    <input type="checkbox" name="requiere_aprobada" value="1" class="h-4 w-4 rounded"> Para rendir
                </label>
            </div>
            <div class="sm:col-span-3">
                <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white font-semibold text-sm px-6 py-2.5">Guardar correlativa</button>
            </div>
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
                    <th class="py-2 pr-4">Materia</th>
                    <th class="py-2 pr-4">Materia requerida (correlativa)</th>
                    <th class="py-2 pr-4">Requisito cursar</th>
                    <th class="py-2 pr-4">Requisito rendir</th>
                    <th class="py-2 pr-4">Quitar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($correlativas as $correlativa)
                    <tr>
                        <td class="py-3 pr-4 font-semibold text-slate-700">{{ $correlativa->materia->nombre }}</td>
                        <td class="py-3 pr-4 text-slate-600">{{ $correlativa->materiaRequisito->nombre }}</td>
                        <td class="py-3 pr-4">
                            @if ($correlativa->requiere_regularizada)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-green-100 text-green-700">Regularizada</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            @if ($correlativa->requiere_aprobada)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-blue-100 text-blue-700">Aprobada</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            <form method="POST" action="{{ route('admin.carreras.correlativas.destroy', $correlativa) }}" onsubmit="return confirm('¿Quitar esta correlativa?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="h-7 w-7 rounded-lg border border-red-200 text-red-500 hover:bg-red-50">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-400">Todavía no se cargaron correlativas.</td></tr>
                @endforelse
            </tbody>
        </table>

        <p class="text-xs text-slate-400 mt-4">Las correlatividades afectarán las condiciones de inscripción a examen del alumno.</p>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.carreras.materias', $carrera) }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">← Volver</a>
        <a href="{{ route('admin.carreras.plan', $carrera) }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow transition text-white font-semibold text-sm px-6 py-2.5">Ver plan de estudio</a>
    </div>
@endsection
