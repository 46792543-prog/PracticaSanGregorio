@extends('layouts.admin')

@section('titulo', 'Inscripciones a mesas de examen')

@section('contenido')
    <x-sg.encabezado titulo="Inscripciones a mesas de examen" subtitulo="Aprobá o rechazá las inscripciones enviadas por los alumnos" eyebrow="Gestión académica" icono="inbox" />

    <div class="inline-flex flex-wrap gap-1 rounded-2xl bg-white border border-slate-200 p-1.5 mb-6 shadow-sm sg-revelar">
        @foreach (['En proceso' => ['Pendientes', 'clock'], 'Aceptado' => ['Aceptadas', 'check-circle'], 'Rechazado' => ['Rechazadas', 'x-circle'], 'todas' => ['Todas', 'list']] as $valor => [$texto, $icono])
            <a href="{{ route('admin.inscripciones.index', ['estado' => $valor]) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition-all {{ $filtro === $valor ? 'bg-[#0B3D91] text-white shadow-md shadow-blue-900/20' : 'text-slate-500 hover:bg-slate-50 hover:text-[#0B3D91]' }}">
                <x-sg.icono :nombre="$icono" class="h-4 w-4" /> {{ $texto }}
            </a>
        @endforeach
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Materia</th>
                    <th>Fecha inscripción</th>
                    <th>Nota / Resultado</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($inscripciones as $inscripcion)
                    @php $estadoNombre = $inscripcion->estadoInscripcion->nombre_estado; @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="sg-avatar">{{ mb_substr($inscripcion->personaAlumno->nombre, 0, 1) }}{{ mb_substr($inscripcion->personaAlumno->apellido, 0, 1) }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $inscripcion->personaAlumno->nombre }} {{ $inscripcion->personaAlumno->apellido }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $inscripcion->personaAlumno->dni }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <p class="font-medium text-slate-700">{{ $inscripcion->mesaExamen->materia->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $inscripcion->mesaExamen->materia->carrera->nombre_carrera }}</p>
                        </td>
                        <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::corta($inscripcion->fecha_inscripcion) }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}" class="flex items-center gap-1.5">
                                @csrf @method('PUT')
                                <input type="hidden" name="estado" value="{{ $estadoNombre === 'En proceso' ? 'Aceptado' : $estadoNombre }}">
                                <input type="text" inputmode="numeric" data-nota name="nota_examen" value="{{ $inscripcion->nota_examen }}" placeholder="Nota"
                                       class="sg-input sg-input--chico w-16">
                                <select name="resultado" class="sg-input sg-input--chico">
                                    <option value="">—</option>
                                    <option value="aprobado" @selected($inscripcion->resultado === 'aprobado')>Aprobado</option>
                                    <option value="desaprobado" @selected($inscripcion->resultado === 'desaprobado')>Desaprobado</option>
                                    <option value="ausente" @selected($inscripcion->resultado === 'ausente')>Ausente</option>
                                </select>
                                <button type="submit" class="sg-btn sg-btn--suave sg-btn--chico">Guardar</button>
                            </form>
                        </td>
                        <td>
                            <span @class([
                                    'sg-badge',
                                    'sg-badge--amarillo' => $estadoNombre === 'En proceso',
                                    'sg-badge--verde' => $estadoNombre === 'Aceptado',
                                    'sg-badge--rojo' => $estadoNombre === 'Rechazado',
                                ])>
                                {{ ['En proceso' => 'Pendiente', 'Aceptado' => 'Aceptada', 'Rechazado' => 'Rechazada'][$estadoNombre] ?? $estadoNombre }}
                            </span>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                @if ($estadoNombre === 'En proceso')
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Aceptado">
                                        <button class="sg-btn sg-btn--verde sg-btn--chico"><x-sg.icono nombre="check" /> Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Rechazado">
                                        <button class="sg-btn sg-btn--peligro sg-btn--chico"><x-sg.icono nombre="x" /> Rechazar</button>
                                    </form>
                                @endif

                                @if ($estadoNombre === 'Aceptado')
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Rechazado">
                                        <button class="sg-btn sg-btn--peligro sg-btn--chico"><x-sg.icono nombre="x" /> Rechazar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="En proceso">
                                        <button class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="refresh" /> A pendiente</button>
                                    </form>
                                @endif

                                @if ($estadoNombre === 'Rechazado')
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="Aceptado">
                                        <button class="sg-btn sg-btn--verde sg-btn--chico"><x-sg.icono nombre="check" /> Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.inscripciones.actualizar', $inscripcion) }}">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="estado" value="En proceso">
                                        <button class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="refresh" /> A pendiente</button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('admin.inscripciones.destroy', $inscripcion) }}"
                                      onsubmit="return confirm('¿Eliminar esta inscripción? Esta acción no se puede deshacer.');">
                                    @csrf @method('DELETE')
                                    <button class="sg-btn sg-btn--peligro sg-btn--icono" title="Eliminar"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-sg.vacio icono="inbox" titulo="Bandeja vacía">No hay inscripciones para mostrar.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($inscripciones->hasPages())
            <div class="sg-card__foot">{{ $inscripciones->links() }}</div>
        @endif
    </div>
@endsection
