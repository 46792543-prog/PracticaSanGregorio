@extends('layouts.admin')

@section('titulo', 'Régimen de Correlatividades')

@php
    // Datos para el editor (JS): materias del plan, correlativas actuales por materia destino
    // y lo que hay que mostrar al abrir (lo recién enviado si hubo un error, o el grupo a editar).
    $datosMaterias = $materias->values()->map(fn ($materia) => [
        'id' => $materia->id_materia,
        'orden' => $materia->numero_orden,
        'nombre' => $materia->nombre,
    ]);
    $datosGrupos = $correlativas->map(fn ($grupo) => $grupo->map(fn ($correlativa) => [
        'id' => $correlativa->id_materia_requisito,
        'cursar' => (bool) $correlativa->requiere_regularizada,
        'rendir' => (bool) $correlativa->requiere_aprobada,
    ])->values());

    if (old('id_materia_principal')) {
        $inicial = [
            'principal' => (int) old('id_materia_principal'),
            'requisitos' => collect(old('requisitos', []))->values()->map(fn ($fila) => [
                'id' => (int) ($fila['id_materia'] ?? 0),
                'cursar' => ! empty($fila['para_cursar']),
                'rendir' => ! empty($fila['para_rendir']),
            ]),
        ];
    } else {
        $inicial = ['principal' => $principalEditar, 'requisitos' => $principalEditar ? $datosGrupos[$principalEditar] : []];
    }
@endphp

@section('contenido')
    <x-sg.encabezado titulo="Régimen de Correlatividades" :subtitulo="$carrera->nombre_carrera" eyebrow="Carreras y planes" icono="link">
        <a href="{{ route('admin.carreras.materias', $carrera) }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Materias</a>
        <a href="{{ route('admin.carreras.plan', $carrera) }}" class="sg-btn sg-btn--primario"><x-sg.icono nombre="eye" /> Ver plan de estudio</a>
    </x-sg.encabezado>

    <div class="grid gap-6 xl:grid-cols-5 items-start">
        {{-- ====================== Editor (crear y editar) ====================== --}}
        <div id="editor-correlativas" class="sg-card xl:col-span-2 xl:sticky xl:top-24 border-t-4 border-t-[#FFC107]">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="link" /></span>
                    <div>
                        <h3 id="editor-titulo">Nueva correlativa</h3>
                        <p id="editor-subtitulo">Elegí la materia destino y qué materias necesita</p>
                    </div>
                </div>
            </div>

            <form id="form-correlativas" method="POST" action="{{ route('admin.carreras.correlativas.store', $carrera) }}" class="sg-card__body space-y-6" novalidate>
                @csrf

                {{-- Paso 1: materia destino --}}
                <div>
                    <label for="buscar-destino" class="sg-label flex items-center gap-2">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">1</span>
                        Materia destino
                    </label>
                    <input type="hidden" name="id_materia_principal" id="valor-destino">

                    <div id="destino-elegido" class="hidden items-center gap-3 rounded-xl border-[1.5px] border-[#0B3D91] bg-blue-50 px-3 py-2.5">
                        <span id="destino-orden" class="flex h-8 min-w-[2rem] shrink-0 items-center justify-center rounded-lg bg-[#0B3D91] px-1.5 text-xs font-bold text-white"></span>
                        <span id="destino-nombre" class="min-w-0 flex-1 text-sm font-semibold text-slate-800"></span>
                        <button type="button" id="destino-cambiar" class="sg-btn sg-btn--borde sg-btn--chico shrink-0">Cambiar</button>
                    </div>

                    <div id="destino-buscador" class="relative">
                        <input id="buscar-destino" type="search" placeholder="Buscar por número o nombre..." autocomplete="off" class="sg-input w-full">
                        <div id="lista-destino" role="listbox" class="absolute z-30 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg"></div>
                    </div>
                    <p id="destino-aviso" class="sg-ayuda hidden">Esta materia ya tenía correlativas: se cargaron para que las edites.</p>
                </div>

                {{-- Paso 2: materias requeridas --}}
                <div>
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <label for="buscar-requisito" class="sg-label !mb-0 flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">2</span>
                            Materias requeridas
                        </label>
                        <span id="contador-requisitos" class="sg-badge sg-badge--gris sg-badge--sin-punto hidden"></span>
                    </div>

                    <div class="relative">
                        <input id="buscar-requisito" type="search" placeholder="Agregar materia requerida..." autocomplete="off" class="sg-input w-full" disabled>
                        <div id="lista-requisito" role="listbox" class="absolute z-30 mt-1 hidden max-h-64 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-1 shadow-lg"></div>
                    </div>

                    <ul id="filas-requisitos" class="mt-3 space-y-2"></ul>

                    <div id="requisitos-vacio" class="mt-3 rounded-xl border-[1.5px] border-dashed border-slate-200 px-4 py-5 text-center text-sm text-slate-400">
                        Primero elegí la materia destino; después agregá las materias que necesita.
                    </div>

                    <p class="sg-ayuda mt-3 flex flex-wrap gap-x-4 gap-y-1">
                        <span><span class="font-semibold text-emerald-700">Para cursar:</span> la tiene que tener regularizada.</span>
                        <span><span class="font-semibold text-[#0B3D91]">Para rendir:</span> la tiene que tener aprobada.</span>
                    </p>
                </div>

                <div class="flex gap-3 border-t border-slate-100 pt-5">
                    <button type="button" id="editor-cancelar" class="sg-btn sg-btn--borde flex-1 justify-center">Limpiar</button>
                    <button type="submit" id="editor-guardar" class="sg-btn sg-btn--azul flex-[2] justify-center">
                        <x-sg.icono nombre="check" /> <span id="editor-guardar-texto">Guardar correlativas</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- ====================== Matriz ====================== --}}
        <div class="sg-card xl:col-span-3 overflow-hidden">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="table" /></span>
                    <div><h3>Matriz de correlatividades</h3><p>Afectan las condiciones de inscripción a examen del alumno</p></div>
                </div>
            </div>
            <div id="matriz-correlativas-scroll" class="overflow-x-auto">
                <table class="sg-tabla">
                    <thead class="sticky top-0 z-10 bg-slate-50">
                        <tr>
                            <th>Materia cursante / destino</th>
                            <th>Para cursar</th>
                            <th>Para rendir</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($correlativas as $idPrincipal => $grupo)
                            @php
                                $correlativaPrincipal = $materias[$idPrincipal];
                                $paraCursar = $grupo->where('requiere_regularizada', true)->values();
                                $paraRendir = $grupo->where('requiere_aprobada', true)->values();
                            @endphp
                            <tr data-fila-destino="{{ $idPrincipal }}" class="transition-colors">
                                <td class="font-semibold !text-slate-800">
                                    <span class="mr-1 text-xs font-bold text-slate-400">#{{ $correlativaPrincipal->numero_orden }}</span>
                                    {{ $correlativaPrincipal->nombre }}
                                </td>
                                @foreach ([[$paraCursar, 'sg-badge--verde'], [$paraRendir, 'sg-badge--azul']] as [$lista, $color])
                                    <td>
                                        <div class="flex w-max flex-nowrap items-center gap-1 whitespace-nowrap">
                                            @forelse ($lista as $correlativa)
                                                @php $requisito = $materias[$correlativa->id_materia_requisito] ?? null; @endphp
                                                @if (!$loop->first)<span class="text-slate-400">-</span>@endif
                                                <span class="sg-badge {{ $color }} whitespace-nowrap cursor-help" title="{{ $requisito?->nombre }}">{{ $requisito?->numero_orden }}</span>
                                            @empty
                                                <span class="text-slate-300">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                @endforeach
                                <td>
                                    <div class="flex flex-nowrap items-center gap-2">
                                        <a href="{{ route('admin.carreras.correlativas.grupo.edit', [$carrera, $idPrincipal]) }}" data-editar="{{ $idPrincipal }}" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="pencil" /> Editar</a>
                                        <form method="POST" action="{{ route('admin.carreras.correlativas.grupo.destroy', [$carrera, $idPrincipal]) }}" onsubmit="return confirm('¿Eliminar todas las correlativas de {{ addslashes($correlativaPrincipal->nombre) }}?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="sg-btn sg-btn--peligro sg-btn--chico sg-btn--icono" title="Eliminar" aria-label="Eliminar correlativas de {{ $correlativaPrincipal->nombre }}"><x-sg.icono nombre="trash" /></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-sg.vacio icono="link">Todavía no se cargaron correlativas.</x-sg.vacio></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const MATERIAS = @json($datosMaterias);
            const GRUPOS = @json($datosGrupos);
            const INICIAL = @json($inicial);
            const porId = new Map(MATERIAS.map(function (m) { return [String(m.id), m]; }));

            // ---------- Matriz: alto para 3 filas con scroll ----------
            const matrizScroll = document.getElementById('matriz-correlativas-scroll');
            const filasMatriz = matrizScroll.querySelectorAll('tbody tr');
            const encabezadoMatriz = matrizScroll.querySelector('thead');
            function ajustarScrollMatriz() {
                if (filasMatriz.length <= 3) { matrizScroll.style.maxHeight = ''; return; }
                const alto = encabezadoMatriz.getBoundingClientRect().height
                    + Array.from(filasMatriz).slice(0, 3).reduce(function (t, f) { return t + f.getBoundingClientRect().height; }, 0);
                matrizScroll.style.maxHeight = alto + 'px';
            }
            ajustarScrollMatriz();
            window.addEventListener('resize', ajustarScrollMatriz);

            // ---------- Editor ----------
            const el = function (id) { return document.getElementById(id); };
            const form = el('form-correlativas');
            const valorDestino = el('valor-destino');
            const buscarDestino = el('buscar-destino');
            const listaDestino = el('lista-destino');
            const buscarRequisito = el('buscar-requisito');
            const listaRequisito = el('lista-requisito');
            const filas = el('filas-requisitos');

            // Estado: materia destino y lista de requeridas [{id, cursar, rendir}]
            let destino = null;
            let requisitos = [];

            function normalizar(texto) {
                return String(texto).normalize('NFD').replace(/[̀-ͯ]/g, '').toLocaleLowerCase();
            }

            function etiqueta(m) { return '#' + m.orden + ' · ' + m.nombre; }

            function crear(tag, clase, texto) {
                const nodo = document.createElement(tag);
                if (clase) nodo.className = clase;
                if (texto !== undefined) nodo.textContent = texto;
                return nodo;
            }

            // Lista desplegable con filtro; muestra todas las opciones al enfocar.
            function mostrarOpciones(input, lista, excluir, alElegir) {
                const consulta = normalizar(input.value.trim());
                lista.replaceChildren();
                MATERIAS.forEach(function (m) {
                    if (excluir(m)) return;
                    if (consulta && !normalizar(m.orden + ' ' + m.nombre).includes(consulta)) return;
                    const op = crear('button', 'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-slate-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none');
                    op.type = 'button';
                    op.appendChild(crear('span', 'w-8 shrink-0 text-xs font-bold text-slate-400', '#' + m.orden));
                    op.appendChild(crear('span', '', m.nombre));
                    if (GRUPOS[m.id] && lista === listaDestino) {
                        op.appendChild(crear('span', 'ml-auto shrink-0 text-[11px] font-semibold text-amber-600', 'ya tiene'));
                    }
                    op.addEventListener('mousedown', function (e) { e.preventDefault(); });
                    op.addEventListener('click', function () { alElegir(m); });
                    lista.appendChild(op);
                });
                if (!lista.children.length) {
                    lista.appendChild(crear('p', 'px-3 py-2 text-sm text-slate-400', 'No hay materias que coincidan.'));
                }
                lista.classList.remove('hidden');
            }

            function elegirDestino(m, cargarExistentes) {
                destino = m;
                valorDestino.value = m.id;
                buscarDestino.value = '';
                listaDestino.classList.add('hidden');
                if (cargarExistentes) {
                    requisitos = (GRUPOS[m.id] || []).map(function (r) { return Object.assign({}, r); });
                }
                // La materia destino no puede requerirse a sí misma
                requisitos = requisitos.filter(function (r) { return String(r.id) !== String(m.id); });
                el('destino-aviso').classList.toggle('hidden', !(cargarExistentes && GRUPOS[m.id]));
                render();
                buscarRequisito.focus();
            }

            function agregarRequisito(m) {
                requisitos.push({ id: m.id, cursar: true, rendir: true });
                buscarRequisito.value = '';
                listaRequisito.classList.add('hidden');
                render();
                buscarRequisito.focus();
            }

            function limpiar() {
                destino = null;
                requisitos = [];
                valorDestino.value = '';
                el('destino-aviso').classList.add('hidden');
                history.replaceState(null, '', @json(route('admin.carreras.correlativas', $carrera)));
                render();
            }

            function pill(texto, marcado, color, alCambiar, nombre) {
                const label = crear('label', 'flex cursor-pointer select-none items-center gap-1.5 rounded-lg border-[1.5px] px-2.5 py-1.5 text-xs font-semibold transition-colors '
                    + (marcado ? color : 'border-slate-200 text-slate-400 hover:border-slate-300'));
                const check = crear('input', 'h-3.5 w-3.5 rounded');
                check.type = 'checkbox';
                check.name = nombre;
                check.value = '1';
                check.checked = marcado;
                check.addEventListener('change', function () { alCambiar(check.checked); });
                label.append(check, document.createTextNode(texto));
                return label;
            }

            function render() {
                const editando = destino && GRUPOS[destino.id];
                const enMatriz = destino ? String(destino.id) : null;

                // Encabezado y botones según el modo
                el('editor-titulo').textContent = editando ? 'Editar correlativas' : 'Nueva correlativa';
                el('editor-subtitulo').textContent = editando ? 'Agregá, quitá o cambiá las materias requeridas' : 'Elegí la materia destino y qué materias necesita';
                el('editor-guardar-texto').textContent = editando ? 'Guardar cambios' : 'Guardar correlativas';
                el('editor-cancelar').textContent = destino ? 'Cancelar' : 'Limpiar';
                filasMatriz.forEach(function (f) { f.classList.toggle('!bg-amber-50', f.dataset.filaDestino === enMatriz); });

                // Destino
                el('destino-elegido').classList.toggle('hidden', !destino);
                el('destino-elegido').classList.toggle('flex', !!destino);
                el('destino-buscador').classList.toggle('hidden', !!destino);
                if (destino) {
                    el('destino-orden').textContent = '#' + destino.orden;
                    el('destino-nombre').textContent = destino.nombre;
                }

                // Requeridas
                buscarRequisito.disabled = !destino;
                filas.replaceChildren();
                requisitos.forEach(function (r, i) {
                    const m = porId.get(String(r.id)) || { orden: '?', nombre: 'Materia ' + r.id };
                    const sinMarca = !r.cursar && !r.rendir;
                    const li = crear('li', 'flex flex-wrap items-center gap-2 rounded-xl border px-3 py-2 sm:flex-nowrap '
                        + (sinMarca ? 'border-red-300 bg-red-50' : 'border-slate-200 bg-white'));

                    const oculto = crear('input');
                    oculto.type = 'hidden';
                    oculto.name = 'requisitos[' + i + '][id_materia]';
                    oculto.value = r.id;

                    const nombre = crear('span', 'flex min-w-0 flex-1 items-center gap-2 text-sm font-medium text-slate-700');
                    nombre.appendChild(crear('span', 'w-7 shrink-0 text-xs font-bold text-slate-400', '#' + m.orden));
                    nombre.appendChild(crear('span', 'min-w-0', m.nombre));

                    const opciones = crear('div', 'flex shrink-0 items-center gap-1.5');
                    opciones.appendChild(pill('Para cursar', r.cursar, 'border-emerald-400 bg-emerald-50 text-emerald-700',
                        function (v) { r.cursar = v; render(); }, 'requisitos[' + i + '][para_cursar]'));
                    opciones.appendChild(pill('Para rendir', r.rendir, 'border-[#4FC3F7] bg-blue-50 text-[#0B3D91]',
                        function (v) { r.rendir = v; render(); }, 'requisitos[' + i + '][para_rendir]'));

                    const quitar = crear('button', 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600');
                    quitar.type = 'button';
                    quitar.title = 'Quitar';
                    quitar.setAttribute('aria-label', 'Quitar ' + m.nombre);
                    quitar.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';
                    quitar.addEventListener('click', function () { requisitos.splice(i, 1); render(); });

                    opciones.appendChild(quitar);
                    li.append(oculto, nombre, opciones);
                    filas.appendChild(li);
                });

                const vacio = el('requisitos-vacio');
                vacio.classList.toggle('hidden', requisitos.length > 0);
                vacio.textContent = destino
                    ? 'Todavía no agregaste materias requeridas. Buscalas arriba.'
                    : 'Primero elegí la materia destino; después agregá las materias que necesita.';

                const contador = el('contador-requisitos');
                contador.classList.toggle('hidden', requisitos.length === 0);
                contador.textContent = requisitos.length + (requisitos.length === 1 ? ' materia' : ' materias');
            }

            // ---------- Eventos ----------
            function excluirRequisito(m) {
                return (destino && m.id === destino.id) || requisitos.some(function (r) { return String(r.id) === String(m.id); });
            }
            function abrirDestino() { mostrarOpciones(buscarDestino, listaDestino, function () { return false; }, function (m) { elegirDestino(m, true); }); }
            function abrirRequisito() { mostrarOpciones(buscarRequisito, listaRequisito, excluirRequisito, agregarRequisito); }

            buscarDestino.addEventListener('focus', abrirDestino);
            buscarDestino.addEventListener('input', abrirDestino);
            buscarRequisito.addEventListener('focus', abrirRequisito);
            buscarRequisito.addEventListener('input', abrirRequisito);
            [buscarDestino, buscarRequisito].forEach(function (input) {
                input.addEventListener('blur', function () { input.nextElementSibling.classList.add('hidden'); });
                input.addEventListener('keydown', function (e) {
                    const lista = input.nextElementSibling;
                    if (e.key === 'Escape') { lista.classList.add('hidden'); }
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const primera = lista.querySelector('button');
                        if (primera && !lista.classList.contains('hidden')) primera.click();
                    }
                });
            });

            el('destino-cambiar').addEventListener('click', function () {
                limpiar();
                buscarDestino.focus();
            });
            el('editor-cancelar').addEventListener('click', limpiar);

            // "Editar" en la matriz: carga el grupo en el editor al instante, sin recargar la página.
            document.querySelectorAll('[data-editar]').forEach(function (enlace) {
                enlace.addEventListener('click', function (e) {
                    const m = porId.get(enlace.dataset.editar);
                    if (!m) return;
                    e.preventDefault();
                    elegirDestino(m, true);
                    el('destino-aviso').classList.add('hidden');
                    history.replaceState(null, '', enlace.href);
                    el('editor-correlativas').scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            form.addEventListener('submit', function (e) {
                let error = '';
                if (!destino) error = 'Elegí la materia destino.';
                else if (!requisitos.length) error = 'Agregá al menos una materia requerida.';
                else if (requisitos.some(function (r) { return !r.cursar && !r.rendir; })) error = 'Cada materia requerida tiene que estar marcada "Para cursar", "Para rendir" o ambas (están marcadas en rojo).';
                if (error) {
                    e.preventDefault();
                    alert(error);
                    return;
                }
                const boton = el('editor-guardar');
                boton.disabled = true;
                el('editor-guardar-texto').textContent = 'Guardando...';
            });

            // ---------- Estado inicial ----------
            if (INICIAL.principal && porId.has(String(INICIAL.principal))) {
                destino = porId.get(String(INICIAL.principal));
                valorDestino.value = destino.id;
                requisitos = (INICIAL.requisitos || []).filter(function (r) { return porId.has(String(r.id)); });
            }
            render();
        })();
    </script>
@endsection
