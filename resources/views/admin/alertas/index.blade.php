{{-- La misma pantalla en el panel de Secretaría y en el de Dirección --}}
@php $panel = request()->routeIs('director.*') ? 'director' : 'admin'; @endphp
@extends($panel === 'director' ? 'layouts.director' : 'layouts.admin')

@section('titulo', 'Central de Alertas')
@section('subtitulo', 'Cuotas vencidas y bajas recientes que requieren atención')

@section('contenido')
    @php
        $estilos = [
            'cuota_vencida' => ['icono' => 'banknotes', 'tono' => 'rojo', 'etiqueta' => 'Cuota vencida'],
            'baja_alumno' => ['icono' => 'user-minus', 'tono' => 'amarillo', 'etiqueta' => 'Baja de alumno'],
            'baja_profesor' => ['icono' => 'briefcase', 'tono' => 'amarillo', 'etiqueta' => 'Baja de profesor'],
        ];
        $hayCuotas = collect($alertas->items())->contains('tipo', 'cuota_vencida');
    @endphp

    <x-sg.encabezado titulo="Central de Alertas" subtitulo="Cuotas vencidas y bajas recientes que requieren atención" eyebrow="Seguimiento" icono="bell-alert">
        <span class="sg-badge sg-badge--rojo text-sm px-4 py-2">{{ $alertas->total() }} {{ $alertas->total() === 1 ? 'alerta activa' : 'alertas activas' }}</span>
    </x-sg.encabezado>

    <x-sg.tarjeta :cuerpo="false">
        <form method="GET" class="flex flex-wrap items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60 rounded-t-[1.25rem]">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2"><x-sg.icono nombre="filter" class="h-4 w-4" /> Filtrar</span>
            <select name="tipo" onchange="this.form.submit()" class="sg-input w-auto min-w-[220px] py-2">
                <option value="">Todos los tipos</option>
                @foreach ($tipos as $clave => $label)
                    <option value="{{ $clave }}" @selected($tipo === $clave)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="sg-campo-icono">
                <x-sg.icono nombre="search" />
                <input type="text" name="q" value="{{ $busqueda }}" data-busqueda placeholder="Nombre, apellido o DNI..." class="sg-input w-auto min-w-[240px] py-2">
            </div>
            <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico"><x-sg.icono nombre="search" /> Buscar</button>
            @if ($tipo || $busqueda)
                <a href="{{ route($panel . '.alertas.index') }}" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif

            {{-- Selección de varios alumnos: se prepara un correo individual para cada uno --}}
            @if ($hayCuotas)
                <div class="ml-auto flex items-center gap-3">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" id="correo-todos" onchange="document.querySelectorAll('.correo-elegir').forEach(c => c.checked = this.checked); correoContar()"> Seleccionar todos
                    </label>
                    <button type="button" id="correo-varios" onclick="correoPrepararVarios()" disabled class="sg-btn sg-btn--azul sg-btn--chico disabled:opacity-40">
                        <x-sg.icono nombre="mail" /> Preparar correos (<span id="correo-cantidad">0</span>)
                    </button>
                </div>
            @endif
        </form>

        <div class="sg-lista sg-filas">
            @forelse ($alertas as $alerta)
                @php $e = $estilos[$alerta['tipo']]; @endphp
                @if ($alerta['tipo'] === 'cuota_vencida')
                    {{-- Cuota vencida: casilla para elegir, enlace al alumno y botón "Enviar correo" --}}
                    <div class="sg-fila group py-4">
                        <input type="checkbox" class="correo-elegir h-4 w-4 shrink-0" value="{{ $alerta['id_persona'] }}" onchange="correoContar()" title="Elegir para preparar su correo">
                        <a href="{{ $alerta['ruta'] }}" class="flex min-w-0 flex-1 items-center gap-3">
                            <span class="sg-chip sg-chip--{{ $e['tono'] }}"><x-sg.icono :nombre="$e['icono']" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-sm text-slate-800">{{ $alerta['titulo'] }}</span>
                                    <span class="sg-badge sg-badge--rojo">Urgente</span>
                                </span>
                                <span class="block text-xs text-slate-500 mt-1">{{ $alerta['descripcion'] }}</span>
                            </span>
                        </a>
                        <button type="button" onclick="correoPreparar([{{ $alerta['id_persona'] }}])" class="sg-btn sg-btn--borde sg-btn--chico shrink-0"><x-sg.icono nombre="mail" /> Enviar correo</button>
                    </div>
                @else
                    <a href="{{ $alerta['ruta'] }}" class="sg-fila group py-4">
                        <span class="sg-chip sg-chip--{{ $e['tono'] }}"><x-sg.icono :nombre="$e['icono']" /></span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-sm text-slate-800">{{ $alerta['titulo'] }}</span>
                                @if ($alerta['severidad'] === 'alta')
                                    <span class="sg-badge sg-badge--rojo">Urgente</span>
                                @endif
                            </span>
                            <span class="block text-xs text-slate-500 mt-1">{{ $alerta['descripcion'] }}</span>
                        </span>
                        <span class="hidden sm:inline-flex sg-badge sg-badge--gris sg-badge--sin-punto">{{ $e['etiqueta'] }}</span>
                        <x-sg.icono nombre="chevron-right" class="sg-fila__flecha h-5 w-5" />
                    </a>
                @endif
            @empty
                <x-sg.vacio icono="check-circle" titulo="¡Todo en orden!">No hay alertas activas por el momento.</x-sg.vacio>
            @endforelse
        </div>

        @if ($alertas->hasPages())
            <x-slot:pie>{{ $alertas->links() }}</x-slot:pie>
        @endif
    </x-sg.tarjeta>

    {{-- Historial de comunicaciones --}}
    <div class="sg-card sg-revelar overflow-hidden mt-8" id="historial-correos">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="mail" /></span>
                <div><h3>Historial de comunicaciones</h3><p>Correos enviados a mano a los alumnos, con su resultado</p></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead><tr><th>Fecha y hora</th><th>Alumno</th><th>Destinatario</th><th>Asunto</th><th>Enviado por</th><th>Resultado</th></tr></thead>
                <tbody class="sg-filas">
                    @forelse ($historial as $c)
                        <tr>
                            <td class="whitespace-nowrap">{{ $c->created_at->format('d/m/Y H:i') }}</td>
                            <td class="font-semibold text-slate-700">{{ $c->alumno?->apellido }}, {{ $c->alumno?->nombre }}</td>
                            <td class="text-slate-500">{{ $c->destinatario }}</td>
                            <td class="text-slate-600">{{ $c->asunto }}</td>
                            <td class="text-slate-500">{{ $c->enviadoPor?->apellido }}, {{ $c->enviadoPor?->nombre }}</td>
                            <td>
                                @if ($c->enviado)
                                    <span class="sg-badge sg-badge--verde">Enviado</span>
                                @else
                                    <span class="sg-badge sg-badge--rojo" title="{{ $c->error }}">Error</span>
                                    <p class="text-[11px] text-red-500 mt-1 max-w-[220px] truncate" title="{{ $c->error }}">{{ $c->error }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-sg.vacio icono="mail">Todavía no se envió ningún correo.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($historial->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">{{ $historial->fragment('historial-correos')->links() }}</div>
        @endif
    </div>

    {{-- ===================== Ventana: preparar y enviar correo ===================== --}}
    <div id="modal-correo" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-2xl p-6 max-h-[92vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-display font-extrabold text-xl text-[#0B3D91]">Enviar correo</h3>
                    <p id="correo-progreso" class="text-sm text-slate-500"></p>
                </div>
                <button type="button" onclick="correoCerrar()" class="text-slate-400 hover:text-slate-700 text-xl" title="Cerrar">✕</button>
            </div>

            <p id="correo-resultado" class="hidden sg-aviso text-sm mb-4"><span></span></p>

            <div class="space-y-3">
                <div>
                    <label class="sg-label">Para</label>
                    <input type="text" id="correo-para" readonly class="sg-input w-full !bg-slate-50">
                    <p id="correo-para-error" class="hidden text-xs text-red-600 mt-1">Este alumno no tiene un correo válido: no se le puede enviar. Corregilo en su ficha.</p>
                </div>
                <div>
                    <label class="sg-label">Asunto *</label>
                    <input type="text" id="correo-asunto" maxlength="150" class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">Mensaje *</label>
                    <textarea id="correo-mensaje" rows="12" maxlength="5000" class="sg-input w-full font-mono text-[13px] leading-relaxed"></textarea>
                    <p class="text-xs text-slate-400 mt-1">Podés editar el asunto y el mensaje antes de enviarlo. Se envía solo a este alumno.</p>
                </div>
                {{-- Vista previa: cómo lo va a ver el alumno --}}
                <div id="correo-previa-caja" class="hidden">
                    <p class="sg-label">Vista previa</p>
                    <iframe id="correo-previa" class="w-full h-80 rounded-2xl border border-slate-200 bg-slate-50"></iframe>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 mt-5">
                <button type="button" onclick="correoVistaPrevia()" class="sg-btn sg-btn--borde"><x-sg.icono nombre="eye" /> Vista previa</button>
                <button type="button" id="correo-saltar" onclick="correoSiguiente()" class="hidden sg-btn sg-btn--borde">Saltar este alumno</button>
                <button type="button" id="correo-enviar" onclick="correoConfirmar()" class="sg-btn sg-btn--azul"><x-sg.icono nombre="mail" /> Enviar</button>
            </div>
        </div>
    </div>

    {{-- Confirmación antes de enviar --}}
    <div id="modal-correo-confirmar" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[90] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center">
            <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-blue-100 text-2xl">✉️</div>
            <h3 class="font-display text-lg font-bold text-slate-800">¿Enviar este correo?</h3>
            <p id="correo-confirmar-texto" class="mt-2 mb-5 text-sm text-slate-600"></p>
            <div class="flex gap-3">
                <button type="button" onclick="correoModal('modal-correo-confirmar', false)" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                <button type="button" onclick="correoEnviar()" class="sg-btn sg-btn--azul flex-1 justify-center">Sí, enviar</button>
            </div>
        </div>
    </div>

    @php
        $rutasCorreoJs = ['base' => url('/admin/comunicaciones'), 'previa' => route('admin.comunicaciones.vista-previa')];
    @endphp
    <script>
        const correoRutas = @json($rutasCorreoJs);
        const correoToken = @json(csrf_token());
        let correoCola = [], correoPos = 0, correoDatos = null, correoHuboEnvios = false;
        const cId = id => document.getElementById(id);

        function correoModal(id, abrir) { cId(id).classList.toggle('hidden', !abrir); cId(id).classList.toggle('flex', abrir); }
        function correoContar() {
            const n = document.querySelectorAll('.correo-elegir:checked').length;
            cId('correo-cantidad').textContent = n;
            cId('correo-varios').disabled = n === 0;
        }
        function correoPrepararVarios() {
            correoPreparar([...document.querySelectorAll('.correo-elegir:checked')].map(c => +c.value));
        }

        // Prepara la cola: un correo individual por alumno, de a uno.
        function correoPreparar(ids) {
            correoCola = ids; correoPos = 0; correoHuboEnvios = false;
            correoModal('modal-correo', true);
            correoCargar();
        }
        async function correoCargar() {
            const id = correoCola[correoPos];
            cId('correo-resultado').classList.add('hidden');
            cId('correo-previa-caja').classList.add('hidden');
            cId('correo-progreso').textContent = 'Preparando...';
            const r = await fetch(`${correoRutas.base}/${id}/preparar`, { headers: { Accept: 'application/json' } });
            correoDatos = await r.json();
            cId('correo-progreso').textContent = `${correoDatos.alumno} · ${correoDatos.cantidad} cuota(s) vencida(s)` + (correoCola.length > 1 ? ` · Alumno ${correoPos + 1} de ${correoCola.length}` : '');
            cId('correo-para').value = correoDatos.email || '(sin correo cargado)';
            cId('correo-para-error').classList.toggle('hidden', correoDatos.email_valido);
            cId('correo-asunto').value = correoDatos.asunto;
            cId('correo-mensaje').value = correoDatos.mensaje;
            cId('correo-enviar').disabled = !correoDatos.email_valido;
            cId('correo-saltar').classList.toggle('hidden', correoCola.length < 2);
        }
        function correoCampos() { return { asunto: cId('correo-asunto').value.trim(), mensaje: cId('correo-mensaje').value.trim() }; }
        function correoAviso(texto, ok) {
            const p = cId('correo-resultado');
            p.className = 'sg-aviso text-sm mb-4 ' + (ok ? 'sg-aviso--exito' : 'sg-aviso--alerta');
            p.firstElementChild.textContent = texto;
        }
        async function correoPost(url, datos) {
            const r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': correoToken }, body: JSON.stringify(datos) });
            return { ok: r.ok, datos: await r.json().catch(() => ({})) };
        }
        const correoErrorValidacion = d => Object.values(d.errors || {})[0]?.[0] || d.mensaje || 'No se pudo completar.';

        async function correoVistaPrevia() {
            const { ok, datos } = await correoPost(correoRutas.previa, correoCampos());
            if (!ok) return correoAviso(correoErrorValidacion(datos), false);
            cId('correo-previa').srcdoc = datos.html;
            cId('correo-previa-caja').classList.remove('hidden');
        }
        function correoConfirmar() {
            const { asunto, mensaje } = correoCampos();
            if (asunto.length < 5 || mensaje.length < 20) return correoAviso('Completá el asunto y el mensaje antes de enviar.', false);
            cId('correo-confirmar-texto').textContent = `Se va a enviar a ${correoDatos.alumno} (${correoDatos.email}). Solo le llega a este alumno.`;
            correoModal('modal-correo-confirmar', true);
        }
        // Envío: solo cuando el secretario confirma. Muestra si salió bien o con error.
        async function correoEnviar() {
            correoModal('modal-correo-confirmar', false);
            cId('correo-enviar').disabled = true;
            correoAviso('Enviando...', true);
            const { ok, datos } = await correoPost(`${correoRutas.base}/${correoCola[correoPos]}/enviar`, correoCampos());
            correoHuboEnvios = correoHuboEnvios || datos.ok !== undefined;
            correoAviso(ok ? datos.mensaje : correoErrorValidacion(datos), ok);
            if (correoPos < correoCola.length - 1) {
                // En la cola: pasa al siguiente alumno (sin enviarle nada hasta que se confirme).
                cId('correo-saltar').textContent = 'Siguiente alumno';
                cId('correo-saltar').classList.remove('hidden');
            } else {
                cId('correo-enviar').disabled = true;
            }
        }
        function correoSiguiente() {
            if (correoPos >= correoCola.length - 1) return correoCerrar();
            correoPos++;
            cId('correo-saltar').textContent = 'Saltar este alumno';
            correoCargar();
        }
        // Al cerrar, si se envió algo, se recarga para ver el historial actualizado.
        function correoCerrar() {
            correoModal('modal-correo', false);
            if (correoHuboEnvios) location.reload();
        }
    </script>
@endsection
