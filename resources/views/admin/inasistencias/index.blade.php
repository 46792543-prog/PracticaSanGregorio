{{-- Solo en el panel de Secretaría --}}
@php $panel = 'admin'; @endphp
@extends('layouts.admin')

@section('titulo', 'Inasistencias docentes')

@section('contenido')
    <x-sg.encabezado titulo="Inasistencias docentes" subtitulo="Licencias e inasistencias de los docentes. Se registran las horas cátedra de las clases que pierden: las sin goce de sueldo (o con documentación pendiente) se descuentan del pago." eyebrow="Plantel docente" icono="clipboard" />

    @php
        $colorDoc = ['Presentada' => 'sg-badge--verde', 'Pendiente' => 'sg-badge--amarillo'];
        // Campo con etiqueta sobre el borde (como en la planilla de licencias)
        $campo = 'relative rounded-2xl border border-amber-100 bg-[#FFFDF5] focus-within:border-[#0B3D91]';
        $etiqueta = 'absolute -top-2 left-4 z-10 bg-white px-1 text-[11px] font-semibold text-slate-500';
        $control = 'w-full rounded-2xl !bg-transparent pl-12 pr-4 py-3 text-[15px] text-slate-700 outline-none'; // transparente: el fondo lo pone el campo
    @endphp

    {{-- Listado del mes --}}
    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head flex flex-wrap items-center justify-between gap-3">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--rojo"><x-sg.icono nombre="calendar" /></span>
                <div>
                    <h3>Inasistencias de <span class="capitalize">{{ $periodo->translatedFormat('F Y') }}</span></h3>
                    <p>{{ $licencias->count() }} registrada{{ $licencias->count() === 1 ? '' : 's' }} · {{ $licencias->filter->descuenta->flatMap->clases->sum('horas_catedra') }} h cátedra que se descuentan</p>
                </div>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <form method="GET" class="flex flex-wrap items-end gap-2">
                    <select name="profesor" onchange="this.form.submit()" class="sg-input">
                        <option value="">Todos los docentes</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}" @selected($filtroProfesor === $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <input type="month" name="periodo" value="{{ $periodo->format('Y-m') }}" required onchange="this.form.submit()" class="sg-input">
                </form>
                <button type="button" onclick="inaAbrir()" class="sg-btn sg-btn--primario"><x-sg.icono nombre="plus" /> Registrar inasistencia</button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr><th>Docente</th><th>Fechas</th><th>Tipo de licencia</th><th>Clases que pierde</th><th>Documentación</th><th>Pago</th><th style="text-align:center">Acciones</th></tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($licencias as $licencia)
                        <tr>
                            <td>
                                <p class="font-semibold text-slate-700">{{ $licencia->profesor->apellido }}, {{ $licencia->profesor->nombre }}</p>
                                <p class="text-xs text-slate-400">DNI {{ $licencia->profesor->dni }}</p>
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="font-semibold text-slate-700">{{ $licencia->desde->format('d/m/Y') }}</span>
                                @unless ($licencia->desde->eq($licencia->hasta))<span class="text-slate-400"> al </span><span class="font-semibold text-slate-700">{{ $licencia->hasta->format('d/m/Y') }}</span>@endunless
                                @if ($licencia->desde->isFuture())<br><span class="sg-badge sg-badge--azul mt-1">Anticipada</span>@endif
                            </td>
                            <td>
                                <p class="text-slate-700">{{ $licencia->tipo->nombre }}</p>
                                <p class="text-xs text-slate-400">{{ $licencia->tipo->resumen }}</p>
                            </td>
                            <td class="text-xs text-slate-500">
                                @foreach ($licencia->clases->sortBy('fecha') as $clase)
                                    <p><span class="capitalize">{{ $clase->fecha->translatedFormat('D d/m') }}</span> · {{ $clase->asignacion->materia->nombre }} · <strong>{{ $clase->horas_catedra }} h</strong></p>
                                @endforeach
                            </td>
                            <td>
                                {{-- Sin goce: no lleva documentación --}}
                                @if (isset($colorDoc[$licencia->documentacion]))<span class="sg-badge {{ $colorDoc[$licencia->documentacion] }}">{{ $licencia->documentacion }}</span>@else<span class="text-slate-300">—</span>@endif
                                @if ($licencia->fecha_presentacion)<p class="text-xs text-slate-400 mt-1">el {{ $licencia->fecha_presentacion->format('d/m/Y') }}</p>@endif
                                @if ($licencia->observaciones)<p class="text-xs text-slate-400 mt-1">{{ $licencia->observaciones }}</p>@endif
                            </td>
                            <td>
                                <span class="sg-badge {{ $licencia->descuenta ? 'sg-badge--rojo' : 'sg-badge--verde' }}">{{ $licencia->descuenta ? 'Descuenta ' . $licencia->clases->sum('horas_catedra') . ' h' : 'No descuenta' }}</span>
                            </td>
                            <td><div class="flex justify-center gap-1">
                                @if ($licencia->tipo->con_goce)
                                <button type="button" onclick="document.getElementById('doc-{{ $licencia->id_licencia }}').classList.toggle('hidden')" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91]" title="Actualizar documentación"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                @endif
                                <form method="POST" action="{{ route($panel . '.inasistencias.destroy', $licencia) }}" data-confirm="¿Eliminar la inasistencia de {{ $licencia->profesor->apellido }} del {{ $licencia->desde->format('d/m/Y') }}?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                </form>
                            </div></td>
                        </tr>
                        {{-- Actualizar documentación (ej: trajo el certificado después) --}}
                        <tr id="doc-{{ $licencia->id_licencia }}" class="hidden bg-slate-50">
                            <td colspan="7">
                                <form method="POST" action="{{ route($panel . '.inasistencias.documentacion', $licencia) }}" class="flex flex-wrap items-end gap-3">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="sg-label">Documentación *</label>
                                        <select name="documentacion" required class="sg-input">
                                            @foreach (\App\Models\LicenciaDocente::DOCUMENTACION as $estado)
                                                <option value="{{ $estado }}" @selected($licencia->documentacion === $estado)>{{ $estado }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="sg-label">Fecha en que la presentó</label>
                                        <input type="date" name="fecha_presentacion" max="{{ today()->toDateString() }}" value="{{ $licencia->fecha_presentacion?->toDateString() }}" class="sg-input">
                                    </div>
                                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="calendar">No hay inasistencias registradas en este mes.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ===================== Modal: Registrar inasistencia ===================== --}}
    <div id="modal-inasistencia" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-[28px] shadow-2xl w-full max-w-xl p-7 max-h-[92vh] overflow-y-auto">
            <h2 class="font-display font-extrabold text-[26px] text-[#0B3D91] mb-5">Registrar inasistencia</h2>

            <form method="POST" action="{{ route($panel . '.inasistencias.store') }}" id="form-inasistencia" class="space-y-4">
                @csrf

                {{-- Docente (buscador): al elegirlo aparece el tilde verde y la X para cambiarlo --}}
                <div class="{{ $campo }}">
                    <span id="ina-docente-icono" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-sg.icono nombre="search" class="h-5 w-5" /></span>
                    <span id="ina-docente-ok" class="hidden absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 rounded-full bg-emerald-600 text-white text-xs grid place-items-center">✓</span>
                    <input type="text" id="ina-docente-buscar" autocomplete="off" placeholder="Buscar docente por apellido, nombre o DNI..." oninput="inaBuscarDocente()" onfocus="inaBuscarDocente()" class="{{ $control }}">
                    <button type="button" id="ina-docente-quitar" onclick="inaElegirDocente(null)" class="hidden absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-red-600 text-lg" title="Cambiar docente">✕</button>
                    <input type="hidden" name="id_profesor" id="ina-docente" value="{{ old('id_profesor') }}">
                    <div id="ina-docente-lista" class="hidden absolute z-20 mt-2 w-full max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                </div>

                {{-- Desde / Hasta (hasta vacío = mismo día). Pueden ser fechas futuras: aviso anticipado. --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="{{ $campo }}">
                        <label class="{{ $etiqueta }}">Desde</label>
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"><x-sg.icono nombre="calendar" class="h-5 w-5" /></span>
                        <input type="date" name="desde" id="ina-desde" required max="{{ today()->addYear()->toDateString() }}" value="{{ old('desde') }}" placeholder="Elegir..." onchange="inaTipo(); inaClases()" class="{{ $control }}">
                    </div>
                    <div class="{{ $campo }}">
                        <label class="{{ $etiqueta }}">Hasta</label>
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"><x-sg.icono nombre="calendar" class="h-5 w-5" /></span>
                        <input type="date" name="hasta" id="ina-hasta" max="{{ today()->addYear()->toDateString() }}" value="{{ old('hasta') }}" placeholder="Mismo día" onchange="inaClases()" class="{{ $control }}">
                    </div>
                </div>

                {{-- Tipo de licencia (+ para agregar uno nuevo) --}}
                <div>
                    <div class="flex gap-2">
                        <div class="{{ $campo }} flex-1 min-w-0">
                            <label class="{{ $etiqueta }}">Tipo de licencia</label>
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"><x-sg.icono nombre="clipboard" class="h-5 w-5" /></span>
                            <select name="id_tipo_licencia" id="ina-tipo" required onchange="inaTipo()" class="{{ $control }}">
                                <option value="">Elegir...</option>
                                @foreach ($tipos as $tipo)
                                    <option value="{{ $tipo->id_tipo_licencia }}" @selected((int) old('id_tipo_licencia') === $tipo->id_tipo_licencia)>{{ $tipo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" onclick="inaModal('modal-tipo-licencia', true)" class="shrink-0 w-12 rounded-2xl border border-amber-100 bg-[#FFFDF5] text-[#0B3D91] text-xl hover:border-[#0B3D91]" title="Nuevo tipo de licencia">+</button>
                    </div>
                    <p id="ina-tipo-resumen" class="text-xs text-slate-400 mt-1.5 ml-1">Elegí el tipo: se muestran su artículo, topes y si es con goce de sueldo.</p>
                </div>

                {{-- Documentación (solo licencias con goce: las sin goce no llevan) --}}
                <div id="ina-doc-bloque" class="space-y-4">
                <div class="flex flex-wrap gap-2">
                    @foreach (\App\Models\LicenciaDocente::DOCUMENTACION as $estado)
                        <label class="cursor-pointer">
                            <input type="radio" name="documentacion" value="{{ $estado }}" class="peer sr-only" onchange="inaDocumentacion()" @checked(old('documentacion', 'Pendiente') === $estado)>
                            <span class="block rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition peer-checked:border-transparent peer-checked:bg-gradient-to-r peer-checked:from-[#F5A623] peer-checked:to-[#FFE08A] peer-checked:text-[#5A3A00] peer-checked:shadow">{{ $estado }}</span>
                        </label>
                    @endforeach
                </div>
                <div id="ina-presentacion" class="{{ $campo }}">
                    <label class="{{ $etiqueta }}">Fecha en que la presentó</label>
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none"><x-sg.icono nombre="calendar" class="h-5 w-5" /></span>
                    <input type="date" name="fecha_presentacion" id="ina-fecha-presentacion" max="{{ today()->toDateString() }}" value="{{ old('fecha_presentacion') }}" placeholder="Elegir..." class="{{ $control }}">
                </div>
                </div>

                {{-- Clases que pierde (según el Horario): lo propio de nuestro sistema, basado en horas cátedra --}}
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Clases que pierde</p>
                        <p id="ina-total" class="text-xs font-semibold text-slate-500"></p>
                    </div>
                    <div id="ina-clases" class="space-y-1.5 max-h-52 overflow-y-auto text-sm">
                        <p class="text-xs text-slate-400">Elegí docente y fechas: se listan sus clases de esos días según el Horario, con sus horas cátedra.</p>
                    </div>
                </div>

                {{-- Observaciones --}}
                <div class="{{ $campo }}">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none text-lg">≡</span>
                    <input type="text" name="observaciones" maxlength="255" value="{{ old('observaciones') }}" placeholder="Observaciones (opcional)" class="{{ $control }}">
                </div>

                <div class="flex justify-end items-center gap-6 pt-3">
                    <button type="button" onclick="inaModal('modal-inasistencia', false)" class="text-[#2952CC] font-semibold hover:underline">Cancelar</button>
                    <button type="submit" class="rounded-xl bg-[#2140B8] px-6 py-2.5 font-bold text-white shadow hover:bg-[#0B3D91]">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Mini modal: nuevo tipo de licencia --}}
    <div id="modal-tipo-licencia" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[90] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-md p-6">
            <h3 class="font-display font-extrabold text-xl text-[#0B3D91] mb-4">Nuevo tipo de licencia</h3>
            <div class="space-y-3">
                <div><label class="sg-label">Nombre *</label><input type="text" id="tl-nombre" maxlength="80" class="sg-input w-full" placeholder="Ej: Donación de sangre"></div>
                <div class="grid grid-cols-3 gap-3">
                    <div><label class="sg-label">Artículo</label><input type="text" id="tl-articulo" maxlength="30" class="sg-input w-full" placeholder="Ej: 115"></div>
                    <div><label class="sg-label">Tope/año</label><input type="number" id="tl-anual" min="1" max="365" data-max-digitos="3" class="sg-input w-full" placeholder="Días"></div>
                    <div><label class="sg-label">Máx/mes</label><input type="number" id="tl-mensual" min="1" max="31" data-max-digitos="2" class="sg-input w-full" placeholder="Días"></div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" id="tl-goce" checked> Con goce de sueldo (si no, se descuenta)</label>
                <p id="tl-error" class="hidden text-xs text-red-600"></p>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" onclick="inaModal('modal-tipo-licencia', false)" class="sg-btn sg-btn--borde">Cancelar</button>
                <button type="button" onclick="inaGuardarTipo()" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar tipo</button>
            </div>
        </div>
    </div>

    @php
        $tiposJs = $tipos->mapWithKeys(fn ($t) => [$t->id_tipo_licencia => ['resumen' => $t->resumen, 'con_goce' => $t->con_goce, 'tope_anual' => $t->tope_anual, 'tope_mensual' => $t->tope_mensual]]);
        $rutasJs = ['tipos' => route($panel . '.inasistencias.tipos.store')];
        $hoyJs = today()->toDateString();
    @endphp
    <script>
        const inaDocentes = @json($docentesJs);
        const inaDesignaciones = @json($designaciones);
        const inaTipos = @json($tiposJs);
        const inaUsados = @json($diasUsados);
        const inaOcupadas = new Set(@json($ocupadas));
        const inaRutas = @json($rutasJs);
        const inaHoy = @json($hoyJs);
        const inaDias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const inaId = id => document.getElementById(id);

        function inaModal(id, abrir) {
            inaId(id).classList.toggle('hidden', !abrir);
            inaId(id).classList.toggle('flex', abrir);
        }
        function inaAbrir() {
            inaModal('modal-inasistencia', true);
            inaTipo(); inaClases();
        }

        // ---------- Docente ----------
        function inaBuscarDocente() {
            const texto = inaId('ina-docente-buscar').value.trim().toLowerCase();
            const lista = inaId('ina-docente-lista');
            if (inaId('ina-docente').value) return; // ya elegido: se cambia con la X
            const encontrados = inaDocentes.filter(d => !texto || d.nombre.toLowerCase().includes(texto) || String(d.dni).includes(texto)).slice(0, 30);
            lista.innerHTML = encontrados.length
                ? encontrados.map(d => `<button type="button" data-id="${d.id}" class="block w-full px-4 py-2 text-left text-sm hover:bg-blue-50">${d.nombre} <span class="text-xs text-slate-400">DNI ${d.dni}</span></button>`).join('')
                : '<p class="px-4 py-2 text-sm text-slate-400">No se encontró ningún docente con designaciones.</p>';
            lista.querySelectorAll('button').forEach(b => b.onclick = () => inaElegirDocente(b.dataset.id));
            lista.classList.remove('hidden');
        }
        function inaElegirDocente(id) {
            const d = inaDocentes.find(x => String(x.id) === String(id));
            inaId('ina-docente').value = d ? d.id : '';
            inaId('ina-docente-buscar').value = d ? d.nombre : '';
            inaId('ina-docente-buscar').readOnly = !!d;
            inaId('ina-docente-lista').classList.add('hidden');
            inaId('ina-docente-ok').classList.toggle('hidden', !d);
            inaId('ina-docente-icono').classList.toggle('hidden', !!d);
            inaId('ina-docente-quitar').classList.toggle('hidden', !d);
            if (!d) inaId('ina-docente-buscar').focus();
            inaTipo(); inaClases();
        }
        document.addEventListener('click', e => { if (!e.target.closest('#ina-docente-lista, #ina-docente-buscar')) inaId('ina-docente-lista').classList.add('hidden'); });

        // ---------- Tipo de licencia: resumen + días ya usados por el docente ----------
        function inaTipo() {
            const t = inaTipos[inaId('ina-tipo').value];
            const prof = inaId('ina-docente').value;
            const fecha = inaId('ina-desde').value || inaHoy;
            let texto = t ? t.resumen : 'Elegí el tipo: se muestran su artículo, topes y si es con goce de sueldo.';
            if (t && prof && (t.tope_anual || t.tope_mensual)) {
                const clave = `${prof}-${inaId('ina-tipo').value}-`;
                const anio = inaUsados[clave + fecha.slice(0, 4)] || 0, mes = inaUsados[clave + fecha.slice(0, 7)] || 0;
                texto += ' · ya usó ' + [t.tope_anual ? `${anio} de ${t.tope_anual} en el año` : '', t.tope_mensual ? `${mes} de ${t.tope_mensual} en el mes` : ''].filter(Boolean).join(' y ');
            }
            inaId('ina-tipo-resumen').textContent = texto;
            // Sin goce no lleva documentación: se oculta (y no se envía).
            const sinGoce = t && !t.con_goce;
            inaId('ina-doc-bloque').classList.toggle('hidden', sinGoce);
            document.querySelectorAll('#ina-doc-bloque input').forEach(el => el.disabled = sinGoce);
            inaDocumentacion();
        }

        // ---------- Documentación ----------
        function inaDocumentacion() {
            const presentada = document.querySelector('[name="documentacion"]:checked')?.value === 'Presentada';
            inaId('ina-presentacion').classList.toggle('hidden', !presentada);
            inaId('ina-fecha-presentacion').required = presentada;
            inaResumen();
        }

        // ---------- Clases que pierde: cada día del rango con clase de sus materias, según el Horario ----------
        function inaClases() {
            const prof = inaId('ina-docente').value;
            const desde = inaId('ina-desde').value;
            const hasta = inaId('ina-hasta').value || desde;
            const caja = inaId('ina-clases');
            if (!prof || !desde) { caja.innerHTML = '<p class="text-xs text-slate-400">Elegí docente y fechas: se listan sus clases de esos días según el Horario, con sus horas cátedra.</p>'; return inaResumen(); }
            if (hasta < desde) { caja.innerHTML = '<p class="text-xs text-red-600">"Hasta" no puede ser anterior a "Desde".</p>'; return inaResumen(); }

            const filas = [];
            for (let f = new Date(desde + 'T12:00'), fin = new Date(hasta + 'T12:00'), n = 0; f <= fin && n < 120; f.setDate(f.getDate() + 1), n++) {
                const fecha = `${f.getFullYear()}-${String(f.getMonth() + 1).padStart(2, '0')}-${String(f.getDate()).padStart(2, '0')}`, dia = inaDias[f.getDay()];
                inaDesignaciones.filter(d => String(d.profesor) === prof && d.dias[dia] && (!d.alta || fecha >= d.alta) && (!d.baja || fecha <= d.baja))
                    .forEach(d => filas.push({ d, fecha, dia, horas: d.dias[dia], ocupada: inaOcupadas.has(d.id + '|' + fecha) }));
            }
            caja.innerHTML = filas.length ? filas.map((c, i) => `
                <label class="flex items-center gap-3 rounded-xl px-3 py-2 ${c.ocupada ? 'bg-slate-50 text-slate-400' : 'bg-white hover:bg-blue-50/50'} border border-slate-100">
                    <input type="checkbox" ${c.ocupada ? 'disabled' : 'checked'} onchange="inaFila(this)">
                    <span class="flex-1 min-w-0">
                        <span class="font-semibold text-slate-700">${c.dia.slice(0, 3)} ${c.fecha.slice(8, 10)}/${c.fecha.slice(5, 7)}</span> · ${c.d.materia}
                        <span class="block text-[11px] text-slate-400">${c.ocupada ? 'Ya tiene una inasistencia cargada' : c.d.detalle}</span>
                    </span>
                    <input type="hidden" name="clases[${i}][id_asignacion]" value="${c.d.id}" ${c.ocupada ? 'disabled' : ''}>
                    <input type="hidden" name="clases[${i}][fecha]" value="${c.fecha}" ${c.ocupada ? 'disabled' : ''}>
                    <select name="clases[${i}][horas]" onchange="inaResumen()" class="sg-input !py-1 !w-auto" ${c.ocupada ? 'disabled' : ''}>
                        ${Array.from({ length: c.horas }, (_, h) => c.horas - h).map(h => `<option value="${h}">${h} h${h === c.horas ? ' (toda)' : ''}</option>`).join('')}
                    </select>
                </label>`).join('')
                : '<p class="text-xs text-amber-600">En esas fechas no tiene clases según el Horario (o sus materias no tienen franjas cargadas).</p>';
            inaResumen();
        }
        // Destildar una clase: no se envía.
        function inaFila(check) {
            check.closest('label').querySelectorAll('input[type=hidden], select').forEach(el => el.disabled = !check.checked);
            inaResumen();
        }

        // Total de horas y si se descuenta (sin goce, o con goce con documentación pendiente).
        function inaResumen() {
            const horas = [...document.querySelectorAll('#ina-clases select:not(:disabled)')].reduce((s, el) => s + +el.value, 0);
            const t = inaTipos[inaId('ina-tipo').value];
            const doc = document.querySelector('[name="documentacion"]:checked')?.value;
            const total = inaId('ina-total');
            if (!horas) return total.textContent = '';
            const descuenta = t && (!t.con_goce || doc === 'Pendiente');
            total.innerHTML = `${horas} h cátedra · ` + (!t ? 'elegí el tipo'
                : descuenta ? `<span class="text-red-600">se descuentan${t.con_goce ? ' (hasta que presente la documentación)' : ''}</span>` : '<span class="text-emerald-600">no se descuentan</span>');
        }

        // ---------- Nuevo tipo de licencia ----------
        async function inaGuardarTipo() {
            const error = inaId('tl-error');
            const datos = { nombre: inaId('tl-nombre').value.trim(), articulo: inaId('tl-articulo').value.trim() || null,
                tope_anual: inaId('tl-anual').value || null, tope_mensual: inaId('tl-mensual').value || null, con_goce: inaId('tl-goce').checked ? 1 : 0 };
            const r = await fetch(inaRutas.tipos, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value }, body: JSON.stringify(datos) });
            const res = await r.json();
            if (!r.ok) { error.textContent = Object.values(res.errors || {})[0]?.[0] || 'No se pudo guardar.'; return error.classList.remove('hidden'); }
            inaTipos[res.id] = res;
            inaId('ina-tipo').add(new Option(res.nombre, res.id, true, true));
            ['tl-nombre', 'tl-articulo', 'tl-anual', 'tl-mensual'].forEach(id => inaId(id).value = '');
            error.classList.add('hidden');
            inaModal('modal-tipo-licencia', false);
            inaTipo();
        }

        // Si volvió con error, reabrir el modal con lo cargado.
        @if ($errors->any() && old('id_profesor'))
            document.addEventListener('DOMContentLoaded', () => { inaElegirDocente(@json(old('id_profesor'))); inaAbrir(); });
        @endif
    </script>
@endsection
