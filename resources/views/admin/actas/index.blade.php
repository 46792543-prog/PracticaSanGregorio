@extends('layouts.admin')

@section('titulo', 'Acta de Exámenes')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">📄 Acta de Exámenes</h1>
        <p class="text-sm text-slate-400">Generación automática con alumnos inscriptos en la mesa</p>
    </div>

    <div class="rounded-xl bg-blue-50 text-[#1E4D8C] text-sm px-4 py-3 mb-6">
        ✨ El acta se <strong>autocompleta</strong> con los alumnos inscriptos en la mesa seleccionada, replicando el formato oficial en papel del instituto.
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6">
        <h2 class="font-bold text-[#1E4D8C] px-6 py-3 border-b border-slate-100 bg-blue-50/60 rounded-t-xl">🔍 Seleccionar Mesa</h2>
        <form method="GET" class="grid sm:grid-cols-3 gap-4 p-6">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">CARRERA</label>
                <select name="carrera" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id }}" @selected($filtroCarrera == $carrera->id)>{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">MESA (MATERIA · LLAMADO / FECHA)</label>
                <select name="mesa" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @forelse ($mesas as $mesa)
                        <option value="{{ $mesa->id }}" @selected($mesaSeleccionada?->id === $mesa->id)>
                            {{ $mesa->materia->nombre }} — {{ $mesa->llamado === 'primer_llamado' ? '1er' : '2do' }} llamado · {{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}
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
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 mb-4">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-4 mb-4">
                    <span class="h-10 w-10 rounded-full bg-[#1E4D8C] text-white grid place-items-center font-bold text-xs">ISG</span>
                    <div>
                        <p class="font-bold text-slate-800">Instituto Superior San Gregorio</p>
                        <p class="text-xs text-slate-400">San Pedro de Jujuy — Pedro Goyena 33 — Tel. 03888-480686</p>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-4 text-sm">
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2">L: <input type="text" name="libro" value="{{ old('libro', $acta->libro ?? '') }}" class="w-16 border-b border-slate-400 text-center focus:outline-none"></label>
                        <label class="flex items-center gap-2">F: <input type="text" name="folio" value="{{ old('folio', $acta->folio ?? '') }}" class="w-16 border-b border-slate-400 text-center focus:outline-none"></label>
                    </div>
                    <span class="border border-slate-300 rounded px-3 py-1 text-sm">{{ \App\Support\FechaEsp::corta($mesaSeleccionada->fecha_examen) }}</span>
                </div>

                <h3 class="text-center font-bold text-slate-800 mb-4">ACTA DE EXÁMENES {{ mb_strtoupper(str_replace('_', ' ', $mesaSeleccionada->turno)) }} {{ $mesaSeleccionada->anioLectivo->anio }}</h3>

                <p class="text-sm mb-1"><strong>Carrera:</strong> {{ $mesaSeleccionada->materia->carrera->nombre }}.</p>
                <p class="text-sm mb-1"><strong>Asignatura:</strong> {{ mb_strtoupper($mesaSeleccionada->materia->nombre) }}</p>
                <p class="text-sm mb-4"><strong>Examen de Alumnos:</strong> REGULAR</p>

                <table class="w-full text-xs border-collapse mb-4">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="border border-slate-300 px-2 py-1">N°</th>
                            <th class="border border-slate-300 px-2 py-1">Documento</th>
                            <th class="border border-slate-300 px-3 py-1 text-left">Apellido y Nombre</th>
                            <th class="border border-slate-300 px-2 py-1" colspan="3">Calificación</th>
                        </tr>
                        <tr class="bg-slate-50 text-slate-500">
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300"></th>
                            <th class="border border-slate-300 px-2 py-1">Escrito</th>
                            <th class="border border-slate-300 px-2 py-1">Oral</th>
                            <th class="border border-slate-300 px-2 py-1">Promedio</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inscripcionesAceptadas as $i => $inscripcion)
                            @php $detalle = $acta?->detalles->firstWhere('user_id', $inscripcion->user_id); @endphp
                            <tr>
                                <td class="border border-slate-300 text-center py-1">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                                <td class="border border-slate-300 text-center">DNI {{ $inscripcion->user->dni }}</td>
                                <td class="border border-slate-300 px-3">{{ mb_strtoupper($inscripcion->user->apellido) }}, {{ mb_strtoupper($inscripcion->user->nombre) }}</td>
                                <td class="border border-slate-300 p-1">
                                    <input type="number" step="0.01" min="0" max="10" name="notas[{{ $inscripcion->user_id }}][nota_escrito]" value="{{ $detalle->nota_escrito ?? '' }}" class="w-full text-center focus:outline-none">
                                </td>
                                <td class="border border-slate-300 p-1">
                                    <input type="number" step="0.01" min="0" max="10" name="notas[{{ $inscripcion->user_id }}][nota_oral]" value="{{ $detalle->nota_oral ?? '' }}" class="w-full text-center focus:outline-none">
                                </td>
                                <td class="border border-slate-300 p-1">
                                    <input type="number" step="0.01" min="0" max="10" name="notas[{{ $inscripcion->user_id }}][nota_promedio]" value="{{ $detalle->nota_promedio ?? '' }}" class="w-full text-center font-bold focus:outline-none">
                                    <select name="notas[{{ $inscripcion->user_id }}][resultado]" class="w-full text-[10px] border-t border-slate-200 focus:outline-none">
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

                <label class="block text-xs font-semibold text-slate-500 mb-1">OBSERVACIONES:</label>
                <textarea name="observaciones" rows="2" class="w-full border-b border-slate-300 text-sm mb-6 focus:outline-none">{{ $acta->observaciones ?? '' }}</textarea>

                <div class="flex justify-end gap-8 text-sm mb-8">
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

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.mesas.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Ver Historial de Acta</a>
                <button type="submit" formaction="{{ route('admin.actas.guardar', $mesaSeleccionada) }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Guardar borrador</button>
                <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-bold text-sm px-6 py-2.5">📄 Generar e imprimir acta</button>
            </div>
        </form>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 text-center text-slate-400">No hay mesas de examen cargadas todavía.</div>
    @endif
@endsection
