@extends('layouts.director')

@section('titulo', 'Estado de Pagos')
@section('subtitulo', 'Panorama general de alumnos al día y con cuotas pendientes')

@section('contenido')
    <x-sg.encabezado titulo="Estado de Pagos" subtitulo="Panorama general de alumnos al día y con cuotas pendientes" eyebrow="Finanzas" icono="chart" />

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
                    <th class="!text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($alumnos as $alumno)
                    @php
                        $adeudado = (float) ($alumno->monto_pendiente ?? 0) + (float) ($alumno->recargo_pendiente ?? 0);
                        $historialPagos = [
                            'alumno' => "{$alumno->apellido}, {$alumno->nombre}",
                            'dni' => $alumno->dni,
                            'cuotas' => $alumno->cuotas->map(fn ($c) => [
                                'periodo' => ($c->mes->nombre_mes ?? '—') . ' ' . ($c->anioLectivo->anio ?? ''),
                                'concepto' => $c->concepto ?: 'Cuota mensual',
                                'total' => '$ ' . number_format($c->montoTotal(), 0, ',', '.'),
                                'recargo' => (float) $c->recargo > 0 ? '$ ' . number_format($c->recargo, 0, ',', '.') : null,
                                'vencimiento' => $c->fecha_vencimiento ? \App\Support\FechaEsp::corta($c->fecha_vencimiento) : '—',
                                'pagado' => (bool) $c->pagado,
                                'vencida' => ! $c->pagado && $c->vencida,
                                'fecha_pago' => $c->fecha_pago ? \App\Support\FechaEsp::corta($c->fecha_pago) : null,
                                'medio' => $c->movimientoCaja?->medioPago?->nombre_medio,
                            ])->values(),
                        ];
                    @endphp
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
                        <td class="!text-center">
                            <button type="button" onclick="verHistorialPagos({{ \Illuminate\Support\Js::from($historialPagos) }})"
                                    class="sg-btn sg-btn--borde sg-btn--icono" title="Ver historial de pagos" aria-label="Ver historial de pagos de {{ $alumno->apellido }}, {{ $alumno->nombre }}">
                                <x-sg.icono nombre="eye" />
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-sg.vacio icono="search" titulo="Sin resultados">No se encontraron alumnos con esos filtros.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($alumnos->hasPages())
            <div class="sg-card__foot">{{ $alumnos->links() }}</div>
        @endif
    </div>

    {{-- Modal: historial de pagos del alumno --}}
    <div id="modal-historial-pagos" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4" onclick="if (event.target === this) cerrarHistorialPagos()">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-4xl p-6 max-h-[88vh] flex flex-col">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="clock" /></span>
                    <div class="min-w-0">
                        <h3 class="font-display font-bold text-slate-800">Historial de pagos</h3>
                        <p class="text-sm text-slate-500 truncate" id="historial-alumno"></p>
                    </div>
                </div>
                <button type="button" onclick="cerrarHistorialPagos()" class="grid h-9 w-9 shrink-0 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>

            <div class="grid grid-cols-3 gap-3 mb-4">
                <div class="rounded-2xl bg-emerald-50 px-4 py-3 text-center"><p class="font-display font-bold text-xl text-emerald-600" id="historial-pagadas">0</p><p class="text-xs font-semibold text-emerald-700">Pagadas</p></div>
                <div class="rounded-2xl bg-amber-50 px-4 py-3 text-center"><p class="font-display font-bold text-xl text-amber-600" id="historial-pendientes">0</p><p class="text-xs font-semibold text-amber-700">Pendientes</p></div>
                <div class="rounded-2xl bg-rose-50 px-4 py-3 text-center"><p class="font-display font-bold text-xl text-rose-600" id="historial-vencidas">0</p><p class="text-xs font-semibold text-rose-700">Vencidas</p></div>
            </div>

            <div class="overflow-auto rounded-2xl border border-slate-100">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Período</th>
                            <th>Concepto</th>
                            <th>Vencimiento</th>
                            <th class="!text-right">Monto</th>
                            <th>Estado</th>
                            <th>Pagada el</th>
                        </tr>
                    </thead>
                    <tbody id="historial-filas"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function escaparHtml(texto) {
            const div = document.createElement('div');
            div.textContent = texto ?? '';
            return div.innerHTML;
        }

        function verHistorialPagos(datos) {
            document.getElementById('historial-alumno').textContent = datos.alumno + ' · DNI ' + datos.dni;
            document.getElementById('historial-pagadas').textContent = datos.cuotas.filter((c) => c.pagado).length;
            document.getElementById('historial-pendientes').textContent = datos.cuotas.filter((c) => ! c.pagado).length;
            document.getElementById('historial-vencidas').textContent = datos.cuotas.filter((c) => c.vencida).length;

            const filas = datos.cuotas.map((c) => {
                const estado = c.pagado
                    ? '<span class="sg-badge sg-badge--verde">Pagada</span>'
                    : (c.vencida ? '<span class="sg-badge sg-badge--rojo">Vencida</span>' : '<span class="sg-badge sg-badge--amarillo">Pendiente</span>');
                const pago = c.pagado
                    ? escaparHtml(c.fecha_pago ?? '—') + (c.medio ? '<p class="text-xs text-slate-400">' + escaparHtml(c.medio) + '</p>' : '')
                    : '—';
                const recargo = c.recargo ? '<p class="text-xs font-normal text-slate-400">incluye recargo ' + escaparHtml(c.recargo) + '</p>' : '';

                return '<tr>'
                    + '<td class="font-semibold !text-slate-800 whitespace-nowrap">' + escaparHtml(c.periodo) + '</td>'
                    + '<td>' + escaparHtml(c.concepto) + '</td>'
                    + '<td class="whitespace-nowrap">' + escaparHtml(c.vencimiento) + '</td>'
                    + '<td class="!text-right font-display font-bold !text-slate-800 whitespace-nowrap">' + escaparHtml(c.total) + recargo + '</td>'
                    + '<td>' + estado + '</td>'
                    + '<td class="whitespace-nowrap">' + pago + '</td>'
                    + '</tr>';
            });

            document.getElementById('historial-filas').innerHTML = filas.length
                ? filas.join('')
                : '<tr><td colspan="6" class="!text-center text-slate-400 py-8">Este alumno todavía no tiene cuotas generadas.</td></tr>';

            const modal = document.getElementById('modal-historial-pagos');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarHistorialPagos() {
            const modal = document.getElementById('modal-historial-pagos');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrarHistorialPagos(); });
    </script>
@endsection
