@extends('layouts.admin')

@section('titulo', 'Materias del plan')

@section('contenido')
    <p class="text-sm text-slate-400 mb-4">
        <a href="{{ route('admin.carreras.index') }}" class="hover:underline">Carreras y planes</a> /
        <span class="text-blue-600 font-semibold">{{ $carrera->nombre_carrera }}</span> / Materias
    </p>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
        <h2 class="font-bold text-slate-800 mb-4">Materias cargadas ({{ $materias->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-400 uppercase border-b border-slate-100">
                        <th class="py-2 pr-4">Orden</th>
                        <th class="py-2 pr-4">Nombre</th>
                        <th class="py-2 pr-4">Año</th>
                        <th class="py-2 pr-4">Período</th>
                        <th class="py-2 pr-4">Régimen</th>
                        <th class="py-2 pr-4">Estado</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($materias as $materia)
                        @php
                            $edicionMateriaPayload = [
                                'id_materia' => $materia->id_materia,
                                'numero_orden' => $materia->numero_orden,
                                'nombre' => $materia->nombre,
                                'id_anio_cursada' => $materia->id_anio_cursada,
                                'id_periodo' => $materia->id_periodo,
                                'id_regimen' => $materia->id_regimen,
                            ];
                        @endphp
                        <tr class="{{ $materia->activa ? '' : 'opacity-50' }}">
                            <td class="py-2 pr-4 text-slate-400">{{ $materia->numero_orden }}</td>
                            <td class="py-2 pr-4 font-semibold text-slate-700">{{ $materia->nombre }}</td>
                            <td class="py-2 pr-4 text-slate-500">{{ $materia->anioCursada->nombre_anio }}</td>
                            <td class="py-2 pr-4 text-slate-500">{{ $materia->periodo->nombre_periodo }}</td>
                            <td class="py-2 pr-4 text-slate-500">{{ $materia->regimen->nombre_regimen }}</td>
                            <td class="py-2 pr-4">
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $materia->activa ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ $materia->activa ? 'Activa' : 'Dada de baja' }}
                                </span>
                            </td>
                            <td class="py-2 pr-4">
                                <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap">
                                    <button type="button" class="text-[#1E4D8C] hover:underline" onclick='editarMateria(@json($edicionMateriaPayload))'>Editar</button>
                                    @if ($materia->activa)
                                        <form method="POST" action="{{ route('admin.carreras.materias.baja', [$carrera, $materia]) }}" onsubmit="return confirm('¿Dar de baja esta materia?');">
                                            @csrf @method('PUT')
                                            <button class="text-red-500 hover:underline">Dar de baja</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.carreras.materias.reactivar', [$carrera, $materia]) }}">
                                            @csrf @method('PUT')
                                            <button class="text-green-600 hover:underline">Reactivar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-slate-400">Todavía no se cargaron materias.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h2 id="titulo-form-materia" class="font-bold text-slate-800 mb-4">Agregar materia</h2>
        <form id="form-materia" method="POST" action="{{ route('admin.carreras.materias.store', $carrera) }}" class="grid sm:grid-cols-6 gap-4 items-end">
            @csrf
            <input type="hidden" name="_method" id="input-materia-method" value="">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nº ORDEN</label>
                <input type="number" name="numero_orden" id="input-materia-orden" min="1" step="1" required value="{{ $materias->max('numero_orden') + 1 }}"
                       onkeydown="if (!['ArrowUp', 'ArrowDown', 'Tab'].includes(event.key)) event.preventDefault();"
                       onpaste="event.preventDefault();"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE</label>
                <input type="text" maxlength="40" name="nombre" id="input-materia-nombre" required
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">AÑO</label>
                <select name="id_anio_cursada" id="input-materia-anio" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($aniosCursada as $anio)
                        <option value="{{ $anio->id_anio_cursada }}">{{ $anio->nombre_anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">PERÍODO</label>
                <select name="id_periodo" id="input-materia-periodo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}">{{ $periodo->nombre_periodo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">RÉGIMEN</label>
                <select name="id_regimen" id="input-materia-regimen" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach ($regimenes as $regimen)
                        <option value="{{ $regimen->id_regimen }}">{{ $regimen->nombre_regimen }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-6 flex items-center gap-3">
                <span id="editando-materia-aviso" class="hidden text-xs text-amber-600 font-semibold mr-auto">✏️ Editando una materia existente — al guardar se actualizará.</span>
                <button type="button" onclick="cancelarEdicionMateria()" class="rounded-xl border border-slate-300 text-slate-600 font-semibold text-sm px-4 py-2.5">Limpiar</button>
                <button type="submit" id="input-materia-submit" class="flex-1 rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white font-semibold text-sm px-4 py-2.5">+ Agregar</button>
            </div>
        </form>
    </div>

    <script>
        const rutaBaseMaterias = @json(url("/admin/carreras/{$carrera->id_carrera}/materias"));
        const rutaGuardarMateria = @json(route('admin.carreras.materias.store', $carrera));
        const ordenSiguienteMateria = {{ $materias->max('numero_orden') + 1 }};

        function editarMateria(materia) {
            document.getElementById('form-materia').action = rutaBaseMaterias + '/' + materia.id_materia;
            document.getElementById('input-materia-method').value = 'PUT';
            document.getElementById('input-materia-orden').value = materia.numero_orden;
            document.getElementById('input-materia-nombre').value = materia.nombre;
            document.getElementById('input-materia-anio').value = materia.id_anio_cursada;
            document.getElementById('input-materia-periodo').value = materia.id_periodo;
            document.getElementById('input-materia-regimen').value = materia.id_regimen;
            document.getElementById('input-materia-submit').textContent = 'Guardar cambios';
            document.getElementById('titulo-form-materia').textContent = 'Editar materia';
            document.getElementById('editando-materia-aviso').classList.remove('hidden');
            document.getElementById('form-materia').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function cancelarEdicionMateria() {
            const form = document.getElementById('form-materia');
            form.reset();
            form.action = rutaGuardarMateria;
            document.getElementById('input-materia-method').value = '';
            document.getElementById('input-materia-orden').value = ordenSiguienteMateria;
            document.getElementById('input-materia-submit').textContent = '+ Agregar';
            document.getElementById('titulo-form-materia').textContent = 'Agregar materia';
            document.getElementById('editando-materia-aviso').classList.add('hidden');
        }
    </script>

    <div class="flex justify-end gap-3 mt-6">
        <a href="{{ route('admin.carreras.index') }}" class="rounded-lg border border-[#1E4D8C] text-[#1E4D8C] font-semibold text-sm px-6 py-2.5">Volver</a>
        <a href="{{ route('admin.carreras.correlativas', $carrera) }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow transition text-white font-semibold text-sm px-6 py-2.5">Configurar correlativas →</a>
    </div>
@endsection
