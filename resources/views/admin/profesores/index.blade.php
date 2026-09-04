@extends('layouts.admin')

@section('titulo', 'Asignación de Materias y Horarios')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">🎓 Asignación de Materias y Horarios</h1>
        <p class="text-sm text-slate-400">Carga y consulta de materias asignadas a cada docente — Año lectivo {{ $anioLectivo?->anio }}</p>
    </div>

    <div class="bg-[#1E4D8C] rounded-t-xl px-6 py-3">
        <h2 class="text-white font-bold text-sm">📌 Asignar Materia y Horario a Profesor</h2>
    </div>
    <div class="bg-white rounded-b-xl shadow-sm p-6 mb-4">
        <form id="form-asignacion" method="POST" action="{{ route('admin.profesores.asignaciones.store') }}" class="grid sm:grid-cols-2 gap-4 mb-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">PROFESOR *</label>
                <select name="id_profesor" id="input-id-profesor" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Seleccioná profesor...</option>
                    @foreach ($profesoresActivos as $profesor)
                        <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }} — {{ $profesor->especialidad->nombre_especialidad }} ({{ $profesor->condicion }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MATERIA *</label>
                <select name="id_materia" id="input-id-materia" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO LECTIVO *</label>
                <select name="id_anio_lectivo" id="input-id-anio-lectivo" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($aniosLectivos as $anio)
                        <option value="{{ $anio->id_anio_lectivo }}">{{ $anio->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">HORA INICIO *</label>
                    <input type="time" name="hora_inicio" id="input-hora-inicio" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">HORA FIN *</label>
                    <input type="time" name="hora_fin" id="input-hora-fin" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">AULA</label>
                <input type="text" maxlength="20" name="aula" id="input-aula" placeholder="Ej: Aula 3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-2">DÍAS DE CURSADA *</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia)
                        <label class="flex items-center gap-1.5 text-sm bg-slate-50 border border-slate-200 rounded-full px-3 py-1.5 cursor-pointer has-[:checked]:bg-blue-100 has-[:checked]:border-[#1E4D8C]">
                            <input type="checkbox" name="dias[]" value="{{ $dia }}" class="dia-checkbox h-3.5 w-3.5"> {{ $dia }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3">
                <span id="editando-aviso" class="hidden text-xs text-amber-600 font-semibold self-center mr-auto">✏️ Editando una asignación existente — al guardar se actualizará.</span>
                <button type="reset" onclick="cancelarEdicionAsignacion()" class="rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold px-5 py-2">Limpiar</button>
                <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow transition text-white text-sm font-semibold px-5 py-2">Guardar asignación</button>
            </div>
        </form>

        <details id="detalle-padron" class="text-sm">
            <summary class="cursor-pointer text-[#1E4D8C] font-semibold">+ Agregar nuevo profesor al padrón</summary>
            <form id="form-profesor" method="POST" action="{{ route('admin.profesores.store') }}" class="grid sm:grid-cols-3 gap-3 mt-3">
                @csrf
                <input type="hidden" name="_method" id="input-profesor-method" value="">
                <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="dni" id="input-profesor-dni" placeholder="DNI" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="apellido" id="input-profesor-apellido" placeholder="Apellido" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" data-solo="letras" data-max-len="25" maxlength="25" name="nombre" id="input-profesor-nombre" placeholder="Nombre" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="email" maxlength="25" name="email" id="input-profesor-email" placeholder="Email (opcional)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div class="flex gap-2 min-w-0">
                    <select name="id_especialidad" id="input-profesor-especialidad" required class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Especialidad...</option>
                        @foreach ($especialidades as $especialidad)
                            <option value="{{ $especialidad->id_especialidad }}">{{ $especialidad->nombre_especialidad }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirModalEspecialidad()" title="Agregar nueva especialidad"
                            class="shrink-0 w-10 h-10 flex items-center justify-center rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-lg font-bold leading-none hover:bg-blue-50">+</button>
                </div>
                <select name="condicion" id="input-profesor-condicion" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Condición...</option>
                    <option value="Titular">Titular</option>
                    <option value="Suplente">Suplente</option>
                </select>
                <div class="sm:col-span-3 flex justify-end items-center gap-3">
                    <span id="editando-profesor-aviso" class="hidden text-xs text-amber-600 font-semibold mr-auto">✏️ Editando un profesor existente — al guardar se actualizará.</span>
                    <button type="button" onclick="cancelarEdicionProfesor()" class="rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold px-5 py-2">Limpiar</button>
                    <button type="submit" id="input-profesor-submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white text-sm font-semibold px-6 py-2">Guardar docente</button>
                </div>
            </form>
        </details>
    </div>

    <p class="text-xs font-semibold text-slate-400 uppercase mb-2 mt-6">Profesores registrados</p>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-4">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase">
                    <th class="px-6 py-3 font-semibold">DNI</th>
                    <th class="px-6 py-3 font-semibold">Profesor</th>
                    <th class="px-6 py-3 font-semibold">Email</th>
                    <th class="px-6 py-3 font-semibold">Especialidad</th>
                    <th class="px-6 py-3 font-semibold">Condición</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($profesores as $profesor)
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
                    <tr class="{{ $profesor->activo ? '' : 'opacity-50' }}">
                        <td class="px-6 py-3 text-slate-500">{{ $profesor->dni }}</td>
                        <td class="px-6 py-3 font-semibold text-slate-700">{{ $profesor->apellido }}, {{ $profesor->nombre }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $profesor->email ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $profesor->especialidad->nombre_especialidad }}</td>
                        <td class="px-6 py-3">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $profesor->condicion === 'Titular' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">{{ $profesor->condicion }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $profesor->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                {{ $profesor->activo ? 'Activo' : 'Dado de baja' }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap">
                                <button type="button" class="text-[#1E4D8C] hover:underline"
                                        onclick='editarProfesor(@json($edicionProfesorPayload))'>Editar</button>
                                @if ($profesor->activo)
                                    <form method="POST" action="{{ route('admin.profesores.baja', $profesor) }}" data-confirm="¿Dar de baja a este profesor? Ya no va a poder asignarse a nuevas materias ni tribunales.">
                                        @csrf @method('PUT')
                                        <button class="text-red-500 hover:underline">Dar de baja</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('admin.profesores.reactivar', $profesor) }}">
                                        @csrf @method('PUT')
                                        <button class="text-green-600 hover:underline">Reactivar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No hay profesores cargados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal: nueva especialidad --}}
    <div id="modal-especialidad" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800">➕ Nueva especialidad</h3>
                <button type="button" onclick="cerrarModalEspecialidad()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.profesores.especialidades.store') }}">
                @csrf
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DE LA ESPECIALIDAD</label>
                <input type="text" name="nombre_especialidad" required autofocus maxlength="45" placeholder="Ej: Enfermería Pediátrica"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalEspecialidad()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar especialidad</button>
                </div>
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

    <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Asignaciones ya cargadas</p>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-slate-100">
            <select name="profesor" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los profesores</option>
                @foreach ($profesores as $profesor)
                    <option value="{{ $profesor->id_profesor }}" @selected(($filtros['profesor'] ?? null) == $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                @endforeach
            </select>
            <select name="dia" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los días</option>
                @foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes'] as $dia)
                    <option value="{{ $dia }}" @selected(($filtros['dia'] ?? null) === $dia)>{{ $dia }}</option>
                @endforeach
            </select>
            <input type="text" name="materia" value="{{ $filtros['materia'] ?? '' }}" placeholder="Buscar materia..." maxlength="50" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <button class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white text-sm font-semibold px-5">Buscar</button>
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase">
                    <th class="px-6 py-3 font-semibold">Profesor</th>
                    <th class="px-6 py-3 font-semibold">Materia</th>
                    <th class="px-6 py-3 font-semibold">Días</th>
                    <th class="px-6 py-3 font-semibold">Horario</th>
                    <th class="px-6 py-3 font-semibold">Aula</th>
                    <th class="px-6 py-3 font-semibold">Año</th>
                    <th class="px-6 py-3 font-semibold"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($asignaciones as $asignacion)
                    @php $primerHorario = $asignacion->horarios->first(); @endphp
                    <tr>
                        <td class="px-6 py-3">
                            <p class="font-semibold text-slate-700">{{ $asignacion->profesor->apellido }}, {{ $asignacion->profesor->nombre }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $asignacion->profesor->especialidad->nombre_especialidad }}
                                <span class="ml-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $asignacion->profesor->condicion === 'Titular' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">{{ $asignacion->profesor->condicion }}</span>
                            </p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-700">{{ $asignacion->materia->nombre }}</p>
                            <p class="text-xs text-blue-600">{{ $asignacion->materia->carrera->nombre_carrera }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-500">{{ $asignacion->horarios->pluck('dia_semana')->map(fn ($d) => \Illuminate\Support\Str::limit($d, 3, ''))->implode(' ') }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $primerHorario ? substr($primerHorario->hora_desde, 0, 5) . ' – ' . substr($primerHorario->hora_fin, 0, 5) : '—' }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $asignacion->aula ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $asignacion->materia->anioCursada->nombre_anio }}</td>
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
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <button type="button" class="text-[#1E4D8C] text-xs font-semibold hover:underline"
                                        onclick='editarAsignacion(@json($edicionPayload))'>Editar</button>
                                <form method="POST" action="{{ route('admin.profesores.asignaciones.destroy', $asignacion) }}" onsubmit="return confirm('¿Eliminar esta asignación?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 text-xs font-semibold hover:underline">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No hay asignaciones cargadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
