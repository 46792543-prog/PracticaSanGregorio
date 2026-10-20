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
                        <select name="id_condicion" required data-selector-condicion class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                            <optgroup label="En curso">
                                @foreach ($condiciones->whereIn('nombre_condicion', ['Cursando', 'Regular', 'Libre']) as $condicion)
                                    <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $condicion->nombre_condicion }}">{{ $condicion->nombre_condicion }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Aprobación definitiva">
                                @foreach ($condiciones->whereIn('nombre_condicion', ['Promocionada', 'Examen Final', 'Equivalencia']) as $condicion)
                                    <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $condicion->nombre_condicion }}">{{ $condicion->nombre_condicion }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div data-campo-nota>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nota (entre 1 y 10, sin decimales):</label>
                        <input type="number" step="1" min="1" max="10" name="nota_cursada" data-input-nota
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-veces>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Veces rendida (opcional):</label>
                        <input type="number" min="0" max="50" name="veces_rendida" data-input-veces
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-equivalencia class="sm:col-span-2 lg:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Institución de origen (equivalencia):</label>
                        <input type="text" name="institucion_equivalencia" data-input-equivalencia maxlength="255"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-equivalencia>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">N° de resolución (equivalencia):</label>
                        <input type="text" name="resolucion_equivalencia" data-input-equivalencia maxlength="255"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha:</label>
                        <input type="date" name="fecha_ultima_modificacion" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-4">
                    Mientras la materia está "en curso" (Cursando, Regular o Libre) no se carga nota — recién pide nota cuando se marca una aprobación definitiva (Promocionada, Examen Final o Equivalencia), y ahí tiene que ser de 4 a 10. "Libre" admite una nota opcional, pero menor a 4. Una "Equivalencia" además pide institución de origen y número de resolución, y no lleva veces rendida. Si las veces rendida son más de 3, la materia se marca como "Libre" automáticamente, sin importar la condición tildada arriba.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">Guardar Materia</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <h2 class="font-bold text-slate-700 px-6 pt-5 pb-3">Historial actual del alumno</h2>
            <p class="text-xs text-slate-400 px-6 pb-3 -mt-2">Para corregir una materia ya cargada, editá los campos y tocá "Guardar" en su fila.</p>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                        <th class="px-6 py-3">Materia</th>
                        <th class="px-6 py-3">Año lectivo</th>
                        <th class="px-6 py-3">Condición</th>
                        <th class="px-6 py-3">Nota</th>
                        <th class="px-6 py-3">Veces rendida</th>
                        <th class="px-6 py-3">Institución (equivalencia)</th>
                        <th class="px-6 py-3">N° resolución (equivalencia)</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historial as $item)
                        @php $formId = 'form-historial-' . $item->id_historial; @endphp
                        <tr>
                            <td class="px-6 py-3 font-semibold text-slate-700">
                                {{ $item->materia->nombre }}
                                @if ($item->materia->anioCursada)
                                    <span class="font-normal text-slate-400">({{ $item->materia->anioCursada->nombre_anio }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <select form="{{ $formId }}" name="id_anio_lectivo" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    @foreach ($aniosLectivos as $anio)
                                        <option value="{{ $anio->id_anio_lectivo }}" @selected($item->id_anio_lectivo == $anio->id_anio_lectivo)>{{ $anio->anio }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-3">
                                <select form="{{ $formId }}" name="id_condicion" data-selector-condicion class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    <optgroup label="En curso">
                                        @foreach ($condiciones->whereIn('nombre_condicion', ['Cursando', 'Regular', 'Libre']) as $condicion)
                                            <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $condicion->nombre_condicion }}" @selected($item->id_condicion == $condicion->id_condicion)>{{ $condicion->nombre_condicion }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Aprobación definitiva">
                                        @foreach ($condiciones->whereIn('nombre_condicion', ['Promocionada', 'Examen Final', 'Equivalencia']) as $condicion)
                                            <option value="{{ $condicion->id_condicion }}" data-nombre="{{ $condicion->nombre_condicion }}" @selected($item->id_condicion == $condicion->id_condicion)>{{ $condicion->nombre_condicion }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-nota>
                                    <input form="{{ $formId }}" type="number" step="1" min="1" max="10" name="nota_cursada" value="{{ $item->nota_cursada }}" data-input-nota
                                           class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-veces>
                                    <input form="{{ $formId }}" type="number" min="0" max="50" name="veces_rendida" value="{{ $item->veces_rendida }}" data-input-veces
                                           class="w-16 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-equivalencia>
                                    <input form="{{ $formId }}" type="text" name="institucion_equivalencia" value="{{ $item->institucion_equivalencia }}" data-input-equivalencia maxlength="255"
                                           class="w-32 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-equivalencia>
                                    <input form="{{ $formId }}" type="text" name="resolucion_equivalencia" value="{{ $item->resolucion_equivalencia }}" data-input-equivalencia maxlength="255"
                                           class="w-28 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <button form="{{ $formId }}" type="submit" class="text-[#1E4D8C] hover:underline font-semibold text-xs">Guardar</button>
                                <button form="form-eliminar-{{ $item->id_historial }}" type="submit" class="text-red-500 hover:underline font-semibold text-xs ml-2">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-10 text-center text-slate-400">Este alumno todavía no tiene historial cargado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach ($historial as $item)
            <form id="form-historial-{{ $item->id_historial }}" method="POST" action="{{ route('admin.alumnos.historial-previo.update', $item) }}" class="hidden">
                @csrf
                @method('PUT')
                <input type="hidden" name="busqueda_actual" value="{{ $busqueda }}">
                <input type="hidden" name="fecha_ultima_modificacion" value="{{ $item->fecha_ultima_modificacion->format('Y-m-d') }}">
            </form>
            <form id="form-eliminar-{{ $item->id_historial }}" method="POST" action="{{ route('admin.alumnos.historial-previo.destroy', $item) }}?busqueda={{ urlencode($busqueda) }}" class="hidden" onsubmit="return confirm('¿Eliminar esta materia del historial?');">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
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

        // Según la condición elegida: la nota se oculta en curso, se pide
        // obligatoria en una aprobación definitiva y queda opcional en Libre.
        // Una Equivalencia no lleva veces rendida, pero sí institución y resolución.
        const SIN_NOTA = ['Cursando', 'Regular'];

        function actualizarCamposPorCondicion(selectCondicion) {
            const contenedor = selectCondicion.closest('tr') || selectCondicion.closest('form');
            if (!contenedor) return;

            const nombreCondicion = selectCondicion.selectedOptions[0]?.dataset.nombre;
            const esEquivalencia = nombreCondicion === 'Equivalencia';

            const campoNota = contenedor.querySelector('[data-campo-nota]');
            const inputNota = contenedor.querySelector('[data-input-nota]');
            if (campoNota && inputNota) {
                if (SIN_NOTA.includes(nombreCondicion)) {
                    campoNota.classList.add('hidden');
                    inputNota.value = '';
                    inputNota.required = false;
                    inputNota.disabled = true;
                } else {
                    campoNota.classList.remove('hidden');
                    inputNota.disabled = false;
                    inputNota.required = nombreCondicion !== 'Libre';
                }
            }

            const campoVeces = contenedor.querySelector('[data-campo-veces]');
            const inputVeces = contenedor.querySelector('[data-input-veces]');
            if (campoVeces && inputVeces) {
                if (esEquivalencia) {
                    campoVeces.classList.add('hidden');
                    inputVeces.value = '';
                    inputVeces.disabled = true;
                } else {
                    campoVeces.classList.remove('hidden');
                    inputVeces.disabled = false;
                }
            }

            contenedor.querySelectorAll('[data-campo-equivalencia]').forEach((campo) => {
                const inputEquivalencia = campo.querySelector('[data-input-equivalencia]');
                if (esEquivalencia) {
                    campo.classList.remove('hidden');
                    if (inputEquivalencia) {
                        inputEquivalencia.disabled = false;
                        inputEquivalencia.required = true;
                    }
                } else {
                    campo.classList.add('hidden');
                    if (inputEquivalencia) {
                        inputEquivalencia.value = '';
                        inputEquivalencia.disabled = true;
                        inputEquivalencia.required = false;
                    }
                }
            });
        }

        document.querySelectorAll('[data-selector-condicion]').forEach((select) => {
            actualizarCamposPorCondicion(select);
            select.addEventListener('change', () => actualizarCamposPorCondicion(select));
        });
    </script>
@endsection
