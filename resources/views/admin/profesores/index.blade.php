@extends('layouts.admin')

@section('titulo', 'Asignación de Materias y Horarios')

@section('contenido')
    <x-sg.encabezado titulo="Profesores y horarios" subtitulo="Carga y consulta de materias asignadas a cada docente — Año lectivo {{ $anioLectivo?->anio }}" eyebrow="Plantel docente" icono="briefcase" />

    <div class="sg-card sg-revelar mb-8 overflow-hidden">
    <div class="relative bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#1565C0] px-6 py-4 overflow-hidden">
        <span class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[10px] border-[#FFC107]/25"></span>
        <h2 class="relative text-white font-display font-bold flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-[#FFD54F]"><x-sg.icono nombre="calendar" class="h-5 w-5" /></span> Asignar materia y horario a profesor</h2>
    </div>
    <div class="p-6">
        <form id="form-asignacion" method="POST" action="{{ route('admin.profesores.asignaciones.store') }}" class="grid sm:grid-cols-2 gap-4 mb-4">
            @csrf
            <div>
                <label class="sg-label">PROFESOR *</label>
                <select name="id_profesor" id="input-id-profesor" required class="sg-input w-full">
                    <option value="">Seleccioná profesor...</option>
                    @foreach ($profesoresActivos as $profesor)
                        <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }} — {{ $profesor->especialidad->nombre_especialidad }} ({{ $profesor->condicion }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">MATERIA *</label>
                <select name="id_materia" id="input-id-materia" required class="sg-input w-full">
                    <option value="">Ej: Organización y Gestión en Enfermería</option>
                    @foreach ($carreras as $carrera)
                        <optgroup label="{{ $carrera->nombre_carrera }}">
                            @foreach ($carrera->materias()->with('nombreMateria', 'anioCursada')->orderBy('numero_orden')->get() as $materia)
                                <option value="{{ $materia->id_materia }}">{{ $materia->nombre }} ({{ $materia->anioCursada->nombre_anio }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">AÑO LECTIVO *</label>
                <select name="id_anio_lectivo" id="input-id-anio-lectivo" required class="sg-input w-full">
                    @foreach ($aniosLectivos as $anio)
                        <option value="{{ $anio->id_anio_lectivo }}">{{ $anio->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="sg-label">HORA INICIO *</label>
                    <input type="time" name="hora_inicio" id="input-hora-inicio" required class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">HORA FIN *</label>
                    <input type="time" name="hora_fin" id="input-hora-fin" required class="sg-input w-full">
                </div>
            </div>
            <div>
                <label class="sg-label">AULA</label>
                <input type="text" maxlength="20" name="aula" id="input-aula" placeholder="Ej: Aula 3" class="sg-input w-full">
            </div>
            <div class="sm:col-span-2">
                <label class="sg-label">DÍAS DE CURSADA *</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia)
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-600 bg-white border-[1.5px] border-slate-200 rounded-xl px-4 py-2 cursor-pointer transition-colors hover:border-[#4FC3F7] has-[:checked]:bg-[#0B3D91] has-[:checked]:border-[#0B3D91] has-[:checked]:text-white">
                            <input type="checkbox" name="dias[]" value="{{ $dia }}" class="dia-checkbox h-3.5 w-3.5"> {{ $dia }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <span id="editando-aviso" class="hidden sg-badge sg-badge--amarillo self-center mr-auto">Editando una asignación existente — al guardar se actualizará.</span>
                <button type="reset" onclick="cancelarEdicionAsignacion()" class="sg-btn sg-btn--borde">Limpiar</button>
                <button type="submit" class="sg-btn sg-btn--primario"><x-sg.icono nombre="check" /> Guardar asignación</button>
            </div>
        </form>

        <details id="detalle-padron" class="text-sm mt-6 rounded-2xl border-[1.5px] border-dashed border-slate-200 bg-slate-50/60 open:bg-white open:border-[#BDE5FA] transition-colors">
            <summary class="cursor-pointer list-none flex items-center gap-3 px-4 py-3 text-[#0B3D91] font-semibold"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="user-plus" /></span> Agregar nuevo profesor al padrón <x-sg.icono nombre="chevron-down" class="h-4 w-4 ml-auto" /></summary>
            <div class="px-4 pb-4">
            <form id="form-profesor" method="POST" action="{{ route('admin.profesores.store') }}" class="grid sm:grid-cols-3 gap-3 mt-3">
                @csrf
                <input type="hidden" name="_method" id="input-profesor-method" value="">
                <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" id="input-profesor-dni" placeholder="DNI" required class="sg-input">
                <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="apellido" id="input-profesor-apellido" placeholder="Apellido" required class="sg-input">
                <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="nombre" id="input-profesor-nombre" placeholder="Nombre" required class="sg-input">
                <input type="email" maxlength="25" name="email" id="input-profesor-email" placeholder="Email (opcional)" class="sg-input">
                <div class="flex gap-2 min-w-0">
                    <select name="id_especialidad" id="input-profesor-especialidad" required class="sg-input min-w-0 flex-1">
                        <option value="">Especialidad...</option>
                        @foreach ($especialidades as $especialidad)
                            <option value="{{ $especialidad->id_especialidad }}">{{ $especialidad->nombre_especialidad }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirModalEspecialidad()" title="Agregar nueva especialidad"
                            class="shrink-0 w-[46px] flex items-center justify-center rounded-xl bg-blue-100 text-[#0B3D91] hover:bg-[#0B3D91] hover:text-white transition-colors"><x-sg.icono nombre="plus" /></button>
                </div>
                <select name="condicion" id="input-profesor-condicion" required class="sg-input">
                    <option value="">Condición...</option>
                    <option value="Titular">Titular</option>
                    <option value="Suplente">Suplente</option>
                </select>
                <div class="sm:col-span-3 flex justify-end items-center gap-3">
                    <span id="editando-profesor-aviso" class="hidden sg-badge sg-badge--amarillo mr-auto">Editando un profesor existente — al guardar se actualizará.</span>
                    <button type="button" onclick="cancelarEdicionProfesor()" class="sg-btn sg-btn--borde">Limpiar</button>
                    <button type="submit" id="input-profesor-submit" class="sg-btn sg-btn--azul">Guardar docente</button>
                </div>
            </form>
            </div>
        </details>
    </div>
    </div>

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="users" /></span>
        <h2>Profesores registrados</h2>
    </div>
    <div class="sg-card sg-revelar mb-8 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Profesor</th>
                    <th>Especialidad</th>
                    <th>Condición</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($profesoresPaginados as $profesor)
                    @php
                        $edicionProfesorPayload = [
                            'id_profesor' => $profesor->id_profesor,
                            'dni' => $profesor->dni,
                            'apellido' => $profesor->apellido,
                            'nombre' => $profesor->nombre,
                            'email' => $profesor->email,
                            'id_especialidad' => $profesor->id_especialidad,
                            'condicion' => $profesor->condicion,
                        ];
                    @endphp
                    <tr class="{{ $profesor->activo ? '' : 'opacity-60' }}">
                        <td>
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="sg-avatar">{{ mb_substr($profesor->nombre, 0, 1) }}{{ mb_substr($profesor->apellido, 0, 1) }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $profesor->apellido }}, {{ $profesor->nombre }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-[260px]">DNI {{ $profesor->dni }}@if ($profesor->email) · {{ $profesor->email }}@endif</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                                <x-sg.icono nombre="academic" class="h-4 w-4 shrink-0 text-[#4FC3F7]" />
                                <span class="leading-tight">{{ $profesor->especialidad->nombre_especialidad }}</span>
                            </span>
                        </td>
                        <td>
                            <span class="sg-badge sg-badge--sin-punto {{ $profesor->condicion === 'Titular' ? 'sg-badge--azul' : 'sg-badge--amarillo' }}">{{ $profesor->condicion }}</span>
                        </td>
                        <td>
                            <span class="sg-badge {{ $profesor->activo ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                                {{ $profesor->activo ? 'Activo' : 'Dado de baja' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" class="sg-btn sg-btn--suave sg-btn--icono" title="Editar profesor" aria-label="Editar profesor"
                                        onclick='editarProfesor(@json($edicionProfesorPayload))'><x-sg.icono nombre="pencil" class="!h-4 !w-4" /></button>
                                @if ($profesor->activo)
                                    <form method="POST" action="{{ route('admin.profesores.baja', $profesor) }}" data-confirm="¿Dar de baja a este profesor? Ya no va a poder asignarse a nuevas materias ni tribunales.">
                                        @csrf @method('PUT')
                                        <button class="sg-btn sg-btn--peligro sg-btn--icono" title="Dar de baja" aria-label="Dar de baja"><x-sg.icono nombre="user-minus" class="!h-4 !w-4" /></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.profesores.reactivar', $profesor) }}">
                                        @csrf @method('PUT')
                                        <button class="sg-btn sg-btn--verde sg-btn--icono" title="Reactivar" aria-label="Reactivar"><x-sg.icono nombre="refresh" class="!h-4 !w-4" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-sg.vacio icono="briefcase">No hay profesores cargados.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($profesoresPaginados->hasPages())
            <div class="sg-card__foot">{{ $profesoresPaginados->links() }}</div>
        @endif
    </div>

    {{-- Modal: especialidades --}}
    <div id="modal-especialidad" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6 max-h-[85vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="plus" /></span> Especialidades</h3>
                <button type="button" onclick="cerrarModalEspecialidad()" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <p class="text-xs text-slate-400 -mt-2 mb-4">Áreas de formación del profesor (ej. Enfermería Pediátrica, Farmacología) — no es el nombre de una materia puntual, eso se elige al armar la asignación.</p>

            @if ($errors->has('nombre_especialidad'))
                <p class="text-xs text-red-500 mb-3">{{ $errors->first('nombre_especialidad') }}</p>
            @endif

            @if ($especialidades->isNotEmpty())
                <div class="space-y-2 mb-5">
                    @foreach ($especialidades as $especialidad)
                        <div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors">
                            <form method="POST" action="{{ route('admin.profesores.especialidades.update', $especialidad) }}" class="flex flex-1 gap-2 items-center min-w-0">
                                @csrf @method('PUT')
                                <input type="text" name="nombre_especialidad" value="{{ $especialidad->nombre_especialidad }}" required maxlength="45"
                                       class="sg-input sg-input--chico min-w-0 flex-1">
                                <button type="submit" title="Guardar cambios" class="sg-btn sg-btn--suave sg-btn--chico shrink-0">Guardar</button>
                            </form>
                            <form method="POST" action="{{ route('admin.profesores.especialidades.destroy', $especialidad) }}" onsubmit="return confirm('¿Eliminar la especialidad \'{{ $especialidad->nombre_especialidad }}\'?');">
                                @csrf @method('DELETE')
                                <button type="submit" title="Eliminar especialidad" class="sg-btn sg-btn--peligro sg-btn--icono shrink-0"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="sg-label mb-2">Agregar nueva especialidad</p>
            <form method="POST" action="{{ route('admin.profesores.especialidades.store') }}" class="flex gap-2">
                @csrf
                <input type="text" name="nombre_especialidad" required autofocus maxlength="45" placeholder="Ej: Enfermería Pediátrica"
                       class="sg-input sg-input--chico min-w-0 flex-1">
                <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico shrink-0">Agregar</button>
            </form>
        </div>
    </div>

    <script>
        function abrirModalEspecialidad() {
            const modal = document.getElementById('modal-especialidad');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalEspecialidad() {
            const modal = document.getElementById('modal-especialidad');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function editarAsignacion(asignacion) {
            document.getElementById('input-id-profesor').value = asignacion.id_profesor;
            document.getElementById('input-id-materia').value = asignacion.id_materia;
            document.getElementById('input-id-anio-lectivo').value = asignacion.id_anio_lectivo;
            document.getElementById('input-aula').value = asignacion.aula ?? '';
            document.getElementById('input-hora-inicio').value = asignacion.hora_inicio ?? '';
            document.getElementById('input-hora-fin').value = asignacion.hora_fin ?? '';

            document.querySelectorAll('.dia-checkbox').forEach(function (checkbox) {
                checkbox.checked = asignacion.dias.includes(checkbox.value);
            });

            document.getElementById('editando-aviso').classList.remove('hidden');
            document.getElementById('form-asignacion').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelarEdicionAsignacion() {
            document.getElementById('editando-aviso').classList.add('hidden');
        }

        const rutaBaseProfesores = @json(url('/admin/profesores'));
        const rutaGuardarProfesor = @json(route('admin.profesores.store'));

        function editarProfesor(profesor) {
            document.getElementById('form-profesor').action = rutaBaseProfesores + '/' + profesor.id_profesor;
            document.getElementById('input-profesor-method').value = 'PUT';
            document.getElementById('input-profesor-dni').value = profesor.dni;
            document.getElementById('input-profesor-apellido').value = profesor.apellido;
            document.getElementById('input-profesor-nombre').value = profesor.nombre;
            document.getElementById('input-profesor-email').value = profesor.email ?? '';
            document.getElementById('input-profesor-especialidad').value = profesor.id_especialidad;
            document.getElementById('input-profesor-condicion').value = profesor.condicion;
            document.getElementById('input-profesor-submit').textContent = 'Guardar cambios';
            document.getElementById('editando-profesor-aviso').classList.remove('hidden');
            document.getElementById('detalle-padron').open = true;
            document.getElementById('detalle-padron').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelarEdicionProfesor() {
            document.getElementById('form-profesor').reset();
            document.getElementById('form-profesor').action = rutaGuardarProfesor;
            document.getElementById('input-profesor-method').value = '';
            document.getElementById('input-profesor-submit').textContent = 'Guardar docente';
            document.getElementById('editando-profesor-aviso').classList.add('hidden');
        }
    </script>

    <div class="sg-seccion-titulo sg-revelar">
        <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="clock" /></span>
        <h2>Asignaciones ya cargadas</h2>
    </div>
    <div class="sg-card sg-revelar overflow-hidden">
        <div class="flex flex-wrap gap-2 p-4 pb-0">
            <a href="{{ route('admin.profesores.index', array_filter(['materia' => $filtros['materia'] ?? null])) }}"
               class="sg-btn sg-btn--chico {{ $sinAsignar ? 'sg-btn--borde' : 'sg-btn--azul' }}">Asignaciones cargadas</a>
            <a href="{{ route('admin.profesores.index', array_filter(['materia' => $filtros['materia'] ?? null, 'sin_asignar' => 1])) }}"
               class="sg-btn sg-btn--chico {{ $sinAsignar ? 'sg-btn--azul' : 'sg-btn--borde' }}">Materias sin profesor asignado
               @if ($materiasSinAsignar->total() > 0)<span class="sg-badge sg-badge--sin-punto sg-badge--rojo ml-1">{{ $materiasSinAsignar->total() }}</span>@endif</a>
        </div>

        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-slate-100 bg-slate-50/60">
            @if ($sinAsignar)
                <input type="hidden" name="sin_asignar" value="1">
            @else
                <select name="profesor" onchange="this.form.submit()" class="sg-input w-auto">
                    <option value="">Todos los profesores</option>
                    @foreach ($profesores as $profesor)
                        <option value="{{ $profesor->id_profesor }}" @selected(($filtros['profesor'] ?? null) == $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                    @endforeach
                </select>
                <select name="dia" onchange="this.form.submit()" class="sg-input w-auto">
                    <option value="">Todos los días</option>
                    @foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia)
                        <option value="{{ $dia }}" @selected(($filtros['dia'] ?? null) === $dia)>{{ $dia }}</option>
                    @endforeach
                </select>
            @endif
            <input type="text" name="materia" value="{{ $filtros['materia'] ?? '' }}" placeholder="Buscar materia..." maxlength="50" class="sg-input w-auto flex-1 min-w-[180px]">
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="search" /> Buscar</button>
        </form>

        @if ($sinAsignar)
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Materia</th>
                    <th>Carrera</th>
                    <th>Año</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($materiasSinAsignar as $materia)
                    <tr>
                        <td class="text-slate-700">{{ $materia->nombre }}</td>
                        <td class="text-xs text-blue-600">{{ $materia->carrera->nombre_carrera }}</td>
                        <td class="text-slate-500">{{ $materia->anioCursada->nombre_anio }}</td>
                        <td class="text-right">
                            <button type="button" class="sg-btn sg-btn--suave sg-btn--chico" onclick="preseleccionarMateria({{ $materia->id_materia }})">Asignar profesor</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><x-sg.vacio icono="calendar">No hay materias activas sin profesor asignado — ¡todas tienen al menos un profesor para este año lectivo!</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($materiasSinAsignar->hasPages())
            <div class="sg-card__foot">{{ $materiasSinAsignar->links() }}</div>
        @endif
        @else
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Profesor</th>
                    <th>Materia</th>
                    <th>Días</th>
                    <th>Horario</th>
                    <th>Aula</th>
                    <th>Año</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($asignaciones as $asignacion)
                    @php $primerHorario = $asignacion->horarios->first(); @endphp
                    <tr>
                        <td>
                            <p class="font-semibold text-slate-800">{{ $asignacion->profesor->apellido }}, {{ $asignacion->profesor->nombre }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $asignacion->profesor->especialidad->nombre_especialidad }}
                                <span class="ml-1 sg-badge sg-badge--sin-punto !py-0.5 {{ $asignacion->profesor->condicion === 'Titular' ? 'sg-badge--azul' : 'sg-badge--amarillo' }}">{{ $asignacion->profesor->condicion }}</span>
                            </p>
                        </td>
                        <td>
                            <p class="text-slate-700">{{ $asignacion->materia->nombre }}</p>
                            <p class="text-xs text-blue-600">{{ $asignacion->materia->carrera->nombre_carrera }}</p>
                        </td>
                        <td><div class="flex flex-wrap gap-1">@foreach ($asignacion->horarios->pluck('dia_semana') as $d)<span class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[11px] font-bold text-[#0B3D91]">{{ \Illuminate\Support\Str::limit($d, 3, '') }}</span>@endforeach</div></td>
                        <td class="text-slate-500">{{ $primerHorario ? substr($primerHorario->hora_desde, 0, 5) . ' – ' . substr($primerHorario->hora_fin, 0, 5) : '—' }}</td>
                        <td class="text-slate-500">{{ $asignacion->aula ?? '—' }}</td>
                        <td class="text-slate-500">{{ $asignacion->materia->anioCursada->nombre_anio }}</td>
                        @php
                            $edicionPayload = [
                                'id_profesor' => $asignacion->id_profesor,
                                'id_materia' => $asignacion->id_materia,
                                'id_anio_lectivo' => $asignacion->id_anio_lectivo,
                                'aula' => $asignacion->aula,
                                'hora_inicio' => $primerHorario ? substr($primerHorario->hora_desde, 0, 5) : '',
                                'hora_fin' => $primerHorario ? substr($primerHorario->hora_fin, 0, 5) : '',
                                'dias' => $asignacion->horarios->pluck('dia_semana'),
                            ];
                        @endphp
                        <td>
                            <div class="flex items-center gap-3">
                                <button type="button" class="sg-btn sg-btn--suave sg-btn--chico"
                                        onclick='editarAsignacion(@json($edicionPayload))'>Editar</button>
                                <form method="POST" action="{{ route('admin.profesores.asignaciones.destroy', $asignacion) }}" onsubmit="return confirm('¿Eliminar esta asignación?');">
                                    @csrf @method('DELETE')
                                    <button class="sg-btn sg-btn--peligro sg-btn--icono" title="Eliminar"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-sg.vacio icono="calendar">No hay asignaciones cargadas.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($asignaciones->hasPages())
            <div class="sg-card__foot">{{ $asignaciones->links() }}</div>
        @endif
        @endif
    </div>

    <script>
        function preseleccionarMateria(idMateria) {
            document.getElementById('input-id-materia').value = idMateria;
            document.getElementById('form-asignacion').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
@endsection
