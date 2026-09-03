@extends('layouts.director')

@section('titulo', 'Estado de Pagos')
@section('subtitulo', 'Panorama general de alumnos al día y con cuotas pendientes')

@section('contenido')
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Alumnos al día</p>
            <p class="text-3xl font-extrabold text-emerald-600 tracking-tight mt-1">{{ $alumnosAlDia }}</p>
            <p class="text-[11px] text-slate-400 mt-1">de {{ $totalAlumnos }} alumnos</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Alumnos con deuda</p>
            <p class="text-3xl font-extrabold text-rose-600 tracking-tight mt-1">{{ $alumnosConDeuda }}</p>
            <p class="text-[11px] text-slate-400 mt-1">tienen cuotas pendientes</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Monto adeudado</p>
            <p class="text-3xl font-extrabold text-slate-800 tracking-tight mt-1">$ {{ number_format($montoAdeudadoTotal, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 mt-1">total pendiente de cobro</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Cuotas vencidas</p>
            <p class="text-3xl font-extrabold text-amber-600 tracking-tight mt-1">{{ $cuotasVencidas }}</p>
            <p class="text-[11px] text-slate-400 mt-1">pasaron la fecha de vencimiento</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">📊 Estado de Pagos por Alumno</h2>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 px-6 py-4 border-b border-slate-100">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." maxlength="20"
                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm min-w-[200px] focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">

            <select name="carrera" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30">
                <option value="">Todas las carreras</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id_carrera }}" @selected($carreraId == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                @endforeach
            </select>

            <select name="estado" class="rounded-xl border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30">
                <option value="">Todos los estados</option>
                <option value="al_dia" @selected($estado === 'al_dia')>Al día</option>
                <option value="debe" @selected($estado === 'debe')>Con deuda</option>
            </select>

            <button class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-6 py-2">Filtrar</button>
            @if ($busqueda || $carreraId || $estado)
                <a href="{{ route('director.pagos.index') }}" class="rounded-xl border border-slate-200 text-slate-500 font-semibold text-sm px-4 py-2">Limpiar</a>
            @endif
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                    <th class="px-6 py-3 font-semibold">Alumno</th>
                    <th class="px-6 py-3 font-semibold">Carrera</th>
                    <th class="px-6 py-3 font-semibold">Cuotas pagadas</th>
                    <th class="px-6 py-3 font-semibold">Cuotas pendientes</th>
                    <th class="px-6 py-3 font-semibold">Monto adeudado</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($alumnos as $alumno)
                    @php $adeudado = (float) ($alumno->monto_pendiente ?? 0) + (float) ($alumno->recargo_pendiente ?? 0); @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 rounded-full bg-blue-100 text-blue-700 text-xs font-bold grid place-items-center shrink-0">
                                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-700">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $alumno->inscripcionesCarrera->first()?->carrera?->nombre_carrera ?? '—' }}</td>
                        <td class="px-6 py-3 font-semibold text-emerald-600">{{ $alumno->cuotas_pagadas_count }}</td>
                        <td class="px-6 py-3 font-semibold {{ $alumno->cuotas_pendientes_count > 0 ? 'text-rose-600' : 'text-slate-400' }}">{{ $alumno->cuotas_pendientes_count }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-700">
                            {{ $adeudado > 0 ? '$ ' . number_format($adeudado, 0, ',', '.') : '—' }}
                        </td>
                        <td class="px-6 py-3">
                            @if ($alumno->cuotas_pendientes_count > 0)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-rose-100 text-rose-700">Debe</span>
                            @else
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-emerald-100 text-emerald-700">Al día</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">No se encontraron alumnos con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $alumnos->links() }}
        </div>
    </div>
@endsection
