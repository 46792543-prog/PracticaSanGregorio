@extends('layouts.admin')

@section('titulo', 'Alumnos Registrados')

@section('contenido')
    <x-sg.encabezado titulo="Alumnos Registrados" subtitulo="Listado completo por carrera — Año lectivo {{ $corteActivo?->anio ?? now()->year }}" eyebrow="Gestión académica" icono="users">
        <a href="{{ route('admin.alumnos.historial-previo.index') }}" class="sg-btn sg-btn--borde">
            <x-sg.icono nombre="book" /> Cargar historial previo
        </a>
        <a href="{{ route('admin.alumnos.create') }}" class="sg-btn sg-btn--primario">
            <x-sg.icono nombre="user-plus" /> Registrar nuevo alumno
        </a>
    </x-sg.encabezado>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" data-sg-escalonar>
        <x-sg.stat etiqueta="Alumnos en esta carrera" :valor="$totalEnCarrera" icono="users" tono="azul" />
        <x-sg.stat etiqueta="Activos" :valor="$activos" icono="check-circle" tono="verde" />
        <x-sg.stat etiqueta="Doc. pendiente" :valor="$docPendiente" icono="clock" tono="amarillo" />
        <x-sg.stat etiqueta="Con cuotas en deuda" :valor="$conDeuda" icono="alert" tono="rojo" />
    </div>

    <form method="GET" class="sg-card sg-revelar p-4 sm:p-5 mb-6 grid sm:grid-cols-3 gap-4">
        <div>
            <label class="sg-label">Carrera</label>
            <select name="carrera" onchange="this.form.submit()" class="sg-input">
                <option value="">Todas las carreras</option>
                @foreach ($carreras as $carrera)
                    <option value="{{ $carrera->id_carrera }}" @selected($carreraId == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="sg-label">Estado</label>
            <select name="estado" onchange="this.form.submit()" class="sg-input">
                <option value="">Todos</option>
                <option value="activo" @selected($estado === 'activo')>Activo</option>
                <option value="baja" @selected($estado === 'baja')>Baja</option>
            </select>
        </div>
        <div>
            <label class="sg-label">Buscar</label>
            <div class="sg-campo-icono">
                <x-sg.icono nombre="search" />
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." data-busqueda maxlength="50" class="sg-input">
            </div>
        </div>
    </form>

    <x-sg.tarjeta :cuerpo="false">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Carrera</th>
                    <th>Año</th>
                    <th>Estado</th>
                    <th>Cuotas</th>
                    <th>Fecha alta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($alumnos as $alumno)
                    @php
                        $inscripcion = $alumno->inscripcionesCarrera->first();
                        $vencidas = $alumno->cuotas->filter(fn ($c) => $c->vencida)->count();
                        $iniciales = mb_substr($alumno->nombre, 0, 1) . mb_substr($alumno->apellido, 0, 1);
                    @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="sg-avatar">{{ $iniciales }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }}</p>
                                </div>
                            </div>
                        </td>
                        <td>{{ $inscripcion?->carrera?->nombre_carrera ?? '—' }}</td>
                        <td>{{ $inscripcion?->anioCursada?->nombre_anio ?? '—' }}</td>
                        <td>
                            @if (! $inscripcion)
                                <span class="sg-badge sg-badge--gris">Sin carrera</span>
                            @elseif ($inscripcion->estadoInscripcion->nombre_estado === 'Baja')
                                <span class="sg-badge sg-badge--rojo">Baja</span>
                            @else
                                <span class="sg-badge sg-badge--verde">Activo</span>
                            @endif
                        </td>
                        <td>
                            @if ($vencidas > 0)
                                <span class="sg-badge sg-badge--rojo">{{ $vencidas }} cuotas</span>
                            @else
                                <span class="sg-badge sg-badge--verde">Al día</span>
                            @endif
                        </td>
                        <td>{{ \App\Support\FechaEsp::corta($alumno->created_at) }}</td>
                        <td class="text-right">
                            <a href="{{ route('admin.alumnos.show', $alumno) }}" class="sg-btn sg-btn--suave sg-btn--chico">
                                Ver ficha <x-sg.icono nombre="arrow-right" class="sg-flecha" />
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"><x-sg.vacio icono="users" titulo="Sin resultados">No se encontraron alumnos.</x-sg.vacio></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($alumnos->hasPages())
            <x-slot:pie>{{ $alumnos->links() }}</x-slot:pie>
        @endif
    </x-sg.tarjeta>
@endsection
