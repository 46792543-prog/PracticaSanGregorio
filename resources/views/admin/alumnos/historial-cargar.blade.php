@extends('layouts.admin')

@section('titulo', 'Cargar historial académico')

@section('contenido')
    @php
        $materiasPorCarrera = $carreras->mapWithKeys(fn ($carrera) => [
            $carrera->id_carrera => $carrera->materias->map(fn ($materia) => [
                'id' => $materia->id_materia,
                'nombre' => $materia->nombre,
                'anio' => $materia->anioCursada->nombre_anio ?? null,
            ])->values(),
        ]);
        $materiasYaCargadas = ($historial ?? collect())->pluck('id_materia')->unique()->values();
        $idAnioPorNumero = $aniosLectivos->mapWithKeys(fn ($a) => [$a->anio => $a->id_anio_lectivo]);
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
                <span class="text-sm text-slate-600 pb-2.5">
                    Alumno: <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong> (DNI {{ $alumno->dni }})
                    @if ($anioIngreso)
                        — Ingresó en {{ $anioIngreso }}
                    @endif
                    @if ($nombreAnioActual)
                        · cursa {{ $nombreAnioActual }}
                    @endif
                </span>
            @elseif ($busqueda)
                <span class="text-sm text-red-600 pb-2.5">No se encontró ningún alumno inscripto que coincida con "{{ $busqueda }}".</span>
            @endif
        </form>
    </div>

    @if ($alumno)
        @php
            $idAnioIngreso = $aniosLectivos->firstWhere('anio', $anioIngreso)?->id_anio_lectivo;
            $claseInput = 'w-full rounded-lg border border-slate-300 px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]';
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <h2 class="font-bold text-slate-700 mb-1">2. Cargar un año completo</h2>
            <p class="text-xs text-slate-400 mb-4">Elegí la carrera y el año: aparecen todas sus materias. Elegí la condición de cada una (y nota si corresponde); el año lectivo y la fecha los podés copiar a todas con "Aplicar a todas". Guardalas juntas. Destildá las que no quieras cargar.</p>

            <form method="POST" action="{{ route('admin.alumnos.historial-previo.store-lote') }}" id="form-lote">
                @csrf
                <input type="hidden" name="id_persona_alumno" value="{{ $alumno->id_persona }}">
                <input type="hidden" name="busqueda_actual" value="{{ $busqueda }}">

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Carrera:</label>
                        <select id="lote-carrera" autocomplete="off" class="{{ $claseInput }}" style="padding-top:.625rem;padding-bottom:.625rem">
                            <option value="">Seleccioná carrera...</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" @selected($carreraAlumnoId && (int) $carrera->id_carrera === (int) $carreraAlumnoId)>{{ $carrera->nombre_carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Año de la carrera:</label>
                        <select name="anio_lote" id="lote-anio" autocomplete="off" class="{{ $claseInput }}" style="padding-top:.625rem;padding-bottom:.625rem">
                            <option value="">Elegí la carrera primero...</option>
                        </select>
                    </div>
                </div>

                <div id="lote-contenido" class="hidden">
                    {{-- Valores para copiar a todas las filas de una vez --}}
                    <div class="flex flex-wrap items-end gap-3 rounded-xl bg-slate-50 border border-slate-100 p-3 mb-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Año lectivo</label>
                            <select id="lote-todas-anio" class="{{ $claseInput }}">
                                @foreach ($aniosLectivos as $anio)
                                    <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === $idAnioIngreso)>{{ $anio->anio }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha</label>
                            <input type="date" id="lote-todas-fecha" value="{{ now()->format('Y-m-d') }}" class="{{ $claseInput }}">
                        </div>
                        <button type="button" id="lote-aplicar" class="rounded-xl border border-[#1E4D8C] text-[#1E4D8C] hover:bg-blue-50 font-semibold text-sm px-4 py-2">Aplicar a todas</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" style="min-width:1100px">
                            <thead>
                                <tr class="bg-slate-50 text-left text-xs font-semibold text-slate-500 border-b border-slate-100">
                                    <th class="px-3 py-2 w-10"><input type="checkbox" id="lote-tildar-todas" checked title="Tildar / destildar todas" class="h-4 w-4"></th>
                                    <th class="px-3 py-2">Materia</th>
                                    <th class="px-3 py-2 w-28">Año lectivo</th>
                                    <th class="px-3 py-2">Condición</th>
                                    <th class="px-3 py-2 w-36">Fecha</th>
                                    <th class="px-3 py-2 w-20">Nota</th>
                                    <th class="px-3 py-2 w-20">Veces</th>
                                    <th class="px-3 py-2">Institución (equiv.)</th>
                                    <th class="px-3 py-2">N° resolución (equiv.)</th>
                                </tr>
                            </thead>
                            <tbody id="lote-filas"></tbody>
                        </table>
                    </div>
                    <p class="text-xs text-slate-400 mt-3" id="lote-aviso"></p>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">Guardar todas</button>
                    </div>
                </div>
            </form>

            {{-- Modelo de una fila: el script lo copia una vez por materia --}}
            <template id="tpl-fila-lote">
                <tr class="border-b border-slate-100 align-top" data-fila-lote>
                    <td class="px-3 py-2"><input type="checkbox" data-campo="incluir" value="1" checked class="h-4 w-4 mt-2.5"></td>
                    <td class="px-3 py-2 pt-3 font-medium text-slate-700" data-nombre-materia></td>
                    <td class="px-3 py-2">
                        <select data-campo="id_anio_lectivo" class="{{ $claseInput }}">
                            @foreach ($aniosLectivos as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}">{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <select data-campo="id_condicion" data-selector-condicion required class="{{ $claseInput }}" style="min-width:165px">
                            @include('admin.alumnos.partials.opciones-condicion', ['conVacio' => true])
                        </select>
                    </td>
                    <td class="px-3 py-2"><input type="date" data-campo="fecha_ultima_modificacion" required class="{{ $claseInput }}"></td>
                    <td class="px-3 py-2" data-campo-nota><input type="number" step="1" min="1" max="10" data-campo="nota_cursada" data-input-nota class="{{ $claseInput }}"></td>
                    <td class="px-3 py-2" data-campo-veces><input type="number" step="1" min="1" max="10" data-campo="veces_rendida" data-input-veces class="{{ $claseInput }}"></td>
                    <td class="px-3 py-2" data-campo-equivalencia><input type="text" maxlength="255" data-campo="institucion_equivalencia" data-input-equivalencia class="{{ $claseInput }}"></td>
                    <td class="px-3 py-2" data-campo-equivalencia><input type="text" maxlength="255" data-campo="resolucion_equivalencia" data-input-equivalencia class="{{ $claseInput }}"></td>
                </tr>
            </template>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
            <h2 class="font-bold text-slate-700 mb-4">3. Cargar una materia suelta</h2>

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
                                <option value="{{ $carrera->id_carrera }}" @selected($carreraAlumnoId && (int) $carrera->id_carrera === (int) $carreraAlumnoId)>{{ $carrera->nombre_carrera }}</option>
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

                {{-- Datos siempre visibles, en posición fija --}}
                <div class="grid sm:grid-cols-3 gap-4 mb-4">
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
                            @include('admin.alumnos.partials.opciones-condicion', ['conVacio' => true])
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Fecha:</label>
                        <input type="date" name="fecha_ultima_modificacion" value="{{ now()->format('Y-m-d') }}" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>

                {{-- Datos que dependen de la condición elegida: siempre en la misma
                     posición (se ocultan sin visibilidad, no sin espacio) para que
                     no "salten" de lugar al cambiar de condición. --}}
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div data-campo-nota>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Nota (entre 1 y 10, sin decimales):</label>
                        <input type="number" step="1" min="1" max="10" name="nota_cursada" data-input-nota
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-veces>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Veces rendida (opcional, entre 1 y 10):</label>
                        <input type="number" step="1" min="1" max="10" name="veces_rendida" data-input-veces
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-equivalencia>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Institución de origen (equivalencia):</label>
                        <input type="text" name="institucion_equivalencia" data-input-equivalencia maxlength="255"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                    <div data-campo-equivalencia>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">N° de resolución (equivalencia):</label>
                        <input type="text" name="resolucion_equivalencia" data-input-equivalencia maxlength="255"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]">
                    </div>
                </div>

                <p class="text-xs text-slate-400 mb-4">
                    Notas: <strong>Final Aprobado</strong> y <strong>Equivalencia</strong> de 4 a 10, <strong>Promocionada</strong> de 7 a 10, <strong>Final Desaprobado</strong> de 1 a 3 (obligatoria). "Cursando" y "Regular" no llevan nota ni veces rendida; "Libre" admite una nota opcional menor a 4. Una "Equivalencia" además pide institución de origen y número de resolución. Si las veces rendida son más de 3, la materia se marca como "Libre" automáticamente.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-[#1E4D8C] font-bold text-sm px-6 py-2.5">Guardar Materia</button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <h2 class="font-bold text-slate-700 px-6 pt-5 pb-3">Historial actual del alumno</h2>
            <p class="text-xs text-slate-400 px-6 pb-3 -mt-2">
                @if ($esDireccion)
                    Una materia ya cargada queda bloqueada: para rectificarla, cambiá lo que haga falta, indicá el motivo y tocá "Rectificar".
                @else
                    🔒 Una materia ya cargada queda bloqueada para corrección: solo Dirección puede rectificarla (con motivo).
                @endif
            </p>
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
                        @if ($esDireccion)
                            <th class="px-6 py-3">Motivo de rectificación</th>
                        @endif
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
                                <select form="{{ $formId }}" name="id_anio_lectivo" @disabled(! $esDireccion) class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                                    @foreach ($aniosLectivos as $anio)
                                        <option value="{{ $anio->id_anio_lectivo }}" @selected($item->id_anio_lectivo == $anio->id_anio_lectivo)>{{ $anio->anio }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-3">
                                <select form="{{ $formId }}" name="id_condicion" data-selector-condicion @disabled(! $esDireccion) class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400" style="min-width:165px">
                                    @include('admin.alumnos.partials.opciones-condicion', ['seleccionada' => $item->id_condicion])
                                </select>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-nota>
                                    <input form="{{ $formId }}" type="number" step="1" min="1" max="10" name="nota_cursada" value="{{ $item->nota_cursada !== null ? (int) $item->nota_cursada : '' }}" data-input-nota @disabled(! $esDireccion)
                                           class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-veces>
                                    <input form="{{ $formId }}" type="number" step="1" min="1" max="10" name="veces_rendida" value="{{ $item->veces_rendida }}" data-input-veces @disabled(! $esDireccion)
                                           class="w-16 rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-equivalencia>
                                    <input form="{{ $formId }}" type="text" name="institucion_equivalencia" value="{{ $item->institucion_equivalencia }}" data-input-equivalencia maxlength="255" @disabled(! $esDireccion)
                                           class="w-32 rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <div data-campo-equivalencia>
                                    <input form="{{ $formId }}" type="text" name="resolucion_equivalencia" value="{{ $item->resolucion_equivalencia }}" data-input-equivalencia maxlength="255" @disabled(! $esDireccion)
                                           class="w-28 rounded-lg border border-slate-300 px-2 py-1.5 text-sm disabled:bg-slate-50 disabled:text-slate-400">
                                </div>
                            </td>
                            @if ($esDireccion)
                                <td class="px-6 py-3">
                                    <input form="{{ $formId }}" type="text" name="motivo" required minlength="5" maxlength="500" placeholder="Ej: error de carga, Libro X Folio Y"
                                           class="w-48 rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </td>
                            @endif
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                @if ($esDireccion)
                                    <button form="{{ $formId }}" type="submit" class="text-[#1E4D8C] hover:underline font-semibold text-xs">Rectificar</button>
                                    <button type="button" data-eliminar-historial="{{ $item->id_historial }}" class="text-red-500 hover:underline font-semibold text-xs ml-2">Eliminar</button>
                                @else
                                    <span class="text-slate-400 text-xs">Bloqueado</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $esDireccion ? 9 : 8 }}" class="px-6 py-10 text-center text-slate-400">Este alumno todavía no tiene historial cargado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($esDireccion)
            @foreach ($historial as $item)
                <form id="form-historial-{{ $item->id_historial }}" method="POST" action="{{ route('admin.alumnos.historial-previo.update', $item) }}" class="hidden">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="busqueda_actual" value="{{ $busqueda }}">
                    <input type="hidden" name="fecha_ultima_modificacion" value="{{ $item->fecha_ultima_modificacion->format('Y-m-d') }}">
                </form>
                <form id="form-eliminar-{{ $item->id_historial }}" method="POST" action="{{ route('admin.alumnos.historial-previo.destroy', $item) }}?busqueda={{ urlencode($busqueda) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="motivo" data-campo-motivo-eliminar>
                </form>
            @endforeach
        @endif
    @endif

    <script>
        const materiasPorCarrera = @json($materiasPorCarrera);
        const materiasYaCargadas = new Set(@json($materiasYaCargadas));
        const ORDEN_ANIO = { 'Primer Año': 1, 'Segundo Año': 2, 'Tercer Año': 3 };
        const selectCarrera = document.getElementById('select-carrera');
        const selectMateria = document.getElementById('select-materia');

        selectCarrera?.addEventListener('change', () => {
            const materias = materiasPorCarrera[selectCarrera.value] || [];

            // Un año está "completo" cuando todas sus materias ya tienen
            // historial cargado — recién ahí se habilita el año siguiente.
            const materiasPorAnio = {};
            materias.forEach((materia) => {
                const orden = ORDEN_ANIO[materia.anio];
                if (orden == null) return;
                (materiasPorAnio[orden] ??= []).push(materia.id);
            });
            const anioCompleto = {};
            Object.entries(materiasPorAnio).forEach(([orden, ids]) => {
                anioCompleto[orden] = ids.every((id) => materiasYaCargadas.has(id));
            });

            selectMateria.innerHTML = '<option value="">Seleccioná materia...</option>';
            materias.forEach((materia) => {
                const opcion = document.createElement('option');
                opcion.value = materia.id;

                let etiqueta = `${materia.nombre} (${materia.anio ?? '—'})`;
                const orden = ORDEN_ANIO[materia.anio];

                if (materiasYaCargadas.has(materia.id)) {
                    etiqueta += ' — ya cargada';
                    opcion.disabled = true;
                } else if (orden > 1 && !anioCompleto[orden - 1]) {
                    etiqueta += ' — falta completar el año anterior';
                    opcion.disabled = true;
                }

                opcion.textContent = etiqueta;
                selectMateria.appendChild(opcion);
            });
        });

        // Si la carrera del alumno ya viene seleccionada, cargar sus materias.
        if (selectCarrera?.value) {
            selectCarrera.dispatchEvent(new Event('change'));
        }

        // Nota y veces rendida: solo enteros, dentro del rango permitido.
        // Se recorta en vivo para que no dependa de que el navegador muestre
        // el error de validación recién al tocar "Guardar".
        function limitarEntero(input, min, max) {
            input.addEventListener('input', () => {
                let valor = input.value.replace(/[^0-9]/g, '');
                if (valor !== '') {
                    valor = String(Math.min(max, Math.max(min, parseInt(valor, 10))));
                }
                input.value = valor;
            });
        }

        document.querySelectorAll('[data-input-nota]').forEach((input) => limitarEntero(input, 1, 10));
        document.querySelectorAll('[data-input-veces]').forEach((input) => limitarEntero(input, 1, 10));

        // Eliminar una materia ya cargada también es una rectificación: pide
        // el motivo antes de mandar el formulario (ver HistorialPrevioController::destroy).
        document.querySelectorAll('[data-eliminar-historial]').forEach((boton) => {
            boton.addEventListener('click', () => {
                const motivo = prompt('¿Por qué se elimina esta materia del historial? (obligatorio)');
                if (motivo === null) return;
                if (motivo.trim().length < 5) {
                    alert('Contá brevemente el motivo (al menos 5 caracteres) para poder eliminarla.');
                    return;
                }
                if (! confirm('¿Eliminar esta materia del historial?')) return;

                const form = document.getElementById('form-eliminar-' + boton.dataset.eliminarHistorial);
                form.querySelector('[data-campo-motivo-eliminar]').value = motivo.trim();
                form.submit();
            });
        });

        // Según la condición elegida: la nota se oculta en curso, se pide
        // obligatoria en una aprobación definitiva y queda opcional en Libre.
        // Una Equivalencia no lleva veces rendida, pero sí institución y resolución.
        const SIN_NOTA = ['Cursando', 'Regular'];
        // Rango de nota por condición (igual que en el controlador): Promocionada 7 a 10,
        // Final Aprobado y Equivalencia 4 a 10, Final Desaprobado y Libre 1 a 3.
        const RANGO_NOTA = { 'Promocionada': [7, 10], 'Examen Final': [4, 10], 'Equivalencia': [4, 10], 'Final Desaprobado': [1, 3], 'Libre': [1, 3] };

        function actualizarCamposPorCondicion(selectCondicion) {
            const contenedor = selectCondicion.closest('tr') || selectCondicion.closest('form');
            if (!contenedor) return;

            const nombreCondicion = selectCondicion.selectedOptions[0]?.dataset.nombre;
            const esEquivalencia = nombreCondicion === 'Equivalencia';
            const sinNota = !nombreCondicion || SIN_NOTA.includes(nombreCondicion);

            // visibility (no display:none) a propósito: así el campo deja de
            // verse pero sigue ocupando su lugar en la grilla, y el resto de
            // los campos no cambia de posición cada vez que cambia la condición.
            // Se pisa el estilo directo (no una clase) para no depender de que
            // el CSS de Tailwind ya esté recompilado con esa clase.
            const campoNota = contenedor.querySelector('[data-campo-nota]');
            const inputNota = contenedor.querySelector('[data-input-nota]');
            if (campoNota && inputNota) {
                if (sinNota) {
                    campoNota.style.visibility = 'hidden';
                    inputNota.value = '';
                    inputNota.required = false;
                    inputNota.disabled = true;
                } else {
                    campoNota.style.visibility = '';
                    inputNota.disabled = false;
                    inputNota.required = nombreCondicion !== 'Libre';
                    const [minima, maxima] = RANGO_NOTA[nombreCondicion] ?? [1, 10];
                    inputNota.min = minima;
                    inputNota.max = maxima;
                    inputNota.placeholder = `${minima} a ${maxima}`;
                }
            }

            const campoVeces = contenedor.querySelector('[data-campo-veces]');
            const inputVeces = contenedor.querySelector('[data-input-veces]');
            if (campoVeces && inputVeces) {
                if (esEquivalencia || sinNota) {
                    campoVeces.style.visibility = 'hidden';
                    inputVeces.value = '';
                    inputVeces.disabled = true;
                } else {
                    campoVeces.style.visibility = '';
                    inputVeces.disabled = false;
                }
            }

            contenedor.querySelectorAll('[data-campo-equivalencia]').forEach((campo) => {
                const inputEquivalencia = campo.querySelector('[data-input-equivalencia]');
                if (esEquivalencia) {
                    campo.style.visibility = '';
                    if (inputEquivalencia) {
                        inputEquivalencia.disabled = false;
                        inputEquivalencia.required = true;
                    }
                } else {
                    campo.style.visibility = 'hidden';
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

        // ---------- Carga de un año completo ----------
        (function () {
            const loteCarrera = document.getElementById('lote-carrera');
            if (!loteCarrera) return;

            const loteAnio = document.getElementById('lote-anio');
            const contenido = document.getElementById('lote-contenido');
            const tbody = document.getElementById('lote-filas');
            const plantilla = document.getElementById('tpl-fila-lote');
            const aviso = document.getElementById('lote-aviso');
            const tildarTodas = document.getElementById('lote-tildar-todas');
            const todasAnio = document.getElementById('lote-todas-anio');
            const todasFecha = document.getElementById('lote-todas-fecha');

            // Si al guardar hubo un error, se vuelve a armar la tabla con lo que se había cargado.
            const carreraAlumno = @json($carreraAlumnoId);
            const anioLectivoActual = @json($anioLectivoActual);
            const nombreAnioActual = @json($nombreAnioActual);
            const idAnioPorNumero = @json($idAnioPorNumero);
            const filasPrevias = @json(old('filas', []));
            const anioPrevio = @json(old('anio_lote'));
            const hayPrevias = Object.keys(filasPrevias).length > 0;

            // Activa o desactiva una fila: las destildadas no se envían ni se validan.
            function aplicarInclusion(fila) {
                const incluida = fila.querySelector('[data-campo="incluir"]').checked;
                fila.style.opacity = incluida ? '' : '0.4';
                fila.querySelectorAll('select, input:not([data-campo="incluir"])').forEach((el) => { el.disabled = !incluida; });
                if (incluida) actualizarCamposPorCondicion(fila.querySelector('[data-selector-condicion]'));
            }

            function cargarAnios() {
                const materias = materiasPorCarrera[loteCarrera.value] || [];
                const anios = [...new Set(materias.map((m) => m.anio).filter(Boolean))]
                    .sort((a, b) => (ORDEN_ANIO[a] ?? 99) - (ORDEN_ANIO[b] ?? 99));

                loteAnio.innerHTML = '<option value="">Seleccioná el año...</option>';
                anios.forEach((anio) => {
                    const opcion = document.createElement('option');
                    opcion.value = anio;
                    opcion.textContent = anio;
                    loteAnio.appendChild(opcion);
                });
                armarTabla();
            }

            function armarTabla() {
                tbody.innerHTML = '';
                const anio = loteAnio.value;
                contenido.classList.toggle('hidden', !anio);
                if (!anio) return;

                const materias = (materiasPorCarrera[loteCarrera.value] || []).filter((m) => m.anio === anio);
                const orden = ORDEN_ANIO[anio];

                // Año lectivo propuesto para este año de la carrera: si cursa Segundo
                // Año en 2026, Primer Año fue 2025 (se puede cambiar).
                const ordenActual = ORDEN_ANIO[nombreAnioActual];
                if (anioLectivoActual && ordenActual && orden) {
                    const idPropuesto = idAnioPorNumero[anioLectivoActual - (ordenActual - orden)];
                    if (idPropuesto) todasAnio.value = String(idPropuesto);
                }

                // Misma regla que la carga suelta: no se puede cargar un año si el anterior no está completo.
                const anterior = Object.keys(ORDEN_ANIO).find((nombre) => ORDEN_ANIO[nombre] === orden - 1);
                const materiasAnterior = (materiasPorCarrera[loteCarrera.value] || []).filter((m) => m.anio === anterior);
                const anteriorIncompleto = orden > 1 && materiasAnterior.some((m) => !materiasYaCargadas.has(m.id));

                let yaCargadas = 0;
                materias.forEach((materia) => {
                    const fila = plantilla.content.firstElementChild.cloneNode(true);
                    fila.querySelector('[data-nombre-materia]').textContent = materia.nombre;

                    fila.querySelectorAll('[data-campo]').forEach((el) => {
                        el.name = `filas[${materia.id}][${el.dataset.campo}]`;
                    });

                    const previa = filasPrevias[materia.id];
                    const set = (campo, valor) => {
                        const el = fila.querySelector(`[data-campo="${campo}"]`);
                        if (el && valor !== undefined && valor !== null) el.value = valor;
                    };
                    set('id_anio_lectivo', previa?.id_anio_lectivo ?? todasAnio.value);
                    set('id_condicion', previa?.id_condicion ?? '');
                    set('fecha_ultima_modificacion', previa?.fecha_ultima_modificacion ?? todasFecha.value);
                    set('nota_cursada', previa?.nota_cursada);
                    set('veces_rendida', previa?.veces_rendida);
                    set('institucion_equivalencia', previa?.institucion_equivalencia);
                    set('resolucion_equivalencia', previa?.resolucion_equivalencia);

                    const check = fila.querySelector('[data-campo="incluir"]');
                    if (materiasYaCargadas.has(materia.id)) {
                        check.checked = false;
                        check.disabled = true;
                        fila.querySelector('[data-nombre-materia]').textContent += ' — ya cargada';
                        yaCargadas++;
                    } else if (anteriorIncompleto) {
                        check.checked = false;
                        check.disabled = true;
                    } else if (hayPrevias) {
                        check.checked = previa !== undefined;
                    }

                    tbody.appendChild(fila);

                    fila.querySelector('[data-selector-condicion]').addEventListener('change', (e) => actualizarCamposPorCondicion(e.target));
                    check.addEventListener('change', () => aplicarInclusion(fila));
                    fila.querySelectorAll('[data-input-nota], [data-input-veces]').forEach((input) => limitarEntero(input, 1, 10));
                    aplicarInclusion(fila);
                });

                aviso.textContent = anteriorIncompleto
                    ? `Primero hay que completar todas las materias de ${anterior}.`
                    : (yaCargadas ? `${yaCargadas} materia(s) ya estaban cargadas y no se tocan.` : '');
                tildarTodas.checked = true;
            }

            loteCarrera.addEventListener('change', cargarAnios);
            loteAnio.addEventListener('change', armarTabla);

            document.getElementById('lote-aplicar').addEventListener('click', () => {
                tbody.querySelectorAll('[data-fila-lote]').forEach((fila) => {
                    if (!fila.querySelector('[data-campo="incluir"]').checked) return;
                    fila.querySelector('[data-campo="id_anio_lectivo"]').value = todasAnio.value;
                    fila.querySelector('[data-campo="fecha_ultima_modificacion"]').value = todasFecha.value;
                });
            });

            tildarTodas.addEventListener('change', () => {
                tbody.querySelectorAll('[data-fila-lote]').forEach((fila) => {
                    const check = fila.querySelector('[data-campo="incluir"]');
                    if (check.disabled) return;
                    check.checked = tildarTodas.checked;
                    aplicarInclusion(fila);
                });
            });

            // Al abrir la página: la carrera del alumno ya elegida y, como año,
            // el primero que todavía tiene materias sin cargar (el que sigue).
            if (carreraAlumno && !loteCarrera.value) {
                loteCarrera.value = String(carreraAlumno);
            }

            if (loteCarrera.value) {
                cargarAnios();

                const materias = materiasPorCarrera[loteCarrera.value] || [];
                const anioSiguiente = [...loteAnio.options].map((o) => o.value).filter(Boolean)
                    .find((anio) => materias.some((m) => m.anio === anio && !materiasYaCargadas.has(m.id)));
                const anioInicial = anioPrevio || anioSiguiente;

                if (anioInicial) {
                    loteAnio.value = anioInicial;
                    armarTabla();
                }
            }
        })();
    </script>
@endsection
