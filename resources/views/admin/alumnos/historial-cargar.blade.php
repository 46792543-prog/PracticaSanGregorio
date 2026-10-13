@extends('layouts.admin')

@section('titulo', 'Cargar historial académico')

@section('contenido')
    @php
        $materiasPorCarrera = $carreras->mapWithKeys(fn ($carrera) => [
            $carrera->id_carrera => $carrera->materias->map(fn ($materia) => [
                'id' => $materia->id_materia,
                'nombre' => $materia->nombre . ' (' . ($materia->anioCursada->nombre_anio ?? '—') . ')',
            ])->values(),
        ]);
    @endphp

    <h1 class="text-xl font-bold text-slate-800 mb-1">Cargar historial académico de un alumno</h1>
    <p class="text-sm text-slate-400 mb-6 max-w-3xl">
        Para alumnos que ya tenían materias hechas de antes (otra institución, o de antes de usar este sistema).
        Buscá al alumno, elegí la materia y cargá directamente su condición y nota — sin pasar por autoinscripción ni mesa de examen.
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <h2 class="font-bold text-slate-700 mb-4">1. Buscar alumno (ya inscripto a una carrera)</h2>
        <form method="GET" action="{{ route('admin.alumnos.historial-previo.index') }}" class="flex items-end gap-3">
            <div class="flex-1 max-w-sm">
                <label class="block text-xs font-semibold text-slate-500 mb-1">Apellido, nombre o DNI:</label>
                <input type="text" name="busqueda" value="{{ $busqueda }}" placeholder="Ej: García"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
            </div>
            <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow-md hover:bg-[#173d70] transition text-white font-semibold text-sm px-6 py-2.5">Buscar</button>
            @if ($alumno)
                <span class="text-sm text-slate-600 pb-2.5">Alumno: <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong> (DNI {{ $alumno->dni }})</span>
            @elseif ($busqueda)
                <span class="text-sm text-red-600 pb-2.5">No se encontró ningún alumno inscripto que coincida con "{{ $busqueda }}".</span>
            @endif
        </form>
    </div>

    @if ($alumno)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <h2 class="font-bold text-slate-700 mb-4">2. Cargar una materia</h2>

            <form method="POST" action="{{ route('admin.alumnos.historial-previo.store') }}">
                @csrf
                <input type="hidden" name="id_persona_alumno" value="{{ $alumno->id_persona }}">
                <input type="hidden" name="busqueda_actual" value="{{ $busqueda }}">

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Carrera:</label>
                        <select id="select-carrera" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            <option value="">Seleccioná carrera...</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Materia:</label>
                        <select name="id_materia" id="select-materia" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            <option value="">Elegí la carrera primero...</option>
                        </select>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Año lectivo:</label>
                        <select name="id_anio_lectivo" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach ($aniosLectivos as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}">{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Condición:</label>
                        <select name="id_condicion" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            @foreach ($condiciones as $condicion)
                                <option value="{{ $condicion->id_condicion }}">{{ $condicion->nombre_condicion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nota (opcional):</label>
                        <input type="number" step="0.01" min="1" max="10" name="nota_cursada"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Veces rendida (opcional):</label>
                        <input type="number" min="0" max="50" name="veces_rendida"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha:</label>
                        <input type="date" name="fecha_ultima_modificacion" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-4">
                    No se puede guardar como "Aprobada" con nota menor a 4. Si las veces rendida son más de 3, la materia se marca como "Libre" automáticamente, sin importar la condición tildada arriba.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">Guardar Materia</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <h2 class="font-bold text-slate-700 px-6 pt-5 pb-3">Historial actual del alumno</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="px-6 py-3">Materia</th>
                        <th class="px-6 py-3">Año lectivo</th>
                        <th class="px-6 py-3">Condición</th>
                        <th class="px-6 py-3">Nota</th>
                        <th class="px-6 py-3">Veces rendida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historial as $item)
                        <tr>
                            <td class="px-6 py-3.5 font-semibold text-slate-700">{{ $item->materia->nombre }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $item->anioLectivo->anio }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $item->condicion->nombre_condicion }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $item->nota_cursada ?? '—' }}</td>
                            <td class="px-6 py-3.5 text-slate-500">{{ $item->veces_rendida ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-400">Este alumno todavía no tiene historial cargado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <script>
        const materiasPorCarrera = @json($materiasPorCarrera);
        const selectCarrera = document.getElementById('select-carrera');
        const selectMateria = document.getElementById('select-materia');

        selectCarrera?.addEventListener('change', () => {
            const materias = materiasPorCarrera[selectCarrera.value] || [];
            selectMateria.innerHTML = '<option value="">Seleccioná materia...</option>';
            materias.forEach((materia) => {
                const opcion = document.createElement('option');
                opcion.value = materia.id;
                opcion.textContent = materia.nombre;
                selectMateria.appendChild(opcion);
            });
        });
    </script>
@endsection
