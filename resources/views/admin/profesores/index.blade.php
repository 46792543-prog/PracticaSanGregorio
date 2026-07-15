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
        <form method="POST" action="{{ route('admin.profesores.asignaciones.store') }}" class="grid sm:grid-cols-2 gap-4 mb-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">PROFESOR *</label>
                <select name="profesor_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Seleccioná profesor...</option>
                    @foreach ($profesores as $profesor)
                        <option value="{{ $profesor->id }}">{{ $profesor->apellido }}, {{ $profesor->nombre }} — {{ $profesor->especialidad }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">MATERIA *</label>
                <select name="materia_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Ej: Organización y Gestión en Enfermería</option>
                    @foreach ($carreras as $carrera)
                        <optgroup label="{{ $carrera->nombre }}">
                            @foreach ($carrera->materias()->orderBy('numero_orden')->get() as $materia)
                                <option value="{{ $materia->id }}">{{ $materia->nombre }} ({{ $materia->anio_cursada }}°)</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO LECTIVO *</label>
                <select name="anio_lectivo_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($aniosLectivos as $anio)
                        <option value="{{ $anio->id }}">{{ $anio->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">HORA INICIO *</label>
                    <input type="time" name="hora_inicio" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">HORA FIN *</label>
                    <input type="time" name="hora_fin" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">AULA</label>
                <input type="text" name="aula" placeholder="Ej: Aula 3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO</label>
                <input type="text" name="turno" placeholder="Mañana" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-2">DÍAS DE CURSADA *</label>
                <div class="flex flex-wrap gap-2">
                    @foreach (['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'] as $valor => $texto)
                        <label class="flex items-center gap-1.5 text-sm bg-slate-50 border border-slate-200 rounded-full px-3 py-1.5 cursor-pointer has-[:checked]:bg-blue-100 has-[:checked]:border-[#1E4D8C]">
                            <input type="checkbox" name="dias[]" value="{{ $valor }}" class="h-3.5 w-3.5"> {{ $texto }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3">
                <button type="reset" class="rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold px-5 py-2">Limpiar</button>
                <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow transition text-white text-sm font-semibold px-5 py-2">Guardar asignación</button>
            </div>
        </form>

        <details class="text-sm">
            <summary class="cursor-pointer text-[#1E4D8C] font-semibold">+ Agregar nuevo profesor al padrón</summary>
            <form method="POST" action="{{ route('admin.profesores.store') }}" class="grid sm:grid-cols-4 gap-3 mt-3">
                @csrf
                <input type="text" name="dni" placeholder="DNI" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="apellido" placeholder="Apellido" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <input type="text" name="nombre" placeholder="Nombre" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <div class="flex gap-2">
                    <input type="text" name="especialidad" placeholder="Especialidad" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white text-sm font-semibold px-4">+</button>
                </div>
            </form>
        </details>
    </div>

    <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Asignaciones ya cargadas</p>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
        <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-slate-100">
            <select name="profesor" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los profesores</option>
                @foreach ($profesores as $profesor)
                    <option value="{{ $profesor->id }}" @selected(($filtros['profesor'] ?? null) == $profesor->id)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                @endforeach
            </select>
            <select name="dia" onchange="this.form.submit()" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los días</option>
                @foreach (['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado'] as $valor => $texto)
                    <option value="{{ $valor }}" @selected(($filtros['dia'] ?? null) === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
            <input type="text" name="materia" value="{{ $filtros['materia'] ?? '' }}" placeholder="Buscar materia..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
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
                    <tr>
                        <td class="px-6 py-3">
                            <p class="font-semibold text-slate-700">{{ $asignacion->profesor->apellido }}, {{ $asignacion->profesor->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $asignacion->profesor->especialidad }}</p>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-slate-700">{{ $asignacion->materia->nombre }}</p>
                            <p class="text-xs text-blue-600">{{ $asignacion->materia->carrera->nombre }}</p>
                        </td>
                        <td class="px-6 py-3 text-slate-500 capitalize">{{ collect($asignacion->dias_cursada)->map(fn ($d) => \Illuminate\Support\Str::limit($d, 3, ''))->implode(' ') }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ substr($asignacion->hora_inicio, 0, 5) }} – {{ substr($asignacion->hora_fin, 0, 5) }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $asignacion->aula ?? '—' }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $asignacion->materia->anio_cursada }}° año</td>
                        <td class="px-6 py-3">
                            <form method="POST" action="{{ route('admin.profesores.asignaciones.destroy', $asignacion) }}" onsubmit="return confirm('¿Eliminar esta asignación?');">
                                @csrf @method('DELETE')
                                <button class="text-red-500 text-xs font-semibold hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-slate-400">No hay asignaciones cargadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
