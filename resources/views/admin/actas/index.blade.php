@extends('layouts.admin')

@section('titulo', 'Acta de Exámenes')

@section('contenido')
    <x-sg.encabezado titulo="Acta de Exámenes" subtitulo="Generación automática con alumnos inscriptos en la mesa" eyebrow="Gestión académica" icono="document-check" />

    <div class="sg-aviso sg-aviso--info mb-6 sg-revelar">
        <x-sg.icono nombre="sparkles" class="h-5 w-5 shrink-0" />
        <span>El acta se <strong>autocompleta</strong> con los alumnos inscriptos en la mesa seleccionada, replicando el formato oficial en papel del instituto.</span>
    </div>

    <div class="sg-card sg-revelar mb-6">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="search" /></span>
                <div><h3>Seleccionar mesa</h3><p>Filtrá por carrera y elegí la mesa a completar</p></div>
            </div>
        </div>
        <form method="GET" class="grid sm:grid-cols-3 gap-4 p-6">
            <div>
                <label class="sg-label">CARRERA</label>
                <select name="carrera" onchange="this.form.submit()" class="sg-input w-full">
                    <option value="">Todas</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}" @selected($filtroCarrera == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="sg-label">MESA (MATERIA · LLAMADO / FECHA)</label>
                <select name="mesa" onchange="this.form.submit()" class="sg-input w-full">
                    @forelse ($mesas as $mesa)
                        <option value="{{ $mesa->id_mesa }}" @selected($mesaSeleccionada?->id_mesa === $mesa->id_mesa)>
                            {{ $mesa->materia->nombre }} — {{ $mesa->llamadoExamen->nombre_llamado }} · {{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}
                        </option>
                    @empty
                        <option>No hay mesas cargadas</option>
                    @endforelse
                </select>
            </div>
        </form>
    </div>

    @if ($mesaSeleccionada)
        <form method="POST" action="{{ route('admin.actas.generar', $mesaSeleccionada) }}">
            @csrf
            <div class="sg-revelar rounded-3xl bg-gradient-to-br from-slate-100 to-blue-50 p-3 sm:p-6 mb-6">
            <div class="relative bg-white rounded-xl shadow-lg ring-1 ring-slate-200 p-6 sm:p-10 max-w-4xl mx-auto overflow-hidden">
                <span class="absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-[#0B3D91] via-[#4FC3F7] to-[#FFC107]"></span>
                <div class="flex items-center gap-3 border-b-2 border-[#0B3D91] pb-4 mb-4">
                    <span class="h-11 w-11 rounded-xl bg-gradient-to-br from-[#4FC3F7] to-[#0B3D91] ring-2 ring-[#FFC107] text-white grid place-items-center font-display font-bold text-xs">ISG</span>
                    <div>
                        <p class="font-bold text-slate-800">Instituto Superior San Gregorio</p>
                        <p class="text-xs text-slate-400">San Pedro de Jujuy — Pedro Goyena 33 — Tel. 03888-480686</p>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-4 text-sm">
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">L: <input type="text" maxlength="20" name="libro" value="{{ old('libro', $acta->libro ?? '') }}" class="w-16 border-0 border-b-2 border-slate-300 text-center rounded-none !shadow-none focus:border-[#0B3D91]"></label>
                        <label class="flex items-center gap-2">F: <input type="text" maxlength="20" name="folio" value="{{ old('folio', $acta->folio ?? '') }}" class="w-16 border-0 border-b-2 border-slate-300 text-center rounded-none !shadow-none focus:border-[#0B3D91]"></label>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($acta)
                            <span class="sg-badge {{ $acta->estado === 'generada' ? 'sg-badge--verde' : 'sg-badge--amarillo' }}">
                                {{ $acta->estado === 'generada' ? 'Definitiva' : 'Borrador' }}
                            </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 text-[#0B3D91] font-semibold px-3 py-1 text-sm"><x-sg.icono nombre="calendar" class="h-4 w-4" />{{ \App\Support\FechaEsp::corta($mesaSeleccionada->fecha_examen) }}</span>
                    </div>
                </div>

                <h3 class="text-center font-display font-extrabold text-[#0B3D91] tracking-wide mb-5">ACTA DE EXÁMENES {{ mb_strtoupper($mesaSeleccionada->turnoExamen->nombre_turno) }} {{ $mesaSeleccionada->anioLectivo->anio }}</h3>

                <p class="text-sm mb-1"><strong>Carrera:</strong> {{ $mesaSeleccionada->materia->carrera->nombre_carrera }}.</p>
                <p class="text-sm mb-1"><strong>Asignatura:</strong> {{ mb_strtoupper($mesaSeleccionada->materia->nombre) }}</p>
                <p class="text-sm mb-4"><strong>Examen de Alumnos:</strong> REGULAR</p>

                <table class="w-full text-xs border-collapse mb-4">
                    <thead>
                        <tr class="bg-[#0B3D91] text-white">
                            <th class="border border-slate-300 px-2 py-1">N°</th>
                            <th class="border border-slate-300 px-2 py-1">Documento</th>
                            <th class="border border-slate-300 px-3 py-1 text-left">Apellido y Nombre</th>
                            <th class="border border-slate-300 px-2 py-1" colspan="3">Calificación</th>
                        </tr>
                        <tr class="bg-blue-50 text-[#0B3D91]">
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300 px-2 py-1">Escrito</th>
                            <th class="border border-slate-300 px-2 py-1">Oral</th>
                            <th class="border border-slate-300 px-2 py-1">Final</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscripcionesAceptadas as $i => $inscripcion)
                            @php $detalle = $acta?->detalles->firstWhere('id_persona_alumno', $inscripcion->id_persona_alumno); @endphp
                            <tr>
                                <td class="border border-slate-300 text-center py-1">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="border border-slate-300 text-center">DNI {{ $inscripcion->personaAlumno->dni }}</td>
                                <td class="border border-slate-300 px-3">{{ mb_strtoupper($inscripcion->personaAlumno->apellido) }}, {{ mb_strtoupper($inscripcion->personaAlumno->nombre) }}</td>
                                <td class="border border-slate-300 p-1">
                                    <input type="text" inputmode="numeric" data-nota name="notas[{{ $inscripcion->id_persona_alumno }}][nota_escrito]" value="{{ $detalle->nota_escrito ?? '' }}" class="w-full text-center rounded-md py-1 hover:bg-blue-50 focus:bg-blue-50 !shadow-none focus:outline-none">
                                </td>
                                <td class="border border-slate-300 p-1">
                                    <input type="text" inputmode="numeric" data-nota name="notas[{{ $inscripcion->id_persona_alumno }}][nota_oral]" value="{{ $detalle->nota_oral ?? '' }}" class="w-full text-center rounded-md py-1 hover:bg-blue-50 focus:bg-blue-50 !shadow-none focus:outline-none">
                                </td>
                                <td class="border border-slate-300 p-1">
                                    <input type="text" inputmode="numeric" data-nota name="notas[{{ $inscripcion->id_persona_alumno }}][nota_final]" value="{{ $detalle->nota_final ?? '' }}" class="w-full text-center font-bold rounded-md py-1 hover:bg-blue-50 focus:bg-blue-50 !shadow-none focus:outline-none">
                                    <select name="notas[{{ $inscripcion->id_persona_alumno }}][resultado]" class="w-full text-[10px] border-t border-slate-200 focus:outline-none">
                                        <option value="">—</option>
                                        <option value="aprobado" @selected(($detalle->resultado ?? null) === 'aprobado')>Aprobado</option>
                                        <option value="desaprobado" @selected(($detalle->resultado ?? null) === 'desaprobado')>Desaprobado</option>
                                        <option value="ausente" @selected(($detalle->resultado ?? null) === 'ausente')>Ausente</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="border border-slate-300 text-center py-4 text-slate-400">Nadie se inscribió a esta mesa todavía.</td></tr>
                        @endforelse
                        @for ($i = $inscripcionesAceptadas->count(); $i < max(7, $inscripcionesAceptadas->count()); $i++)
                            <tr class="text-slate-300">
                                <td class="border border-slate-300 text-center py-1">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="border border-slate-300"></td><td class="border border-slate-300"></td>
                                <td class="border border-slate-300"></td><td class="border border-slate-300"></td><td class="border border-slate-300"></td>
                            </tr>
                        @endfor
                    </tbody>
                </table>

                <label class="sg-label">OBSERVACIONES:</label>
                <textarea name="observaciones" rows="2" maxlength="2000" class="w-full rounded-xl border-[1.5px] border-slate-200 bg-slate-50 p-3 text-sm mb-6">{{ $acta->observaciones ?? '' }}</textarea>

                <div class="flex flex-wrap justify-end gap-x-8 gap-y-2 text-sm mb-10 rounded-xl bg-slate-50 px-5 py-3">
                    <p>TOTAL DE ALUMNOS: <strong>{{ $inscripcionesAceptadas->count() }}</strong></p>
                    <p>APROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'aprobado')->count() ?: '—' }}</strong></p>
                    <p>DESAPROBADOS: <strong>{{ $acta?->detalles->where('resultado', 'desaprobado')->count() ?: '—' }}</strong></p>
                    <p>AUSENTES: <strong>{{ $acta?->detalles->where('resultado', 'ausente')->count() ?: '—' }}</strong></p>
                </div>

                <div class="grid grid-cols-3 gap-6 text-center text-sm">
                    <p class="border-t border-slate-400 pt-2">Presidente</p>
                    <p class="border-t border-slate-400 pt-2">Vocal 1</p>
                    <p class="border-t border-slate-400 pt-2">Vocal 2</p>
                </div>
                <p class="text-center text-xs font-semibold mt-6">
                    SAN PEDRO DE JUJUY, {{ mb_strtoupper(\Illuminate\Support\Str::after(\App\Support\FechaEsp::larga($mesaSeleccionada->fecha_examen), ', ')) }}
                </p>
            </div>
            </div>

            <div class="sticky bottom-4 flex flex-wrap justify-end gap-3 rounded-2xl bg-white/90 backdrop-blur border border-slate-200 p-3 shadow-lg">
                <a href="{{ route('admin.mesas.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="clock" /> Historial de actas</a>
                @if ($acta)
                    <a href="{{ route('admin.mesas.acta', $mesaSeleccionada) }}" target="_blank" class="sg-btn sg-btn--borde"><x-sg.icono nombre="download" /> Ver / descargar PDF</a>
                @endif
                <button type="submit" formaction="{{ route('admin.actas.guardar', $mesaSeleccionada) }}" class="sg-btn sg-btn--suave"><x-sg.icono nombre="pencil" /> Guardar borrador</button>
                <button type="submit" formtarget="_blank" class="sg-btn sg-btn--primario"><x-sg.icono nombre="printer" /> Generar e imprimir acta</button>
            </div>
        </form>
    @else
        <div class="sg-card sg-revelar"><x-sg.vacio icono="document" titulo="Sin mesas">No hay mesas de examen cargadas todavía.</x-sg.vacio></div>
    @endif
@endsection
