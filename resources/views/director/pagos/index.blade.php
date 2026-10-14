@extends('layouts.director')

@section('titulo', 'Estado de Pagos')
@section('subtitulo', 'Panorama general de alumnos al día y con cuotas pendientes')

@section('contenido')
    <x-sg.encabezado titulo="Estado de Pagos" subtitulo="Panorama general de alumnos al día y con cuotas pendientes" eyebrow="Finanzas" icono="chart">
        <a href="{{ route('director.pagos.excel', request()->query()) }}" class="sg-btn sg-btn--verde">
            <x-sg.icono nombre="download" /> Exportar Excel
        </a>
    </x-sg.encabezado>

    @php $porcentajeAlDia = $totalAlumnos > 0 ? round($alumnosAlDia / $totalAlumnos * 100) : 0; @endphp

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6" data-sg-escalonar>
        <x-sg.stat etiqueta="Alumnos al día" :valor="$alumnosAlDia" icono="check-circle" tono="verde">
            <span class="text-slate-400 font-medium">de {{ $totalAlumnos }} alumnos</span>
            <div class="sg-progreso mt-2"><span style="width: {{ $porcentajeAlDia }}%; background: linear-gradient(90deg, #34D399, #059669);"></span></div>
        </x-sg.stat>
        <x-sg.stat etiqueta="Alumnos con deuda" :valor="$alumnosConDeuda" icono="alert" tono="rojo">
            <span class="text-slate-400 font-medium">tienen cuotas pendientes</span>
        </x-sg.stat>
        <x-sg.stat etiqueta="Monto adeudado" :valor="'$ ' . number_format($montoAdeudadoTotal, 0, ',', '.')" icono="banknotes" tono="azul">
            <span class="text-slate-400 font-medium">total pendiente de cobro</span>
        </x-sg.stat>
        <x-sg.stat etiqueta="Cuotas vencidas" :valor="$cuotasVencidas" icono="clock" tono="amarillo">
            <span class="text-slate-400 font-medium">pasaron la fecha de vencimiento</span>
        </x-sg.stat>
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="users" /></span>
                <div><h3>Estado de pagos por alumno</h3><p>Filtrá por nombre, carrera o situación</p></div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div class="sg-campo-icono flex-1 min-w-[200px]">
                <x-sg.icono nombre="search" />
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." maxlength="20"
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                       class="sg-input w-full">
            </div>

            <select name="carrera" class="sg-input w-auto">
                <option value="">Todas las carreras</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id_carrera }}" @selected($carreraId == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                @endforeach
            </select>

            <select name="estado" class="sg-input w-auto">
                <option value="">Todos los estados</option>
                <option value="al_dia" @selected($estado === 'al_dia')>Al día</option>
                <option value="debe" @selected($estado === 'debe')>Con deuda</option>
            </select>

            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Filtrar</button>
            @if ($busqueda || $carreraId || $estado)
                <a href="{{ route('director.pagos.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Carrera</th>
                    <th>Pagadas</th>
                    <th>Pendientes</th>
                    <th>Monto adeudado</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($alumnos as $alumno)
                    @php $adeudado = (float) ($alumno->monto_pendiente ?? 0) + (float) ($alumno->recargo_pendiente ?? 0); @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="sg-avatar">
                                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $alumno->inscripcionesCarrera->first()?->carrera?->nombre_carrera ?? '—' }}</td>
                        <td><span class="inline-grid min-w-[2rem] h-8 place-items-center rounded-lg bg-emerald-50 px-2 font-display font-bold text-emerald-600">{{ $alumno->cuotas_pagadas_count }}</span></td>
                        <td><span class="inline-grid min-w-[2rem] h-8 place-items-center rounded-lg px-2 font-display font-bold {{ $alumno->cuotas_pendientes_count > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }}">{{ $alumno->cuotas_pendientes_count }}</span></td>
                        <td class="font-display font-bold !text-slate-800 whitespace-nowrap">
                            {{ $adeudado > 0 ? '$ ' . number_format($adeudado, 0, ',', '.') : '—' }}
                        </td>
                        <td>
                            @if ($alumno->cuotas_pendientes_count > 0)
                                <span class="sg-badge sg-badge--rojo">Debe</span>
                            @else
                                <span class="sg-badge sg-badge--verde">Al día</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-sg.vacio icono="search" titulo="Sin resultados">No se encontraron alumnos con esos filtros.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($alumnos->hasPages())
            <div class="sg-card__foot">{{ $alumnos->links() }}</div>
        @endif
    </div>
@endsection
