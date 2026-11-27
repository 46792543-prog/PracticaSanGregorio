@extends('layouts.admin')

@section('titulo', 'Materias del plan')

@section('contenido')
    <x-sg.encabezado titulo="Materias del plan" :subtitulo="$carrera->nombre_carrera" eyebrow="Carreras y planes" icono="book">
        <a href="{{ route('admin.carreras.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
        <a href="{{ route('admin.carreras.correlativas', $carrera) }}" class="sg-btn sg-btn--primario">Configurar correlativas <x-sg.icono nombre="arrow-right" class="sg-flecha" /></a>
    </x-sg.encabezado>

    @include('admin.carreras.partials.plan-selector', ['editable' => true])


    <div class="sg-card sg-revelar mb-6 overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="list" /></span>
                <h3>Materias cargadas</h3>
            </div>
            <span class="sg-badge sg-badge--azul">{{ $materias->count() }} materias</span>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Nombre</th>
                        <th>Año</th>
                        <th>Período</th>
                        <th>Régimen</th>
                        <th>Hs. cátedra</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($materias as $materia)
                        @php
                            $edicionMateriaPayload = [
                                'id_materia' => $materia->id_materia,
                                'numero_orden' => $materia->numero_orden,
                                'nombre' => $materia->nombre,
                                'id_anio_cursada' => $materia->id_anio_cursada,
                                'id_periodo' => $materia->id_periodo,
                                'id_regimen' => $materia->id_regimen,
                                'horas_catedra' => $materia->horas_catedra,
                                'cupof' => $materia->cupof,
                                'es_taller' => $materia->es_taller,
                            ];
                        @endphp
                        <tr class="{{ $materia->activa ? '' : 'opacity-50' }}">
                            <td><span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-600">{{ $materia->numero_orden }}</span></td>
                            <td class="font-semibold text-slate-700">{{ $materia->nombre }}</td>
                            <td class="text-slate-500">{{ $materia->anioCursada->nombre_anio }}</td>
                            <td class="text-slate-500">{{ $materia->periodo->nombre_periodo }}</td>
                            <td class="text-slate-500">{{ $materia->regimen->nombre_regimen }}</td>
                            <td class="whitespace-nowrap">
                                @if ($materia->horas_catedra)
                                    <span class="font-semibold text-[#0B3D91]">{{ $materia->horas_catedra }} h</span>
                                    <span class="block text-xs text-slate-400">{{ $materia->horas_reloj }} reloj</span>
                                @else
                                    <span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">Sin cargar</span>
                                @endif
                                @if ($materia->es_taller)<span class="sg-badge sg-badge--azul sg-badge--sin-punto mt-1">Práctica</span>@endif
                                @if ($materia->cupof)<span class="block text-xs text-slate-400">CUPOF {{ $materia->cupof }}</span>@endif
                            </td>
                            <td>
                                <span class="sg-badge {{ $materia->activa ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                                    {{ $materia->activa ? 'Activa' : 'Dada de baja' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap">
                                    <button type="button" class="sg-btn sg-btn--suave sg-btn--chico" onclick='editarMateria(@json($edicionMateriaPayload))'>Editar</button>
                                    @if ($materia->activa)
                                        <form method="POST" action="{{ route('admin.carreras.materias.baja', [$carrera, $materia]) }}" data-confirm="¿Dar de baja esta materia?">
                                            @csrf @method('PUT')
                                            <button class="sg-btn sg-btn--peligro sg-btn--chico">Dar de baja</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.carreras.materias.reactivar', [$carrera, $materia]) }}">
                                            @csrf @method('PUT')
                                            <button class="sg-btn sg-btn--verde sg-btn--chico">Reactivar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-sg.vacio icono="book">Todavía no se cargaron materias.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="sg-card sg-revelar p-6 border-t-4 border-t-[#FFC107]">
        <div class="flex items-center gap-3 mb-5">
            <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="plus" /></span>
            <h2 id="titulo-form-materia" class="font-display font-bold text-slate-800">Agregar materia</h2>
        </div>
        <form id="form-materia" method="POST" action="{{ route('admin.carreras.materias.store', $carrera) }}" class="grid sm:grid-cols-6 gap-4 items-end">
            @csrf
            <input type="hidden" name="_method" id="input-materia-method" value="">
            <div>
                <label class="sg-label">Nº ORDEN</label>
                <input type="number" name="numero_orden" id="input-materia-orden" min="1" step="1" required value="{{ $materias->max('numero_orden') + 1 }}"
                       onkeydown="if (!['ArrowUp', 'ArrowDown', 'Tab'].includes(event.key)) event.preventDefault();"
                       onpaste="event.preventDefault();"
                       class="sg-input w-full">
            </div>
            <div class="sm:col-span-2">
                <label class="sg-label">NOMBRE</label>
                <input type="text" maxlength="150" name="nombre" id="input-materia-nombre" required
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                       class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">AÑO</label>
                <select name="id_anio_cursada" id="input-materia-anio" class="sg-input w-full">
                    @foreach ($aniosCursada as $anio)
                        <option value="{{ $anio->id_anio_cursada }}">{{ $anio->nombre_anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">PERÍODO</label>
                <select name="id_periodo" id="input-materia-periodo" class="sg-input w-full">
                    @foreach ($periodos as $periodo)
                        <option value="{{ $periodo->id_periodo }}">{{ $periodo->nombre_periodo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">RÉGIMEN</label>
                <select name="id_regimen" id="input-materia-regimen" class="sg-input w-full">
                    @foreach ($regimenes as $regimen)
                        <option value="{{ $regimen->id_regimen }}">{{ $regimen->nombre_regimen }}</option>
                    @endforeach
                </select>
            </div>
            {{-- Horas cátedra (1 h cátedra = 40 min), CUPOF y práctica --}}
            <div>
                <label class="sg-label">HS. CÁTEDRA *</label>
                <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" data-max-valor="30" name="horas_catedra" id="input-materia-horas" required placeholder="Ej: 4"
                       oninput="mostrarHorasReloj()" class="sg-input w-full">
                <p id="texto-horas-reloj" class="text-xs text-slate-400 mt-1">Semanales · 1 h cátedra = 40 min</p>
            </div>
            <div>
                <label class="sg-label">CUPOF</label>
                <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="8" maxlength="8" name="cupof" id="input-materia-cupof" placeholder="Opcional" class="sg-input w-full">
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm cursor-pointer has-[:checked]:border-[#0B3D91] has-[:checked]:bg-blue-50">
                    <input type="checkbox" name="es_taller" value="1" id="input-materia-taller" class="h-4 w-4 rounded">
                    <span><strong>Es práctica</strong> <span class="text-slate-400">— las horas se pueden repartir entre varios docentes</span></span>
                </label>
            </div>
            <div class="sm:col-span-6 flex items-center gap-3">
                <span id="editando-materia-aviso" class="hidden sg-badge sg-badge--amarillo mr-auto">Editando una materia existente — al guardar se actualizará.</span>
                <button type="button" onclick="cancelarEdicionMateria()" class="sg-btn sg-btn--borde">Limpiar</button>
                <button type="submit" id="input-materia-submit" class="sg-btn sg-btn--azul flex-1">+ Agregar</button>
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
            document.getElementById('input-materia-horas').value = materia.horas_catedra ?? '';
            document.getElementById('input-materia-cupof').value = materia.cupof ?? '';
            document.getElementById('input-materia-taller').checked = !!materia.es_taller;
            mostrarHorasReloj();
            document.getElementById('input-materia-submit').textContent = 'Guardar cambios';
            document.getElementById('titulo-form-materia').textContent = 'Editar materia';
            document.getElementById('editando-materia-aviso').classList.remove('hidden');
            document.getElementById('form-materia').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // "4 h cátedra = 2 h 40 min reloj" mientras se escribe.
        function mostrarHorasReloj() {
            const horas = parseInt(document.getElementById('input-materia-horas').value, 10);
            const texto = document.getElementById('texto-horas-reloj');
            if (!horas) { texto.textContent = 'Semanales · 1 h cátedra = 40 min'; return; }
            const min = horas * 40;
            const h = Math.floor(min / 60), r = min % 60;
            texto.textContent = '= ' + [h ? h + ' h' : '', r ? r + ' min' : ''].filter(Boolean).join(' ') + ' reloj por semana';
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
@endsection
