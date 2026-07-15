@extends('layouts.admin')

@section('titulo', 'Materias del plan')

@section('contenido')
    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.carreras.index') }}" class="hover:underline">Carreras y planes</a> /
        <span class="text-blue-600 font-semibold">{{ $carrera->nombre }}</span> / Materias
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <h2 class="font-bold text-slate-800 mb-4">Materias cargadas ({{ $materias->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
                        <th class="py-2 pr-4">Orden</th>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Año</th>
                        <th class="py-2 pr-4">Cuatrimestre</th>
                        <th class="py-2 pr-4">Régimen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($materias as $materia)
                        <tr>
                            <td class="py-2 pr-4 text-slate-400">{{ $materia->numero_orden }}</td>
                            <td class="py-2 pr-4 font-semibold text-slate-700">{{ $materia->nombre }}</td>
                            <td class="py-2 pr-4 text-slate-500">{{ $materia->anio_cursada }}°</td>
                            <td class="py-2 pr-4 text-slate-500">{{ ucwords(str_replace('_', ' ', $materia->cuatrimestre)) }}</td>
                            <td class="py-2 pr-4 text-slate-500">
                                {{ match($materia->regimen) { 'solo_promocion' => 'Promoción', 'solo_examen_final' => 'Examen Final', default => 'Promoción / Examen Final' } }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-400">Todavía no se cargaron materias.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 class="font-bold text-slate-800 mb-4">Agregar materia</h2>
        <form method="POST" action="{{ route('admin.carreras.materias.store', $carrera) }}" class="grid sm:grid-cols-5 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nº ORDEN</label>
                <input type="number" name="numero_orden" min="1" required value="{{ $materias->max('numero_orden') + 1 }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE</label>
                <input type="text" name="nombre" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO</label>
                <select name="anio_cursada" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @for ($i = 1; $i <= $carrera->duracion_anios; $i++)
                        <option value="{{ $i }}">{{ $i }}°</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">CUATRIMESTRE</label>
                <select name="cuatrimestre" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="anual">Anual</option>
                    <option value="1er_cuatrimestre">1er Cuatrimestre</option>
                    <option value="2do_cuatrimestre">2do Cuatrimestre</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">RÉGIMEN</label>
                <select name="regimen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="promocion_o_examen_final">Promoción / Examen Final</option>
                    <option value="solo_examen_final">Examen Final</option>
                    <option value="solo_promocion">Promoción</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white font-semibold text-sm px-4 py-2.5">+ Agregar</button>
            </div>
        </form>
    </div>

    <div class="flex justify-end gap-3 mt-6">
        <a href="{{ route('admin.carreras.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Volver</a>
        <a href="{{ route('admin.carreras.correlativas', $carrera) }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow transition text-white font-semibold text-sm px-6 py-2.5">Configurar correlativas →</a>
    </div>
@endsection
