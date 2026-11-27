@extends('layouts.admin')

@section('titulo', 'Designaciones')

@section('contenido')
    <x-sg.encabezado titulo="Designaciones" subtitulo="Qué profesor dicta cada materia, con cuántas horas cátedra y desde cuándo — Año lectivo {{ $anio?->anio }}" eyebrow="Plantel docente" icono="briefcase" />

    @php
        $colorEstado = ['Activa' => 'sg-badge--verde', 'Por vencer' => 'sg-badge--amarillo', 'Vencida' => 'sg-badge--rojo', 'Finalizada' => 'sg-badge--gris'];
        $colorTipo = ['Titular' => 'sg-badge--amarillo', 'Interino' => 'sg-badge--azul', 'Suplente' => 'sg-badge--rojo', 'Provisional' => 'sg-badge--gris'];
    @endphp

    {{-- Formulario: nueva designación / editar (como SIGEDOC) --}}
    @php
        $tarjetasTipo = ['Titular' => ['T', 'from-[#FFC107] to-[#FFD54F] text-[#7A5400]'], 'Interino' => ['I', 'from-[#2563EB] to-[#4FC3F7] text-white'],
            'Suplente' => ['S', 'from-[#F87171] to-[#EC4899] text-white'], 'Provisional' => ['P', 'from-[#78716C] to-[#A8A29E] text-white']];
    @endphp
    <div id="tarjeta-designacion" class="sg-card sg-revelar mb-8 border-l-4 border-l-[#FFC107] p-6">
        <p class="text-[11px] font-bold uppercase tracking-[.14em] text-amber-500">Acto administrativo · Designación docente</p>
        <h2 id="titulo-form-designacion" class="font-display font-extrabold text-2xl text-[#0B3D91] mb-5">Nueva designación</h2>

        <form id="form-designacion" method="POST" action="{{ route('admin.designaciones.store') }}">
            @csrf
            <input type="hidden" name="_method" id="input-designacion-method" value="">

            {{-- Carrera · Turno · Materia --}}
            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="sg-label">Carrera <span class="text-red-500">*</span></label>
                    <select id="input-designacion-carrera" onchange="filtrarMaterias()" class="sg-input w-full">
                        <option value="">Elegir carrera...</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">Turno <span class="text-red-500">*</span></label>
                    <select name="id_turno_cursada" id="input-designacion-turno" required onchange="actualizarMateria()" class="sg-input w-full">
                        <option value="">Elegir turno...</option>
                        @foreach ($turnos as $turno)
                            <option value="{{ $turno->id_turno_cursada }}" @selected(old('id_turno_cursada') == $turno->id_turno_cursada)>{{ $turno->nombre_turno }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">Materia <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select name="id_materia" id="input-designacion-materia" required disabled onchange="actualizarMateria()" class="sg-input min-w-0 flex-1">
                            <option value="">Primero elegí la carrera</option>
                            @foreach ($materiasPorCarrera->flatten() as $materia)
                                <option value="{{ $materia->id_materia }}" data-carrera="{{ $materia->id_carrera }}" hidden>
                                    {{ $materia->nombre }} · {{ $materia->anioCursada->nombre_anio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Recuadro de la materia elegida --}}
            <div id="info-materia" class="hidden mt-4 rounded-2xl border border-blue-200 bg-blue-50/70 px-4 py-3">
                <p class="flex items-center gap-2 font-semibold text-[#0B3D91]"><x-sg.icono nombre="book" class="h-4 w-4" /> <span id="info-materia-nombre"></span></p>
                <p id="info-materia-detalle" class="text-xs text-slate-600 mt-1"></p>
                <div id="info-materia-docentes" class="text-xs text-slate-600 mt-1"></div>
            </div>

            {{-- Docente (buscador) --}}
            <div class="mt-4">
                <label class="sg-label">Docente <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <div class="relative flex-1 min-w-0">
                        <div class="sg-campo-icono">
                            <x-sg.icono nombre="search" />
                            <input type="text" id="input-designacion-buscar" autocomplete="off" placeholder="Escribí el apellido o el nombre..." oninput="buscarDocente()" onfocus="buscarDocente()" class="sg-input w-full">
                        </div>
                        <input type="hidden" name="id_profesor" id="input-designacion-profesor" value="{{ old('id_profesor') }}">
                        <div id="lista-docentes" class="hidden absolute z-20 mt-1 w-full max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    </div>
                </div>
            </div>

            {{-- Lo de abajo se habilita al elegir el docente --}}
            <fieldset id="campos-designacion" disabled class="mt-5 space-y-5 disabled:opacity-50">
                <div>
                    <label class="sg-label">Tipo de designación <span class="text-red-500">*</span></label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach ($tarjetasTipo as $tipo => [$letra, $color])
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 cursor-pointer transition hover:border-[#4FC3F7] has-[:checked]:border-[#0B3D91] has-[:checked]:ring-2 has-[:checked]:ring-[#0B3D91]/20">
                                <input type="radio" name="tipo" value="{{ $tipo }}" class="sr-only" @checked(old('tipo', 'Titular') === $tipo)>
                                <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-gradient-to-br {{ $color }} font-display font-bold">{{ $letra }}</span>
                                <span class="font-semibold text-slate-700">{{ $tipo }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid sm:grid-cols-4 gap-4 items-start">
                    <div>
                        <label class="sg-label">Carga horaria <span class="text-red-500">*</span> <span class="font-normal normal-case text-slate-400">— hs cátedra</span></label>
                        {{-- Materia común: bloqueada (son todas las horas de la materia). --}}
                        <div id="carga-bloqueada" class="flex items-center gap-2 rounded-xl bg-blue-50 px-4 py-3 text-[#0B3D91]">
                            <x-sg.icono nombre="lock" class="h-4 w-4 text-slate-500" />
                            <span id="texto-carga" class="font-display font-bold text-lg">—</span>
                            <span id="texto-carga-detalle" class="text-xs text-slate-500"></span>
                        </div>
                        {{-- Práctica: se elige cuántas horas da este docente (de 1 a las que quedan libres). --}}
                        <div id="carga-practica" class="hidden">
                            <select id="select-carga-practica" onchange="porId('input-designacion-horas').value = this.value" class="sg-input w-full font-semibold"></select>
                            <p id="texto-carga-practica" class="text-xs text-slate-500 mt-1"></p>
                        </div>
                        <input type="hidden" name="horas_catedra" id="input-designacion-horas" value="{{ old('horas_catedra') }}">
                    </div>
                    <div>
                        <label class="sg-label">Fecha de alta <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_alta" id="input-designacion-alta" required value="{{ old('fecha_alta', now()->toDateString()) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Fecha de baja <span class="font-normal normal-case text-slate-400">— prevista</span></label>
                        <input type="date" name="fecha_baja" id="input-designacion-baja" value="{{ old('fecha_baja') }}" placeholder="Sin fecha" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Estado</label>
                        <div class="inline-flex rounded-xl bg-slate-100 p-1">
                            <label class="cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 has-[:checked]:bg-emerald-600 has-[:checked]:text-white">
                                <input type="radio" name="activa" value="1" class="sr-only" @checked(old('activa', '1') === '1')> Activa
                            </label>
                            <label class="cursor-pointer rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 has-[:checked]:bg-slate-600 has-[:checked]:text-white">
                                <input type="radio" name="activa" value="0" class="sr-only" @checked(old('activa') === '0')> Inactiva
                            </label>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="sg-label">Año lectivo <span class="text-red-500">*</span></label>
                        <select name="id_anio_lectivo" id="input-designacion-anio" required onchange="actualizarAnioResolucion(); actualizarMateria()" class="sg-input w-full">
                            @foreach ($anios as $a)
                                <option value="{{ $a->id_anio_lectivo }}" data-anio="{{ $a->anio }}" @selected($a->id_anio_lectivo === (int) old('id_anio_lectivo', $anio?->id_anio_lectivo))>{{ $a->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">Resolución</label>
                        {{-- Bloqueada: se completa sola con la resolución del plan vigente de la carrera elegida --}}
                        <div class="flex items-stretch">
                            <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="6" maxlength="6" readonly tabindex="-1" name="resolucion_numero" id="input-designacion-resolucion" value="{{ old('resolucion_numero') }}" placeholder="Sin resolución" class="sg-input min-w-0 flex-1 !rounded-r-none bg-slate-50 text-slate-600 cursor-not-allowed">
                            <span id="sufijo-resolucion" class="grid place-items-center rounded-r-xl border border-l-0 border-slate-200 bg-slate-50 px-3 font-semibold text-slate-500">/{{ $anio?->anio }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Se completa sola con la resolución del plan vigente de la carrera.</p>
                    </div>
                </div>
            </fieldset>

            <span id="editando-designacion-aviso" class="hidden sg-aviso sg-aviso--alerta text-xs mt-4"><span>Editando una designación: al guardar se actualiza.</span></span>
            <div class="flex justify-center gap-3 pt-5 mt-6 border-t border-slate-100">
                <button type="button" onclick="limpiarDesignacion()" class="sg-btn sg-btn--borde min-w-[140px] justify-center">Limpiar</button>
                <button type="submit" id="btn-designacion" class="sg-btn sg-btn--primario min-w-[180px] justify-center"><x-sg.icono nombre="check" /> Guardar designación</button>
            </div>
        </form>
    </div>

    {{-- Filtros y pestañas --}}
    <div class="sg-card sg-revelar overflow-hidden">
        <div class="flex flex-wrap gap-2 p-4 pb-0">
            @foreach (['activas' => 'Activas', 'por_vencer' => 'Por vencer', 'finalizadas' => 'Finalizadas', 'sin_docente' => 'Materias sin docente'] as $clave => $texto)
                <a href="{{ route('admin.designaciones.index', array_filter(['estado' => $clave, 'anio' => $anio?->id_anio_lectivo] + $filtros)) }}"
                   class="sg-btn sg-btn--chico {{ $estado === $clave ? 'sg-btn--azul' : 'sg-btn--borde' }}">{{ $texto }}
                    @if ($clave === 'sin_docente' && $sinDocente->isNotEmpty())<span class="sg-badge sg-badge--rojo sg-badge--sin-punto ml-1">{{ $sinDocente->count() }}</span>@endif
                </a>
            @endforeach
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 bg-slate-50/60">
            <input type="hidden" name="estado" value="{{ $estado }}">
            <div>
                <label class="sg-label">Año lectivo</label>
                <select name="anio" class="sg-input">
                    @foreach ($anios as $a)
                        <option value="{{ $a->id_anio_lectivo }}" @selected($a->id_anio_lectivo === $anio?->id_anio_lectivo)>{{ $a->anio }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Profesor</label>
                <select name="profesor" class="sg-input">
                    <option value="">Todos</option>
                    @foreach ($profesores as $profesor)
                        <option value="{{ $profesor->id_profesor }}" @selected(($filtros['profesor'] ?? null) == $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="sg-label">Carrera</label>
                <select name="carrera" class="sg-input">
                    <option value="">Todas</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}" @selected(($filtros['carrera'] ?? null) == $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Filtrar</button>
        </form>

        <div class="overflow-x-auto">
            @if ($estado === 'sin_docente')
                <table class="sg-tabla">
                    <thead><tr><th>Materia</th><th>Carrera</th><th>Turno</th><th>Hs. cátedra</th><th style="text-align:center">Acción</th></tr></thead>
                    <tbody class="sg-filas">
                        @forelse ($sinDocente as ['materia' => $materia, 'turno' => $turno])
                            <tr>
                                <td class="font-semibold text-slate-800">{{ $materia->nombre }} <span class="text-xs text-slate-400">({{ $materia->anioCursada->nombre_anio }})</span></td>
                                <td class="text-slate-500">{{ $materia->carrera->nombre_carrera }}</td>
                                <td><span class="sg-badge sg-badge--azul sg-badge--sin-punto">{{ $turno->nombre_turno }}</span></td>
                                <td>{{ $materia->horas_catedra ? $materia->horas_catedra . ' h' : '—' }} @if ($materia->es_taller)<span class="sg-badge sg-badge--azul sg-badge--sin-punto">Práctica</span>@endif</td>
                                <td class="text-center"><button type="button" onclick="designarMateria({{ $materia->id_materia }}, {{ $materia->id_carrera }}, {{ $turno->id_turno_cursada }})" class="sg-btn sg-btn--suave sg-btn--chico">Designar profesor</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-sg.vacio icono="check-circle">Todas las materias tienen docente en todos sus turnos.</x-sg.vacio></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="sg-tabla">
                    <thead>
                        <tr><th>Profesor</th><th>Materia</th><th>Hs. cátedra</th><th>Tipo</th><th>Alta / Baja</th><th>Estado</th><th style="text-align:center">Acciones</th></tr>
                    </thead>
                    <tbody class="sg-filas">
                        @forelse ($designaciones as $d)
                            @php
                                $payload = [
                                    'id' => $d->id_asignacion, 'id_profesor' => $d->id_profesor, 'id_materia' => $d->id_materia, 'id_anio_lectivo' => $d->id_anio_lectivo,
                                    'tipo' => $d->tipo, 'horas_catedra' => $d->horas_catedra, 'fecha_alta' => $d->fecha_alta?->toDateString(),
                                    'fecha_baja' => $d->fecha_baja?->toDateString(),
                                    'id_carrera' => $d->materia->id_carrera, 'id_turno' => $d->id_turno_cursada, 'activa' => $d->activa,
                                    'resolucion_numero' => $d->resolucion ? explode('/', $d->resolucion)[0] : '',
                                    'docente' => $d->profesor->apellido . ', ' . $d->profesor->nombre,
                                ];
                            @endphp
                            <tr class="{{ in_array($d->estado, ['Finalizada', 'Vencida'], true) ? 'opacity-60' : '' }}">
                                <td class="font-semibold text-slate-800">{{ $d->profesor->apellido }}, {{ $d->profesor->nombre }}</td>
                                <td>
                                    <p class="text-slate-700">{{ $d->materia->nombre }}</p>
                                    <p class="text-xs text-slate-400">{{ $d->materia->carrera->nombre_carrera }} · {{ $d->materia->anioCursada?->nombre_anio }}{{ $d->turnoCursada ? ' · ' . $d->turnoCursada->nombre_turno : '' }}{{ $d->resolucion ? ' · Res. ' . $d->resolucion : '' }}</p>
                                </td>
                                <td class="font-semibold text-[#0B3D91] whitespace-nowrap">
                                    {{ $d->horas_catedra ? $d->horas_catedra . ' h' : '—' }}
                                    @php $cargadas = round($d->horarios->sum('minutos') / \App\Models\Materia::MINUTOS_HORA_CATEDRA, 1); @endphp
                                    <a href="{{ route('admin.horarios.index', ['id_carrera' => $d->materia->id_carrera, 'id_turno_cursada' => $d->id_turno_cursada, 'id_anio_cursada' => $d->materia->id_anio_cursada, 'id_anio_lectivo' => $d->id_anio_lectivo]) }}"
                                       class="block text-xs font-normal {{ $d->horas_catedra && $cargadas >= $d->horas_catedra ? 'text-emerald-600' : 'text-slate-400' }} hover:underline">Horario: {{ rtrim(rtrim(number_format($cargadas, 1, ',', ''), '0'), ',') }}/{{ $d->horas_catedra ?? 0 }} h</a>
                                </td>
                                <td><span class="sg-badge sg-badge--sin-punto {{ $colorTipo[$d->tipo] ?? 'sg-badge--gris' }}">{{ $d->tipo }}</span></td>
                                <td class="text-xs text-slate-500 whitespace-nowrap">
                                    {{ \App\Support\FechaEsp::corta($d->fecha_alta) ?? '—' }}<br>
                                    {{ $d->fecha_baja ? 'hasta ' . \App\Support\FechaEsp::corta($d->fecha_baja) : 'sin fecha de baja' }}
                                </td>
                                <td><span class="sg-badge {{ $colorEstado[$d->estado] }}">{{ $d->estado }}</span></td>
                                <td><div class="flex justify-center gap-1">
                                    <button type="button" onclick='editarDesignacion(@json($payload))' title="Editar" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91]"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                    @if ($d->activa)
                                        <form method="POST" action="{{ route('admin.designaciones.finalizar', $d) }}" data-confirm="¿Finalizar la designación de {{ $d->profesor->apellido }} en {{ $d->materia->nombre }}?">
                                            @csrf @method('PUT')
                                            <button type="submit" title="Finalizar" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-amber-50 hover:text-amber-600"><x-sg.icono nombre="ban" class="h-4 w-4" /></button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.designaciones.destroy', $d) }}" data-confirm="¿Eliminar esta designación{{ $d->horarios->isNotEmpty() ? ' y sus horarios' : '' }}? No se puede deshacer.">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Eliminar" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                    </form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><x-sg.vacio icono="briefcase">No hay designaciones con estos filtros.</x-sg.vacio></td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    @php
        // (@json separa por comas: los datos con comas se arman acá antes)
        $docentesJs = $profesores->map(fn ($p) => ['id' => $p->id_profesor, 'nombre' => $p->apellido . ', ' . $p->nombre, 'dni' => $p->dni])->values();
        $nombresCarreraJs = $carreras->pluck('nombre_carrera', 'id_carrera');
        $turnosPorCarreraJs = $carreras->mapWithKeys(fn ($c) => [$c->id_carrera => $c->turnos->pluck('id_turno_cursada')->map(fn ($t) => (string) $t)]);
        // Resolución del plan vigente de cada carrera (solo la parte numérica: el campo
        // de la designación es solo números, p. ej. de "3576e/s" se toma "3576").
        $resolucionesPorCarreraJs = $carreras->mapWithKeys(function ($c) {
            $vigente = $c->planes->first(fn ($p) => $p->estado() === 'Vigente');
            $numero = $vigente ? preg_replace('/[^0-9].*$/', '', (string) $vigente->resolucion_ministerial) : '';

            return [$c->id_carrera => $numero !== '' ? $numero : null];
        });
    @endphp
    <script>
        const datosMaterias = @json($datosMaterias);
        const vigentes = @json($vigentes);
        const docentes = @json($docentesJs);
        const nombresCarrera = @json($nombresCarreraJs);
        const turnosPorCarrera = @json($turnosPorCarreraJs);
        const resolucionesPorCarrera = @json($resolucionesPorCarreraJs);
        const rutaBaseDesignaciones = @json(url('/admin/designaciones'));
        const rutaGuardarDesignacion = @json(route('admin.designaciones.store'));
        let designacionEditando = null; // al editar, sus horas no cuentan como ocupadas
        const porId = id => document.getElementById(id);

        // Carrera → solo sus materias.
        function filtrarMaterias(materiaElegida = '') {
            const carrera = porId('input-designacion-carrera').value, select = porId('input-designacion-materia');
            // Turno: solo los turnos en que se dicta la carrera.
            const turno = porId('input-designacion-turno'), turnos = turnosPorCarrera[carrera] || [];
            [...turno.options].forEach(o => { if (o.value) { o.hidden = !!carrera && !turnos.includes(o.value); o.disabled = o.hidden; } });
            if (turno.selectedOptions[0]?.hidden) turno.value = '';
            if (carrera && turnos.length === 1) turno.value = turnos[0];
            select.disabled = !carrera;
            select.options[0].textContent = carrera ? 'Elegir materia...' : 'Primero elegí la carrera';
            [...select.options].slice(1).forEach(o => o.hidden = o.dataset.carrera !== carrera);
            select.value = materiaElegida;
            actualizarMateria();
            autocompletarResolucion();
        }

        // Al elegir la carrera, trae sola la resolución del plan vigente de esa carrera
        // (solo para designaciones nuevas, y sin pisar un número ya escrito a mano).
        // Siempre la resolución del plan vigente de la carrera elegida: tanto al crear
        // una designación nueva como al editar una existente (está bloqueada, así que
        // no tiene sentido dejar un número viejo si no coincide con la carrera actual).
        function autocompletarResolucion() {
            const resolucion = resolucionesPorCarrera[porId('input-designacion-carrera').value];
            porId('input-designacion-resolucion').value = resolucion || '';
        }

        // Recuadro de la materia + carga horaria (bloqueada; en práctica se elige).
        function actualizarMateria() {
            const select = porId('input-designacion-materia'), id = select.value, m = datosMaterias[id];
            const turno = porId('input-designacion-turno').value;
            const caja = porId('info-materia'), carga = porId('texto-carga'), detalle = porId('texto-carga-detalle'), horas = porId('input-designacion-horas');
            const elegidas = +horas.value || 0; // lo que ya estaba elegido (al editar o al volver con error)
            porId('carga-bloqueada').classList.remove('hidden'); porId('carga-practica').classList.add('hidden');
            if (!m) { caja.classList.add('hidden'); carga.textContent = '—'; detalle.textContent = ''; horas.value = ''; return; }

            const otras = vigentes.filter(v => String(v.id_materia) === id && (!turno || String(v.id_turno) === turno) && (!designacionEditando || v.id !== designacionEditando.id));
            porId('info-materia-nombre').textContent = select.selectedOptions[0].textContent.split('·')[0].trim();
            porId('info-materia-detalle').textContent = [nombresCarrera[porId('input-designacion-carrera').value], select.selectedOptions[0].textContent.split('·')[1]?.trim(),
                m.horas ? m.horas + ' h cátedra' : 'sin horas cátedra', m.practica ? 'práctica' : ''].filter(Boolean).join(' · ');
            porId('info-materia-docentes').innerHTML = otras.length
                ? otras.map(o => `• ${o.docente}: ${o.horas ?? '?'} h (${o.tipo})`).join('<br>')
                : (turno ? 'Todavía no tiene docente en este turno.' : 'Elegí el turno para ver quién la dicta.');
            caja.classList.remove('hidden');

            if (!m.horas) { carga.textContent = '—'; detalle.textContent = 'cargá las horas en Materias'; horas.value = ''; return; }
            const libres = Math.max(m.horas - otras.reduce((t, o) => t + (o.horas || 0), 0), 0);
            if (m.practica) {
                // Práctica: las horas se reparten entre varios docentes; se elige cuántas da este.
                if (!libres) { carga.textContent = '0 h'; detalle.textContent = `no quedan horas libres de ${m.horas}`; horas.value = ''; return; }
                const sel = porId('select-carga-practica');
                sel.innerHTML = Array.from({ length: libres }, (_, i) => i + 1).map(h => `<option value="${h}">${h} h cátedra${h === libres ? ' (todas las libres)' : ''}</option>`).join('');
                sel.value = elegidas >= 1 && elegidas <= libres ? elegidas : libres;
                horas.value = sel.value;
                porId('texto-carga-practica').textContent = `Práctica: quedan ${libres} h libres de ${m.horas}. Elegí cuántas da este docente.`;
                porId('carga-bloqueada').classList.add('hidden'); porId('carga-practica').classList.remove('hidden');
                return;
            }
            horas.value = m.horas;
            carga.textContent = m.horas + ' h';
            detalle.textContent = 'de la materia';
        }

        // Docente: buscador por apellido, nombre o DNI.
        function buscarDocente() {
            const texto = porId('input-designacion-buscar').value.trim().toLowerCase();
            const lista = porId('lista-docentes');
            const encontrados = docentes.filter(d => !texto || d.nombre.toLowerCase().includes(texto) || String(d.dni).includes(texto)).slice(0, 30);
            lista.innerHTML = encontrados.length
                ? encontrados.map(d => `<button type="button" data-id="${d.id}" class="block w-full px-4 py-2 text-left text-sm hover:bg-blue-50">${d.nombre} <span class="text-xs text-slate-400">DNI ${d.dni}</span></button>`).join('')
                : '<p class="px-4 py-2 text-sm text-slate-400">No se encontró ningún profesor.</p>';
            lista.querySelectorAll('button').forEach(b => b.onclick = () => elegirDocente(b.dataset.id, b.textContent.split(' DNI')[0]));
            lista.classList.remove('hidden');
        }
        function elegirDocente(id, nombre) {
            porId('input-designacion-profesor').value = id;
            porId('input-designacion-buscar').value = nombre;
            porId('lista-docentes').classList.add('hidden');
            porId('campos-designacion').disabled = !id; // con el docente elegido se habilita todo
        }
        document.addEventListener('click', e => { if (!e.target.closest('#lista-docentes, #input-designacion-buscar')) porId('lista-docentes').classList.add('hidden'); });

        function actualizarAnioResolucion() {
            porId('sufijo-resolucion').textContent = '/' + porId('input-designacion-anio').selectedOptions[0].dataset.anio;
        }

        function ponerFecha(id, valor) { const el = porId(id); el._flatpickr ? el._flatpickr.setDate(valor || null) : el.value = valor ?? ''; }

        function editarDesignacion(d) {
            limpiarDesignacion();
            designacionEditando = d;
            porId('form-designacion').action = rutaBaseDesignaciones + '/' + d.id;
            porId('input-designacion-method').value = 'PUT';
            porId('input-designacion-carrera').value = d.id_carrera;
            porId('input-designacion-turno').value = d.id_turno ?? '';
            filtrarMaterias(String(d.id_materia));
            elegirDocente(d.id_profesor, d.docente);
            document.querySelector(`[name="tipo"][value="${d.tipo}"]`).checked = true;
            document.querySelector(`[name="activa"][value="${d.activa ? 1 : 0}"]`).checked = true;
            porId('input-designacion-anio').value = d.id_anio_lectivo; actualizarAnioResolucion();
            ponerFecha('input-designacion-alta', d.fecha_alta);
            ponerFecha('input-designacion-baja', d.fecha_baja);
            porId('input-designacion-horas').value = d.horas_catedra ?? ''; // en práctica, queda elegida la que tenía
            actualizarMateria();
            porId('titulo-form-designacion').textContent = 'Editar designación';
            porId('btn-designacion').innerHTML = 'Guardar cambios';
            porId('editando-designacion-aviso').classList.remove('hidden');
            porId('tarjeta-designacion').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function limpiarDesignacion() {
            designacionEditando = null;
            const form = porId('form-designacion');
            form.reset();
            form.action = rutaGuardarDesignacion;
            porId('input-designacion-method').value = '';
            porId('input-designacion-profesor').value = '';
            porId('input-designacion-horas').value = '';
            porId('campos-designacion').disabled = true;
            ponerFecha('input-designacion-alta', @json(now()->toDateString()));
            ponerFecha('input-designacion-baja', null);
            porId('titulo-form-designacion').textContent = 'Nueva designación';
            porId('btn-designacion').innerHTML = 'Guardar designación';
            porId('editando-designacion-aviso').classList.add('hidden');
            actualizarAnioResolucion();
            filtrarMaterias();
        }

        // "Designar profesor" desde Materias sin docente.
        function designarMateria(idMateria, idCarrera, idTurno) {
            limpiarDesignacion();
            porId('input-designacion-carrera').value = idCarrera;
            filtrarMaterias(String(idMateria));
            porId('input-designacion-turno').value = idTurno;
            actualizarMateria();
            porId('tarjeta-designacion').scrollIntoView({ behavior: 'smooth', block: 'start' });
            porId('input-designacion-buscar').focus();
        }

        // Al volver con un error: se recupera lo cargado.
        document.addEventListener('DOMContentLoaded', function () {
            const materiaVieja = @json(old('id_materia'));
            const opcion = materiaVieja && document.querySelector(`#input-designacion-materia option[value="${materiaVieja}"]`);
            if (opcion) { porId('input-designacion-carrera').value = opcion.dataset.carrera; filtrarMaterias(String(materiaVieja)); } else { filtrarMaterias(); }
            const profesorViejo = @json(old('id_profesor'));
            const doc = docentes.find(d => String(d.id) === String(profesorViejo));
            if (doc) elegirDocente(doc.id, doc.nombre);
            actualizarAnioResolucion();
        });
    </script>
@endsection
