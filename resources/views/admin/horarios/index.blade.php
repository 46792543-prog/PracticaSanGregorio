@extends('layouts.admin')

@section('titulo', 'Horarios')

@section('contenido')
    <x-sg.encabezado titulo="Horarios" subtitulo="Cargá las franjas de cada docente y mirá el horario de cursada por docente o por curso" eyebrow="Plantel docente" icono="clock">
        <form method="GET" class="flex items-center gap-2">
            <label class="sg-label !mb-0">Año lectivo</label>
            <select name="id_anio_lectivo" onchange="this.form.submit()" class="sg-input">
                @foreach ($aniosLectivos as $anio)
                    <option value="{{ $anio->id_anio_lectivo }}" @selected($anioLectivoActual?->id_anio_lectivo === $anio->id_anio_lectivo)>{{ $anio->anio }}</option>
                @endforeach
            </select>
        </form>
    </x-sg.encabezado>

    {{-- Formulario H-01: Franja horaria --}}
    @php
        $iconosTurno = ['Mañana' => ['☀️', 'from-[#FFC107] to-[#FFE082]'], 'Tarde' => ['🌅', 'from-[#F87171] to-[#F9A8D4]'], 'Noche' => ['🌙', 'from-[#0B3D91] to-[#4F7FD9]']];
        $diasCortos = ['Lunes' => 'Lun', 'Martes' => 'Mar', 'Miércoles' => 'Mié', 'Jueves' => 'Jue', 'Viernes' => 'Vie', 'Sábado' => 'Sáb', 'Domingo' => 'Dom'];
    @endphp
    <div id="tarjeta-franja" class="sg-card sg-revelar mb-6 border-l-4 border-l-[#FFC107] p-6">
        <p class="text-[11px] font-bold uppercase tracking-[.14em] text-amber-500">Cursada semanal · Formulario H-01</p>
        <h2 id="franja-titulo" class="font-display font-extrabold text-2xl text-[#0B3D91] mb-5">Franja horaria</h2>

        <form method="POST" action="{{ route('admin.horarios.franjas.store') }}" id="form-franja">
            @csrf
            <input type="hidden" name="_method" id="franja-method" value="" disabled>
            <input type="hidden" name="id_anio_lectivo" value="{{ $anioLectivoActual?->id_anio_lectivo }}">

            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 mb-5">
                <span class="flex items-center gap-2 text-sm font-semibold text-slate-600"><x-sg.icono nombre="filter" class="h-4 w-4" /> Docentes y materias de la carrera</span>
                <select id="franja-carrera" onchange="franjaFiltrar()" class="sg-input flex-1 min-w-[200px]">
                    <option value="">Todas las carreras</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}">{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mb-5">
                <div>
                    <label class="sg-label">Docente <span class="text-red-500">*</span></label>
                    <select name="id_profesor" id="franja-docente" required onchange="franjaMaterias()" class="sg-input w-full"></select>
                </div>
                <div>
                    <label class="sg-label">Materia <span class="text-red-500">*</span></label>
                    <select name="id_materia" id="franja-materia" required onchange="franjaTurnos()" class="sg-input w-full"></select>
                </div>
            </div>

            <div class="mb-5">
                <label class="sg-label">Día <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach ($todosLosDias as $dia)
                        <label class="cursor-pointer">
                            <input type="radio" name="dia_semana" value="{{ $dia }}" class="peer sr-only" onchange="franjaRevisar()" @checked(old('dia_semana') === $dia)>
                            <span class="grid h-12 w-20 place-items-center rounded-2xl border border-slate-200 bg-white font-semibold text-slate-600 transition peer-checked:border-[#0B3D91] peer-checked:bg-[#0B3D91] peer-checked:text-white hover:border-[#4FC3F7]">{{ $diasCortos[$dia] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-5">
                <label class="sg-label">Horario <span class="text-red-500">*</span> <span class="font-normal normal-case text-slate-400">— inicio y fin (HH:mm)</span></label>
                <div class="flex flex-wrap items-center gap-4">
                    @foreach (['desde' => 'Desde', 'hasta' => 'Hasta'] as $campo => $titulo)
                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-center">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $titulo }}</p>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="franjaCorrer('{{ $campo }}', -5)" class="text-xl font-bold text-[#1565C0] hover:text-[#0B3D91]" title="5 minutos menos">‹</button>
                                <input type="text" name="hora_{{ $campo }}" id="franja-{{ $campo }}" value="{{ old('hora_' . $campo, $campo === 'desde' ? '18:00' : '18:40') }}" maxlength="5" required
                                       inputmode="numeric" oninput="franjaFormatearHora(this)" class="w-24 bg-transparent text-center font-display text-3xl font-extrabold text-[#0B3D91] outline-none">
                                <button type="button" onclick="franjaCorrer('{{ $campo }}', 5)" class="text-xl font-bold text-[#1565C0] hover:text-[#0B3D91]" title="5 minutos más">›</button>
                            </div>
                        </div>
                        @if ($campo === 'desde')<span class="text-2xl text-amber-500">→</span>@endif
                    @endforeach
                    <span id="franja-duracion" class="font-semibold text-slate-700">— min</span>
                </div>
                <div class="mt-3 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-slate-400">Rápido:</span>
                    @foreach ([1, 2, 3, 4] as $n)
                        <button type="button" onclick="franjaRapido({{ $n * \App\Models\Materia::MINUTOS_HORA_CATEDRA }})" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600 hover:border-[#4FC3F7]">+{{ $n * \App\Models\Materia::MINUTOS_HORA_CATEDRA }} min · {{ $n }} mód.</button>
                    @endforeach
                </div>
            </div>

            <div class="mb-5">
                <label class="sg-label">Turno <span class="text-red-500">*</span></label>
                <div id="franja-turnos" class="flex flex-wrap gap-3">
                    @foreach ($turnosTodos as $turno)
                        @php [$emoji, $grad] = $iconosTurno[$turno->nombre_turno] ?? ['🕒', 'from-slate-400 to-slate-300']; @endphp
                        <label class="cursor-pointer has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-40">
                            <input type="radio" name="id_turno_cursada" value="{{ $turno->id_turno_cursada }}" class="peer sr-only" onchange="franjaRevisar()">
                            <span class="flex w-44 items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-3 transition peer-checked:border-[#0B3D91] peer-checked:ring-2 peer-checked:ring-[#0B3D91]/20">
                                <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br {{ $grad }} text-xl">{{ $emoji }}</span>
                                @php $rangoTurno = \App\Http\Controllers\Admin\HorarioCarreraController::RANGOS_TURNO[$turno->nombre_turno] ?? null; @endphp
                                <span class="leading-tight">
                                    <span class="block font-semibold text-slate-700">{{ $turno->nombre_turno }}</span>
                                    @if ($rangoTurno)<span class="block text-[11px] text-slate-400">{{ $rangoTurno[0] }} a {{ $rangoTurno[1] === '23:59' ? '24:00' : $rangoTurno[1] }}</span>@endif
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Superposición: se revisa en vivo (el servidor lo vuelve a controlar) --}}
            <div id="franja-superposicion" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 mb-5">
                <p class="flex items-center gap-2 font-semibold text-slate-700"><x-sg.icono nombre="info" class="h-4 w-4" /> Superposición</p>
                <div id="franja-superposicion-texto" class="text-sm text-slate-500 mt-1">Elegí docente, día y horario para revisar superposiciones.</div>
            </div>

            <p id="franja-editando" class="hidden sg-aviso sg-aviso--alerta text-xs mb-4"><span>Editando una franja ya cargada: al guardar se actualiza (no se crea otra).</span></p>
            <div class="flex justify-center gap-3">
                <button type="button" onclick="franjaCancelarEdicion()" class="sg-btn sg-btn--borde min-w-[140px] justify-center">Limpiar</button>
                <button type="submit" id="franja-guardar" class="sg-btn sg-btn--primario min-w-[180px] justify-center"><x-sg.icono nombre="check" /> <span id="franja-guardar-texto">Guardar franja</span></button>
            </div>
        </form>
    </div>

    {{-- Horario de cursada: por docente o por curso, en grilla semanal o tabla --}}
    <div class="sg-card sg-revelar p-6">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <h2 class="font-display font-extrabold text-2xl text-[#0B3D91]">Horario de cursada</h2>
            <span id="hc-cantidad" class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#1565C0]"></span>
        </div>

        <div class="flex flex-wrap items-center gap-3 mb-4">
            <div class="inline-flex rounded-2xl bg-blue-50 p-1">
                <button type="button" data-modo="docente" onclick="hcModo('docente')" class="hc-tab rounded-xl px-4 py-2 text-sm font-semibold">👤 Docente</button>
                <button type="button" data-modo="curso" onclick="hcModo('curso')" class="hc-tab rounded-xl px-4 py-2 text-sm font-semibold">👥 Curso</button>
            </div>
            <div id="hc-buscador" class="relative flex-1 min-w-[240px] max-w-md">
                <div class="sg-campo-icono">
                    <x-sg.icono nombre="search" />
                    <input type="text" id="hc-buscar" autocomplete="off" placeholder="Buscar docente por apellido, nombre..." oninput="hcSugerir()" onfocus="hcSugerir()" class="sg-input w-full">
                </div>
                <div id="hc-sugerencias" class="hidden absolute z-20 mt-1 w-full max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
            </div>
            <div class="inline-flex rounded-2xl bg-blue-50 p-1 ml-auto">
                <button type="button" data-vista="grilla" onclick="hcVista('grilla')" class="hc-tab rounded-xl px-4 py-2 text-sm font-semibold">▦ Grilla</button>
                <button type="button" data-vista="tabla" onclick="hcVista('tabla')" class="hc-tab rounded-xl px-4 py-2 text-sm font-semibold">☰ Tabla completa</button>
            </div>
            <a id="hc-pdf" href="#" class="hidden sg-btn sg-btn--borde"><x-sg.icono nombre="download" /> PDF</a>
        </div>

        <div id="hc-cursos" class="hidden flex flex-wrap gap-2 mb-4"></div>
        <div id="hc-resumen" class="hidden"></div>

        <div class="flex flex-col lg:flex-row gap-4">
            <div id="hc-contenido" class="flex-1 min-w-0"></div>
            <aside id="hc-lateral" class="hidden w-full lg:w-72 shrink-0"></aside>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-100 bg-amber-50/50 px-4 py-3 text-sm">
            <span id="hc-total" class="font-semibold text-slate-600"></span>
            <span class="flex flex-wrap gap-4 text-xs text-slate-600">
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-full bg-gradient-to-r from-[#F59E0B] to-[#FCD34D]"></span>Mañana</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-full bg-gradient-to-r from-[#F43F5E] to-[#F9A8D4]"></span>Tarde</span>
                <span class="flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-full bg-gradient-to-r from-[#1E40AF] to-[#3B82F6]"></span>Noche</span>
            </span>
        </div>
    </div>

    <script>
        const porId = id => document.getElementById(id);
        const hcMin = h => +h.slice(0, 2) * 60 + +h.slice(3, 5);
        // ===================== Franja horaria =====================
        const franjaDesignaciones = @json($franjaDesignaciones);
        const franjaClases = @json($franjaClases);
        const franjaHC = {{ \App\Models\Materia::MINUTOS_HORA_CATEDRA }};
        const franjaRangos = @json(\App\Http\Controllers\Admin\HorarioCarreraController::RANGOS_TURNO); // horario de cada turno
        const fId = id => document.getElementById(id);
        const aMin = h => { const m = /^(\d{1,2}):(\d{2})$/.exec(h || ''); return m && +m[1] < 24 && +m[2] < 60 ? +m[1] * 60 + +m[2] : null; };
        const aHora = m => String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');

        // Carrera → docentes con designación en esa carrera.
        function franjaFiltrar() {
            const carrera = fId('franja-carrera').value;
            const lista = franjaDesignaciones.filter(d => !carrera || String(d.id_carrera) === carrera);
            const docentes = [...new Map(lista.map(d => [d.id_profesor, d.docente])).entries()].sort((a, b) => a[1].localeCompare(b[1]));
            const sel = fId('franja-docente'), antes = sel.value;
            sel.innerHTML = '<option value="">Elegí un docente...</option>' + docentes.map(([id, n]) => `<option value="${id}">${n}</option>`).join('');
            sel.value = docentes.some(([id]) => String(id) === antes) ? antes : '';
            franjaMaterias();
        }
        // Docente → sus materias.
        function franjaMaterias() {
            const carrera = fId('franja-carrera').value, docente = fId('franja-docente').value;
            const lista = franjaDesignaciones.filter(d => String(d.id_profesor) === docente && (!carrera || String(d.id_carrera) === carrera));
            const materias = [...new Map(lista.map(d => [d.id_materia, d.materia])).entries()];
            const sel = fId('franja-materia'), antes = sel.value;
            sel.innerHTML = '<option value="">' + (docente ? 'Elegí una materia...' : 'Primero elegí el docente') + '</option>' + materias.map(([id, n]) => `<option value="${id}">${n}</option>`).join('');
            sel.value = materias.some(([id]) => String(id) === antes) ? antes : '';
            franjaTurnos();
        }
        // Materia → solo los turnos en que el docente la tiene designada.
        function franjaTurnos() {
            const docente = fId('franja-docente').value, materia = fId('franja-materia').value;
            const turnos = franjaDesignaciones.filter(d => String(d.id_profesor) === docente && String(d.id_materia) === materia).map(d => String(d.id_turno));
            document.querySelectorAll('#franja-turnos input').forEach(r => { r.disabled = !turnos.includes(r.value); if (r.disabled) r.checked = false; });
            if (turnos.length === 1) document.querySelector(`#franja-turnos input[value="${turnos[0]}"]`).checked = true;
            franjaRevisar();
        }
        // Mientras se escribe: solo números, y después de los dos primeros se pone ":" solo (1800 → 18:00).
        function franjaFormatearHora(input) {
            const numeros = input.value.replace(/\D/g, '').slice(0, 4);
            input.value = numeros.length > 2 ? numeros.slice(0, 2) + ':' + numeros.slice(2) : numeros;
            franjaRevisar();
        }
        function franjaCorrer(campo, minutos) {
            const input = fId('franja-' + campo), m = aMin(input.value) ?? 18 * 60;
            input.value = aHora(Math.min(Math.max(m + minutos, 0), 23 * 60 + 55));
            franjaRevisar();
        }
        function franjaRapido(minutos) {
            const desde = aMin(fId('franja-desde').value);
            if (desde === null) return;
            fId('franja-hasta').value = aHora(Math.min(desde + minutos, 23 * 60 + 59));
            franjaRevisar();
        }

        // Revisa en vivo: formato, duración, choques del docente y del curso, y horas.
        function franjaRevisar() {
            const desde = aMin(fId('franja-desde').value), hasta = aMin(fId('franja-hasta').value);
            const dia = document.querySelector('[name="dia_semana"]:checked')?.value;
            const turno = document.querySelector('[name="id_turno_cursada"]:checked')?.value;
            const docente = fId('franja-docente').value, materia = fId('franja-materia').value;
            const dur = desde !== null && hasta !== null ? hasta - desde : null;
            fId('franja-duracion').textContent = dur > 0 ? `${dur} min` + (dur % franjaHC === 0 ? ` · ${dur / franjaHC} h cátedra` : '') : '— min';

            const problemas = [];
            if (desde === null || hasta === null) problemas.push('Escribí las horas con formato HH:mm (ej. 18:00).');
            else if (dur <= 0) problemas.push('"Hasta" tiene que ser después de "Desde".');
            else if (dur % franjaHC !== 0) problemas.push(`Dura ${dur} min: tiene que ser múltiplo de ${franjaHC} (40, 80, 120…).`);

            const d = franjaDesignaciones.find(x => String(x.id_profesor) === docente && String(x.id_materia) === materia && String(x.id_turno) === turno);
            const caja = fId('franja-superposicion'), texto = fId('franja-superposicion-texto');
            fId('franja-guardar').disabled = false;
            if (!d || !dia || dur === null || dur <= 0) {
                caja.className = 'rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 mb-5';
                texto.innerHTML = problemas.length && desde !== null ? problemas.join('<br>') : 'Elegí docente, día y horario para revisar superposiciones.';
                return;
            }
            const choca = c => c.dia === dia && aMin(c.desde) < hasta && aMin(c.hasta) > desde && (!franjaEditando || c.id !== franjaEditando.id);
            for (const c of franjaClases.filter(choca)) {
                const otra = franjaDesignaciones.find(x => x.id === c.id_asignacion);
                if (!otra) continue;
                if (otra.id_profesor === d.id_profesor) problemas.push(`${d.docente} ya da «${otra.materia}» el ${dia} de ${c.desde} a ${c.hasta}.`);
                // Mismo curso: tampoco dos docentes de la misma práctica a la misma hora.
                else if (otra.id_carrera === d.id_carrera && otra.id_anio_cursada === d.id_anio_cursada && otra.id_turno === d.id_turno)
                    problemas.push(`Ese año ya tiene «${otra.materia}» con ${otra.docente} el ${dia} de ${c.desde} a ${c.hasta}`
                        + (otra.id_materia === d.id_materia ? ' (la misma práctica: cada docente necesita su propio horario).' : '.'));
            }
            // Dentro del horario de su turno (ej. Mañana 07:00 a 13:00).
            const rango = franjaRangos[d.turno];
            if (rango && (desde < aMin(rango[0]) || hasta > aMin(rango[1])))
                problemas.push(`El turno ${d.turno} es de ${rango[0]} a ${rango[1] === '23:59' ? '24:00' : rango[1]}: la franja queda fuera de ese horario.`);
            // Al editar, los minutos de la propia franja no cuentan como ya cargados.
            const propios = franjaEditando && franjaEditando.id_asignacion === d.id ? hcMin(franjaEditando.hasta) - hcMin(franjaEditando.desde) : 0;
            const cargados = d.cargados - propios;
            if (cargados + dur > d.tope) problemas.push(`Se pasa de las horas: tiene ${d.tope / franjaHC} h cátedra y ya cargó ${cargados} de ${d.tope} min.`);

            const ok = problemas.length === 0;
            caja.className = 'rounded-2xl border px-4 py-3 mb-5 ' + (ok ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50');
            texto.innerHTML = ok ? `✓ Sin superposiciones. Quedarían ${cargados + dur} de ${d.tope} min cargados.` : problemas.map(p => '✕ ' + p).join('<br>');
            texto.className = 'text-sm mt-1 ' + (ok ? 'text-emerald-700' : 'text-red-700');
            // Con superposición (u otro problema) no se puede guardar; igual lo vuelve a validar el servidor.
            fId('franja-guardar').disabled = !ok;
            fId('franja-guardar').title = ok ? '' : 'Corregí la superposición para poder guardar';
        }
        // ---------- Editar una franja (lápiz del Horario de cursada) ----------
        let franjaEditando = null;
        const rutaFranjaStore = @json(route('admin.horarios.franjas.store'));
        const rutaFranjaBase = @json(url('/admin/horarios/franjas'));
        function franjaEditar(f) {
            franjaEditando = f;
            fId('franja-carrera').value = '';
            franjaFiltrar();
            fId('franja-docente').value = f.id_profesor; franjaMaterias();
            fId('franja-materia').value = f.id_materia; franjaTurnos();
            const turno = document.querySelector(`#franja-turnos input[value="${f.id_turno}"]`); if (turno) turno.checked = true;
            const dia = document.querySelector(`[name="dia_semana"][value="${f.dia}"]`); if (dia) dia.checked = true;
            fId('franja-desde').value = f.desde; fId('franja-hasta').value = f.hasta;
            fId('form-franja').action = rutaFranjaBase + '/' + f.id;
            fId('franja-method').disabled = false; fId('franja-method').value = 'PUT';
            fId('franja-titulo').textContent = 'Editar franja';
            fId('franja-guardar-texto').textContent = 'Guardar cambios';
            fId('franja-editando').classList.remove('hidden');
            franjaRevisar();
            fId('tarjeta-franja').scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        function franjaCancelarEdicion() {
            franjaEditando = null;
            fId('form-franja').reset();
            fId('form-franja').action = rutaFranjaStore;
            fId('franja-method').disabled = true; fId('franja-method').value = '';
            fId('franja-titulo').textContent = 'Franja horaria';
            fId('franja-guardar-texto').textContent = 'Guardar franja';
            fId('franja-editando').classList.add('hidden');
            franjaInicial();
        }

        function franjaInicial() {
            franjaFiltrar();
            @if (old('id_profesor'))
                fId('franja-docente').value = @json(old('id_profesor')); franjaMaterias();
                fId('franja-materia').value = @json(old('id_materia')); franjaTurnos();
                const turnoViejo = document.querySelector(`#franja-turnos input[value="${@json(old('id_turno_cursada'))}"]`); if (turnoViejo) turnoViejo.checked = true;
                franjaRevisar();
            @endif
        }
        franjaInicial();


        // ===================== Horario de cursada =====================
        const hcFranjas = @json($franjas);
        const hcDesignaciones = franjaDesignaciones;
        const hcDias = @json($todosLosDias);
        const hcCortos = { Lunes: 'Lun', Martes: 'Mar', 'Miércoles': 'Mié', Jueves: 'Jue', Viernes: 'Vie', 'Sábado': 'Sáb', Domingo: 'Dom' };
        const hcHoy = hcDias[(new Date().getDay() + 6) % 7];
        const hcToken = @json(csrf_token());
        const hcRutaBorrar = @json(url('/admin/horarios/clases'));
        const hcRutaPdf = @json(route('admin.horarios.pdf'));
        const hcAnioLectivo = @json($anioLectivoActual?->id_anio_lectivo);
        const hcColor = { 'Mañana': 'from-[#F59E0B] to-[#FCD34D] text-[#5B3A00]', 'Tarde': 'from-[#F43F5E] to-[#F9A8D4] text-white', 'Noche': 'from-[#1E40AF] to-[#3B82F6] text-white' };
        const hcIcono = { 'Mañana': '☀️', 'Tarde': '🌅', 'Noche': '🌙' };
        const hcEsc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        let hc = { modo: 'docente', vista: 'grilla', docente: null, curso: null };

        const hcCursoClave = x => `${x.id_carrera}-${x.id_anio_cursada}-${x.id_turno}`;
        const hcCursos = [...new Map(hcDesignaciones.map(d => [hcCursoClave(d), d])).values()]
            .sort((a, b) => (a.carrera + a.anio_cursada + a.turno).localeCompare(b.carrera + b.anio_cursada + b.turno));

        function hcModo(m) { hc.modo = m; hcPintar(); }
        function hcVista(v) { hc.vista = v; hcPintar(); }

        // Docente: buscador por apellido o nombre.
        function hcSugerir() {
            const t = porId('hc-buscar').value.trim().toLowerCase(), caja = porId('hc-sugerencias');
            const docentes = [...new Map(hcDesignaciones.map(d => [d.id_profesor, d.docente])).entries()]
                .filter(([, n]) => !t || n.toLowerCase().includes(t)).sort((a, b) => a[1].localeCompare(b[1])).slice(0, 30);
            caja.innerHTML = docentes.length ? docentes.map(([id, n]) => `<button type="button" data-id="${id}" class="block w-full px-4 py-2 text-left text-sm hover:bg-blue-50">${hcEsc(n)}</button>`).join('')
                : '<p class="px-4 py-2 text-sm text-slate-400">No se encontró ningún docente.</p>';
            caja.querySelectorAll('button').forEach(b => b.onclick = () => { hc.docente = +b.dataset.id; porId('hc-buscar').value = b.textContent; caja.classList.add('hidden'); hcPintar(); });
            caja.classList.remove('hidden');
        }
        document.addEventListener('click', e => { if (!e.target.closest('#hc-buscador')) porId('hc-sugerencias').classList.add('hidden'); });

        function hcLimpiar() { hc.docente = null; hc.curso = null; porId('hc-buscar').value = ''; hcPintar(); }

        // Franjas que se ven según el modo y lo elegido.
        function hcVisibles() {
            if (hc.modo === 'docente') return hc.docente ? hcFranjas.filter(f => f.id_profesor === hc.docente) : [];
            return hc.curso ? hcFranjas.filter(f => hcCursoClave(f) === hc.curso) : [];
        }

        function hcEditar(f) {
            return `<button type="button" title="Editar franja" onclick='franjaEditar(${JSON.stringify(f).replace(/'/g, "&#39;")})' class="grid h-5 w-5 place-items-center rounded-full bg-white/25 text-xs hover:bg-white hover:text-[#0B3D91]">✏️</button>`;
        }

        function hcBorrar(f) {
            const msg = `¿Quitar ${f.materia} del ${f.dia} de ${f.desde} a ${f.hasta}?`;
            return `<form method="POST" action="${hcRutaBorrar}/${f.id}" data-confirm="${hcEsc(msg)}" class="inline">
                <input type="hidden" name="_token" value="${hcToken}"><input type="hidden" name="_method" value="DELETE">
                <button type="submit" title="Quitar" class="grid h-5 w-5 place-items-center rounded-full bg-white/25 text-xs hover:bg-red-500 hover:text-white">✕</button></form>`;
        }

        function hcGrilla(franjas, vacio) {
            const ini = franjas.length ? Math.floor(Math.min(...franjas.map(f => hcMin(f.desde))) / 60) : 7;
            const fin = franjas.length ? Math.ceil(Math.max(...franjas.map(f => hcMin(f.hasta))) / 60) : 13;
            const alto = 72, horas = Array.from({ length: Math.max(fin - ini, 1) }, (_, i) => ini + i);
            const cab = hcDias.map(d => `<div class="py-3 text-center text-sm font-semibold ${d === hcHoy ? 'bg-amber-100 text-[#7A5400]' : 'text-[#0B3D91]'}">${hcCortos[d]}${d === hcHoy ? ' <span class="rounded-full bg-amber-300 px-1.5 text-[10px] font-bold">HOY</span>' : ''}</div>`).join('');
            const columnas = hcDias.map(d => `<div class="relative border-l border-slate-100 ${d === hcHoy ? 'bg-amber-50/60' : ''}" style="height:${horas.length * alto}px">
                ${horas.map((_, i) => `<div class="absolute inset-x-0 border-t border-slate-100" style="top:${i * alto}px"></div>`).join('')}
                ${franjas.filter(f => f.dia === d).map(f => {
                    const top = (hcMin(f.desde) - ini * 60) / 60 * alto, h = Math.max((hcMin(f.hasta) - hcMin(f.desde)) / 60 * alto - 4, 28);
                    const linea = hc.modo === 'docente' ? `${f.carrera} · ${f.anio_cursada}` : f.apellido;
                    return `<div class="absolute inset-x-1 overflow-hidden rounded-xl bg-gradient-to-br ${hcColor[f.turno] ?? 'from-slate-500 to-slate-400 text-white'} p-2 text-xs shadow-md" style="top:${top + 2}px;height:${h}px" title="${hcEsc(f.materia + ' — ' + f.docente)}">
                        <div class="flex items-start justify-between gap-1"><span class="rounded-md bg-black/15 px-1.5 py-0.5 font-bold">${f.desde} a ${f.hasta}</span><span class="flex gap-1">${hcEditar(f)}${hcBorrar(f)}</span></div>
                        <p class="mt-1 font-bold leading-tight">${hcEsc(f.materia)}</p><p class="opacity-80 leading-tight">${hcEsc(linea)}</p></div>`;
                }).join('')}</div>`).join('');
            return `<div class="overflow-x-auto rounded-2xl border border-blue-100"><div class="min-w-[760px]">
                <div class="grid border-b border-blue-100 bg-blue-50/70" style="grid-template-columns:64px repeat(${hcDias.length},1fr)"><div></div>${cab}</div>
                <div class="relative grid" style="grid-template-columns:64px repeat(${hcDias.length},1fr)">
                    <div>${horas.map(hh => `<div class="px-2 pt-1 text-xs font-bold text-slate-400" style="height:${alto}px">${String(hh).padStart(2, '0')}:00</div>`).join('')}</div>${columnas}
                    ${franjas.length ? '' : `<div class="absolute inset-0 grid place-items-center"><span class="rounded-xl bg-white/90 px-5 py-3 font-semibold text-slate-600">${vacio}</span></div>`}
                </div></div></div>`;
        }

        function hcTabla(franjas) {
            if (!franjas.length) return '<p class="rounded-2xl border border-slate-200 p-6 text-center text-slate-400">No hay franjas para mostrar.</p>';
            return `<div class="overflow-x-auto"><table class="sg-tabla"><thead><tr><th>Día</th><th>Horario</th><th>Materia</th><th>Docente</th><th>Curso</th><th></th></tr></thead><tbody class="sg-filas">
                ${franjas.map(f => `<tr><td class="font-semibold">${f.dia}</td><td class="whitespace-nowrap">${f.desde} a ${f.hasta}</td><td>${hcEsc(f.materia)}</td><td class="text-slate-500">${hcEsc(f.docente)}</td>
                <td class="text-xs text-slate-500">${hcEsc(f.carrera)} · ${hcEsc(f.anio_cursada)} · ${hcIcono[f.turno] ?? ''} ${hcEsc(f.turno)}</td><td class="text-slate-400"><span class="flex gap-1">${hcEditar(f)}${hcBorrar(f)}</span></td></tr>`).join('')}</tbody></table></div>`;
        }

        // Curso: resumen arriba y "Horas por materia" al costado.
        function hcResumenCurso(franjas) {
            const c = hcCursos.find(x => hcCursoClave(x) === hc.curso);
            const desigs = hcDesignaciones.filter(d => hcCursoClave(d) === hc.curso);
            const materias = new Set(desigs.map(d => d.id_materia)).size, dias = new Set(franjas.map(f => f.dia)).size;
            const dato = (t, v) => `<div class="rounded-xl bg-white/10 px-4 py-2 text-center"><p class="text-[10px] font-bold uppercase tracking-wider text-blue-100">${t}</p><p class="font-display text-xl font-bold text-[#FFD54F]">${v}</p></div>`;
            porId('hc-resumen').innerHTML = `<div class="mb-4 flex flex-wrap items-center gap-4 rounded-2xl bg-gradient-to-r from-[#0B3D91] to-[#2563EB] px-5 py-4 text-white">
                <span class="grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br from-[#FFC107] to-[#FFE082] text-center text-sm font-bold text-[#5B3A00]">${hcEsc(c.anio_cursada)}</span>
                <div class="mr-auto"><p class="font-display text-lg font-bold">${hcEsc(c.carrera)}</p><p class="text-sm text-blue-100">${hcEsc(c.anio_cursada)} · Turno ${hcEsc(c.turno)}</p></div>
                ${dato('Materias', materias)}${dato('Clases / semana', franjas.length)}${dato('Días con clase', dias)}
                <button type="button" onclick="hcLimpiar()" class="rounded-xl border border-white/30 px-3 py-2 text-sm font-semibold hover:bg-white/10">Limpiar búsqueda ✕</button></div>`;
            const modulos = desigs.reduce((t, d) => t + d.cargados / franjaHC, 0);
            const reloj = desigs.reduce((t, d) => t + d.cargados, 0) / 60;
            porId('hc-lateral').innerHTML = `<div class="rounded-2xl border border-slate-200 bg-white p-4">
                <p class="mb-3 text-sm font-semibold text-slate-700">Horas por materia</p>
                ${desigs.map(d => { const pct = d.tope ? Math.min(d.cargados / d.tope * 100, 100) : 0; return `<div class="mb-3 border-b border-slate-100 pb-3">
                    <div class="flex items-baseline justify-between gap-2"><span class="text-sm font-semibold text-slate-700">${hcEsc(d.materia)}</span><span class="font-display font-bold text-[#0B3D91]">${d.cargados / franjaHC} de ${d.horas} mód.</span></div>
                    <p class="text-xs text-slate-400">${hcEsc(d.docente)}</p>
                    <div class="mt-1 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full ${pct >= 100 ? 'bg-emerald-500' : 'bg-[#2563EB]'}" style="width:${pct}%"></div></div></div>`; }).join('') || '<p class="text-sm text-slate-400">Sin designaciones.</p>'}
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-3"><p class="text-[10px] font-bold uppercase tracking-wider text-amber-700">Total semanal</p>
                <p class="font-display text-3xl font-extrabold text-[#0B3D91]">${modulos} <span class="text-sm font-semibold text-slate-600">módulos</span></p>
                <p class="text-xs text-slate-500">${Math.round(reloj * 10) / 10} h reloj · ${franjas.length} clases</p></div></div>`;
        }

        function hcPintar() {
            document.querySelectorAll('[data-modo]').forEach(b => b.className = 'hc-tab rounded-xl px-4 py-2 text-sm font-semibold ' + (b.dataset.modo === hc.modo ? 'bg-[#0B3D91] text-white shadow' : 'text-slate-600'));
            document.querySelectorAll('[data-vista]').forEach(b => b.className = 'hc-tab rounded-xl px-4 py-2 text-sm font-semibold ' + (b.dataset.vista === hc.vista ? 'bg-[#0B3D91] text-white shadow' : 'text-slate-600'));
            porId('hc-buscador').classList.toggle('hidden', hc.modo !== 'docente');
            const chips = porId('hc-cursos');
            chips.classList.toggle('hidden', hc.modo !== 'curso');
            chips.innerHTML = hcCursos.map(c => { const k = hcCursoClave(c), sel = k === hc.curso;
                return `<button type="button" data-curso="${k}" class="rounded-full border px-4 py-1.5 text-sm font-semibold ${sel ? 'border-transparent bg-gradient-to-r from-[#F59E0B] to-[#FCD34D] text-[#5B3A00] shadow' : 'border-slate-200 bg-white text-slate-600 hover:border-[#4FC3F7]'}">${hcIcono[c.turno] ?? ''} ${hcEsc(c.carrera)} · ${hcEsc(c.anio_cursada)} · ${hcEsc(c.turno)}</button>`; }).join('')
                || '<p class="text-sm text-slate-400">No hay cursos con designaciones este año.</p>';
            chips.querySelectorAll('[data-curso]').forEach(b => b.onclick = () => { hc.curso = b.dataset.curso; hcPintar(); });

            const franjas = hcVisibles();
            const verCurso = hc.modo === 'curso' && hc.curso;
            porId('hc-resumen').classList.toggle('hidden', !verCurso);
            porId('hc-lateral').classList.toggle('hidden', !verCurso);
            if (verCurso) hcResumenCurso(franjas);

            const vacio = hc.modo === 'docente' ? 'Buscá un docente para ver su semana.' : 'Elegí un curso para ver su semana.';
            const mostrar = hc.vista === 'tabla' && !(hc.docente || hc.curso) ? hcFranjas : franjas;
            porId('hc-contenido').innerHTML = hc.vista === 'grilla' ? hcGrilla(franjas, vacio) : hcTabla(mostrar);
            porId('hc-cantidad').textContent = `${(hc.docente || hc.curso) ? franjas.length : hcFranjas.length} franjas`;
            porId('hc-total').textContent = `${hcFranjas.length} franjas cargadas en total`;

            const pdf = porId('hc-pdf'), c = verCurso ? hcCursos.find(x => hcCursoClave(x) === hc.curso) : null;
            const hayPdf = franjas.length && (hc.modo === 'docente' ? hc.docente : c);
            pdf.classList.toggle('hidden', !hayPdf);
            if (hayPdf) pdf.href = hcRutaPdf + '?' + new URLSearchParams(hc.modo === 'docente'
                ? { profesor: hc.docente, id_anio_lectivo: hcAnioLectivo } : { carrera: c.id_carrera, anio: c.id_anio_cursada, turno: c.id_turno, id_anio_lectivo: hcAnioLectivo });
        }

        // Al volver de cargar una franja, se abre directo ese curso.
        @php $cursoInicial = request()->filled(['id_carrera', 'id_anio_cursada', 'id_turno_cursada']) ? request('id_carrera') . '-' . request('id_anio_cursada') . '-' . request('id_turno_cursada') : null; @endphp
        @if ($cursoInicial)
            hc.modo = 'curso'; hc.curso = @json($cursoInicial);
        @endif
        hcPintar();
    </script>
@endsection
