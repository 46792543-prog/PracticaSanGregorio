@extends('layouts.director')

@section('titulo', 'Pago de Docentes')
@section('subtitulo', 'Gestioná y consultá los pagos realizados a los docentes de la institución')

@section('contenido')
    @php
        $estadoEstilos = [
            'Pagado' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'No pagado' => 'bg-rose-50 text-rose-600 ring-rose-200',
            'Anulado' => 'bg-rose-50 text-rose-600 ring-rose-200',
        ];
    @endphp

    <x-sg.encabezado titulo="Pago de Docentes" subtitulo="Registrá y consultá los pagos realizados a los docentes de la institución" eyebrow="Finanzas" icono="briefcase">
        <button type="button" onclick="abrirPago()" class="sg-btn sg-btn--primario">
            <x-sg.icono nombre="plus" /> Registrar pago
        </button>
    </x-sg.encabezado>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="cash" /></span>
                <div><h3>Pagos registrados</h3><p>Podés editar o eliminar un pago si hubo un error</p></div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div class="flex-1 min-w-[200px]">
                <label class="sg-label">Buscar docente</label>
                <div class="sg-campo-icono">
                    <x-sg.icono nombre="search" />
                    <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." maxlength="25"
                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                           class="sg-input w-full">
                </div>
            </div>
            <div>
                <label class="sg-label">Fecha de pago</label>
                <input type="date" name="fecha_pago" value="{{ $fechaPago }}" class="sg-input w-auto">
            </div>
            <div>
                <label class="sg-label">Período abonado</label>
                <input type="month" name="periodo" value="{{ $periodo }}" class="sg-input w-auto">
            </div>
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Buscar</button>
            @if ($busqueda || $fechaPago || $periodo)
                <a href="{{ route('director.pagos-docentes.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Período</th>
                        <th>Fecha de pago</th>
                        <th class="!text-right">Monto</th>
                        <th>Método de pago</th>
                        <th>Estado</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($pagos as $pago)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="sg-avatar">{{ mb_substr($pago->profesor->nombre, 0, 1) }}{{ mb_substr($pago->profesor->apellido, 0, 1) }}</span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800">{{ $pago->profesor->apellido }}, {{ $pago->profesor->nombre }}</p>
                                        <p class="text-xs text-slate-400">DNI {{ $pago->profesor->dni }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::mesAnio($pago->periodo) }}</td>
                            <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::corta($pago->fecha_pago) }}</td>
                            <td class="!text-right font-display font-bold !text-slate-800 whitespace-nowrap">$ {{ number_format($pago->monto, 0, ',', '.') }}</td>
                            <td>{{ $pago->metodoPago->nombre_metodo }}</td>
                            <td>
                                <span class="inline-flex text-xs font-semibold rounded-full px-3 py-1 ring-1 {{ $estadoEstilos[$pago->estadoPago->nombre_estado] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">
                                    {{ $pago->estadoPago->nombre_estado }}
                                </span>
                                @if ($pago->observaciones)
                                    <p class="text-xs text-slate-400 mt-1" title="{{ $pago->observaciones }}">{{ \Illuminate\Support\Str::limit($pago->observaciones, 30) }}</p>
                                @endif
                            </td>
                            <td><div class="flex justify-center gap-1">
                                {{-- Lápiz: abre el mismo modal de "Registrar pago" con los datos de este pago --}}
                                <button type="button" title="Editar pago" onclick="abrirPago(JSON.parse(this.dataset.pago))"
                                        data-pago="{{ json_encode(['id' => $pago->id_pago_docente, 'id_profesor' => $pago->id_profesor, 'periodo' => $pago->periodo->format('Y-m'), 'fecha_pago' => $pago->fecha_pago->toDateString(), 'monto' => (float) $pago->monto, 'id_metodo_pago_docente' => $pago->id_metodo_pago_docente, 'id_estado_pago_docente' => $pago->id_estado_pago_docente, 'observaciones' => $pago->observaciones]) }}"
                                        class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                <form method="POST" action="{{ route('director.pagos-docentes.destroy', $pago) }}"
                                      data-confirm="¿Eliminar el pago a {{ $pago->profesor->apellido }}, {{ $pago->profesor->nombre }}? Si estaba pagado, también se borra su egreso en caja.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Eliminar pago" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                </form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="briefcase" titulo="Sin pagos">No hay pagos a docentes registrados con estos filtros.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pagos->hasPages())
            <div class="sg-card__foot">{{ $pagos->links() }}</div>
        @endif
    </div>

    {{-- Modal: registrar pago --}}
    <div id="modal-pago-docente" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-5">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="briefcase" /></span> <span id="titulo-modal-pago">Registrar pago de docente</span></h3>
                <button type="button" onclick="cerrarModal('modal-pago-docente')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.store') }}" id="form-pago-docente">
                @csrf
                {{-- Solo al editar: _method=PUT y el id del pago que se edita --}}
                <input type="hidden" name="_method" value="PUT" disabled>
                <input type="hidden" name="id_pago_editando" value="{{ old('id_pago_editando') }}">
                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div class="sm:col-span-2">
                        <label class="sg-label">Docente *</label>
                        <select name="id_profesor" id="input-pago-profesor" required onchange="actualizarDniDocente(); calcularMonto()" class="sg-input w-full">
                            <option value="">Seleccioná docente...</option>
                            @foreach ($profesores as $profesor)
                                <option value="{{ $profesor->id_profesor }}" data-nombre-base="{{ $profesor->apellido }}, {{ $profesor->nombre }}" @selected(old('id_profesor') == $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">Los docentes que ya tienen un pago cargado para el período elegido aparecen deshabilitados.</p>
                    </div>
                    <div>
                        <label class="sg-label">DNI</label>
                        <input type="text" id="input-pago-dni" readonly placeholder="—" class="sg-input w-full !bg-slate-50 text-slate-500">
                    </div>
                    <div>
                        <label class="sg-label">Período abonado *</label>
                        <input type="month" name="periodo" id="input-pago-periodo" required value="{{ old('periodo', now()->format('Y-m')) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Fecha de pago *</label>
                        <input type="date" name="fecha_pago" id="input-pago-fecha" required value="{{ old('fecha_pago', now()->toDateString()) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label flex items-center gap-1.5">Monto a pagar (calculado) <x-sg.icono nombre="lock" class="inline h-3 w-3" />
                            {{-- Ojito: abre el detalle de por qué cobra ese monto (horas, valores e inasistencias) --}}
                            <button type="button" id="boton-detalle-monto" onclick="abrirModal('modal-detalle-monto')" disabled title="Ver el detalle del monto"
                                    class="ml-auto grid h-6 w-6 place-items-center rounded-full text-[#0B3D91] hover:bg-blue-50 disabled:opacity-30 disabled:cursor-not-allowed"><x-sg.icono nombre="eye" class="h-4 w-4" /></button>
                        </label>
                        <div class="sg-campo-icono">
                            <x-sg.icono nombre="cash" />
                            {{-- Bloqueado: lo calcula el sistema con las horas cátedra designadas (no se envía, el servidor lo vuelve a calcular) --}}
                            <input type="text" id="input-pago-monto" readonly tabindex="-1" placeholder="Elegí docente y período" class="sg-input w-full !bg-slate-50 font-semibold text-slate-700 cursor-not-allowed">
                        </div>
                        <p id="detalle-pago-monto" class="text-xs text-slate-400 mt-1">Horas semanales × 4 × valor de la hora cátedra, menos inasistencias injustificadas.</p>
                    </div>
                    <div>
                        <label class="sg-label">Método de pago *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_metodo_pago_docente" id="input-pago-metodo" required class="sg-input min-w-0 flex-1">
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago_docente }}" @selected(old('id_metodo_pago_docente') == $metodo->id_metodo_pago_docente)>{{ $metodo->nombre_metodo }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModal('modal-metodo-pago')" title="Agregar nuevo método de pago" class="sg-btn sg-btn--borde sg-btn--icono shrink-0"><x-sg.icono nombre="plus" /></button>
                        </div>
                    </div>
                    <div>
                        <label class="sg-label">Estado *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_estado_pago_docente" id="input-pago-estado" required class="sg-input min-w-0 flex-1">
                                @foreach ($estadosPago as $estado)
                                    <option value="{{ $estado->id_estado_pago_docente }}" @selected(old('id_estado_pago_docente') ? old('id_estado_pago_docente') == $estado->id_estado_pago_docente : $estado->nombre_estado === 'Pagado')>{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModal('modal-estado-pago')" title="Agregar nuevo estado" class="sg-btn sg-btn--borde sg-btn--icono shrink-0"><x-sg.icono nombre="plus" /></button>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="sg-label">Observaciones</label>
                        <textarea name="observaciones" id="input-pago-observaciones" rows="2" maxlength="255" placeholder="Observaciones opcionales..." class="sg-input w-full">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-pago-docente')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center"><x-sg.icono nombre="check" /> <span id="boton-guardar-pago">Guardar pago</span></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: confirmar antes de guardar el pago --}}
    {{-- Ventana del ojito: detalle del monto calculado --}}
    <div id="modal-detalle-monto" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[95] items-center justify-center p-4" onclick="if (event.target === this) cerrarModal(this.id)">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="font-display font-extrabold text-xl text-[#0B3D91]">¿Por qué cobra este monto?</h3>
                    <p id="detalle-monto-titulo" class="text-sm text-slate-500 capitalize"></p>
                </div>
                <button type="button" onclick="cerrarModal('modal-detalle-monto')" class="text-slate-400 hover:text-slate-700 text-xl" title="Cerrar">✕</button>
            </div>
            <div id="detalle-monto-cuerpo" class="space-y-3"></div>
            <div id="detalle-monto-totales" class="mt-4 rounded-2xl bg-slate-50 p-4 space-y-1.5 text-sm"></div>
            <p class="mt-3 text-xs text-slate-400">Horas cátedra designadas × 4 semanas × valor de la hora según la carrera y el tipo de designación, menos las horas de licencias sin goce o con documentación pendiente.</p>
        </div>
    </div>

    <div id="modal-confirmar-pago" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[90] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6 text-center">
            <span class="sg-chip sg-chip--amarillo mx-auto mb-3"><x-sg.icono nombre="alert" /></span>
            <h3 class="font-display font-bold text-slate-800 mb-2">Confirmar pago</h3>
            <p id="texto-confirmar-pago" class="text-sm text-slate-600 mb-5"></p>
            {{-- Solo al editar: motivo obligatorio (va dentro del formulario con form="...") --}}
            <div id="bloque-motivo-pago" class="hidden text-left mb-5">
                <label class="sg-label">¿Por qué se edita este pago? *</label>
                <textarea form="form-pago-docente" name="motivo" id="input-motivo-pago" rows="2" maxlength="500"
                          placeholder="Ej: se cargó mal el período o el método de pago" class="sg-input w-full"></textarea>
                <p id="error-motivo-pago" class="hidden text-xs text-red-500 mt-1">Escribí el motivo (al menos 5 letras).</p>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="cerrarModal('modal-confirmar-pago')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                <button type="button" onclick="confirmarPago()" class="sg-btn sg-btn--primario flex-1 justify-center"><x-sg.icono nombre="check" /> Sí, confirmar</button>
            </div>
        </div>
    </div>

    {{-- Modal: nuevo método de pago --}}
    <div id="modal-metodo-pago" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[80] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="card" /></span> Nuevo método de pago</h3>
                <button type="button" onclick="cerrarModal('modal-metodo-pago')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.metodos-pago.store') }}">
                @csrf
                <label class="sg-label">Nombre del método</label>
                <input type="text" name="nombre_metodo" required maxlength="30" placeholder="Ej: Depósito bancario" class="sg-input w-full mb-5">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-metodo-pago')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center">Guardar método</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo estado --}}
    <div id="modal-estado-pago" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[80] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="check-circle" /></span> Nuevo estado</h3>
                <button type="button" onclick="cerrarModal('modal-estado-pago')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.estados.store') }}">
                @csrf
                <label class="sg-label">Nombre del estado</label>
                <input type="text" name="nombre_estado" required maxlength="30" placeholder="Ej: No pagado" class="sg-input w-full mb-5">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-estado-pago')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center">Guardar estado</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dniPorProfesor = @json($profesores->mapWithKeys(fn ($p) => [$p->id_profesor => $p->dni]));
        const periodosPagadosPorProfesor = @json($periodosPagadosPorProfesor);

        function abrirModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (id === 'modal-pago-docente') actualizarProfesoresDisponibles();
        }
        function cerrarModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function actualizarDniDocente() {
            const id = document.getElementById('input-pago-profesor').value;
            document.getElementById('input-pago-dni').value = dniPorProfesor[id] ?? '';
        }

        // Un docente solo puede tener un pago por período: las opciones del
        // desplegable que ya tienen un pago cargado para el mes elegido
        // quedan deshabilitadas (no ocultas, para que se entienda por qué
        // no se pueden elegir).
        function actualizarProfesoresDisponibles() {
            const periodo = document.getElementById('input-pago-periodo').value;
            const select = document.getElementById('input-pago-profesor');

            Array.from(select.options).forEach(function (opcion) {
                if (!opcion.value) return;

                const periodosPagados = periodosPagadosPorProfesor[opcion.value] || [];
                // El pago que se está editando no cuenta como "ya pagado".
                const esElQueSeEdita = pagoEditando && pagoEditando.id_profesor == opcion.value && pagoEditando.periodo === periodo;
                const yaPagado = periodosPagados.includes(periodo) && !esElQueSeEdita;

                opcion.disabled = yaPagado;
                opcion.textContent = yaPagado
                    ? opcion.dataset.nombreBase + ' — ya tiene pago este período'
                    : opcion.dataset.nombreBase;
            });

            if (select.selectedOptions[0]?.disabled) {
                select.value = '';
                actualizarDniDocente();
            }
        }

        document.getElementById('input-pago-periodo').addEventListener('change', () => { actualizarProfesoresDisponibles(); calcularMonto(); });

        // Monto bloqueado: lo pide al servidor según las horas cátedra del docente en ese mes.
        let montoCalculado = null;
        async function calcularMonto() {
            const profesor = document.getElementById('input-pago-profesor').value;
            const periodo = document.getElementById('input-pago-periodo').value;
            const input = document.getElementById('input-pago-monto');
            const detalle = document.getElementById('detalle-pago-monto');
            const pesos = n => '$ ' + Number(n).toLocaleString('es-AR', { maximumFractionDigits: 2 });
            montoCalculado = null;
            input.value = '';
            document.getElementById('boton-detalle-monto').disabled = true;
            detalle.className = 'text-xs text-slate-400 mt-1';
            if (!profesor || !periodo) { detalle.textContent = 'Horas semanales × 4 × valor de la hora cátedra, menos inasistencias injustificadas.'; return; }

            detalle.textContent = 'Calculando...';
            try {
                const r = await fetch(`{{ route('director.liquidacion-horas.calcular') }}?profesor=${profesor}&periodo=${periodo}`, { headers: { Accept: 'application/json' } });
                const liq = await r.json();
                if (!r.ok) throw new Error();
                if (!liq.detalle.length) {
                    detalle.textContent = 'Este docente no tiene horas cátedra designadas en ese mes.';
                } else if (liq.tipos_sin_valor.length) {
                    detalle.textContent = 'Falta cargar el valor de la hora cátedra para: ' + liq.tipos_sin_valor.join(', ') + ' (en Liquidación de horas).';
                } else if (liq.total <= 0) {
                    detalle.textContent = 'Las inasistencias injustificadas cubren todas sus horas del mes: no hay monto para pagar.';
                } else {
                    montoCalculado = liq.total;
                    input.value = pesos(liq.total);
                    detalle.textContent = liq.horas_falta
                        ? `Incluye descuento de ${liq.horas_falta} h por inasistencias. Tocá el ojito para ver el detalle.`
                        : 'Sin descuentos por inasistencias en este período. Tocá el ojito para ver el detalle.';
                    detalle.className = 'text-xs text-emerald-600 mt-1';
                    armarDetalleMonto(liq, pesos);
                    document.getElementById('boton-detalle-monto').disabled = false;
                    return;
                }
                detalle.className = 'text-xs text-amber-600 mt-1';
            } catch (e) {
                detalle.textContent = 'No se pudo calcular el monto. Probá de nuevo.';
                detalle.className = 'text-xs text-red-600 mt-1';
            }
        }
        actualizarProfesoresDisponibles();

        // Detalle del monto (ventana del ojito): por materia horas, valor, inasistencias y descuento.
        function armarDetalleMonto(liq, pesos) {
            const docente = document.getElementById('input-pago-profesor').selectedOptions[0]?.dataset.nombreBase ?? '';
            const [anio, mes] = document.getElementById('input-pago-periodo').value.split('-');
            const periodo = new Date(anio, mes - 1, 1).toLocaleDateString('es-AR', { month: 'long', year: 'numeric' });
            document.getElementById('detalle-monto-titulo').textContent = `${docente} · ${periodo}`;
            document.getElementById('detalle-monto-cuerpo').innerHTML = liq.detalle.map(d => `
                <div class="rounded-2xl border border-slate-200 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div><p class="font-semibold text-slate-800">${d.materia}</p><p class="text-xs text-slate-400">${d.carrera ?? ''} · ${d.tipo}</p></div>
                        <p class="font-display font-bold text-[#0B3D91] whitespace-nowrap">${pesos(d.subtotal)}</p>
                    </div>
                    <div class="mt-2 text-sm text-slate-600 space-y-0.5">
                        <p>${d.horas_semanales} h semanales × 4 = <strong>${d.horas_mes} h</strong> en el mes</p>
                        <p>${d.horas_mes} h × ${pesos(d.valor_hora)} (hora cátedra ${d.tipo} · ${d.carrera ?? ''}) = <strong>${pesos(d.bruto)}</strong></p>
                        ${d.horas_falta ? `<p class="text-red-600">− ${d.horas_falta} h de inasistencias × ${pesos(d.valor_hora)} = <strong>− ${pesos(d.descuento)}</strong></p>` : ''}
                    </div>
                    ${d.faltas.length ? `<div class="mt-2 border-t border-slate-100 pt-2 text-xs space-y-0.5">
                        <p class="font-bold uppercase tracking-wider text-slate-400">Inasistencias</p>
                        ${d.faltas.map(f => `<p class="${f.justificada ? 'text-slate-400' : 'text-red-600'}">${f.fecha} · ${f.horas} h · ${f.tipo} — ${f.justificada ? 'no descuenta' : 'descuenta'}</p>`).join('')}
                    </div>` : ''}
                </div>`).join('');
            document.getElementById('detalle-monto-totales').innerHTML = `
                <div class="flex justify-between text-slate-600"><span>Total horas cátedra (${liq.horas_mes} h)</span><span>${pesos(liq.bruto)}</span></div>
                <div class="flex justify-between ${liq.descuento ? 'text-red-600' : 'text-slate-400'}"><span>Descuento por inasistencias (${liq.horas_falta} h)</span><span>− ${pesos(liq.descuento)}</span></div>
                <div class="flex justify-between border-t border-slate-200 pt-2 font-display font-extrabold text-lg text-[#0B3D91]"><span>Monto a pagar</span><span>${pesos(liq.total)}</span></div>`;
        }

        // Abre el modal para registrar (sin datos) o para editar (con los datos
        // del pago). "rellenar" en false deja los valores que volvieron con error.
        const formPago = document.getElementById('form-pago-docente');
        var pagoEditando = null; // var: la usa actualizarProfesoresDisponibles(), que corre antes

        function abrirPago(pago = null, rellenar = true) {
            pagoEditando = pago;
            formPago.action = pago ? '{{ route('director.pagos-docentes.update', '__ID__') }}'.replace('__ID__', pago.id) : '{{ route('director.pagos-docentes.store') }}';
            formPago.elements._method.disabled = !pago;
            formPago.elements.id_pago_editando.value = pago ? pago.id : '';
            document.getElementById('titulo-modal-pago').textContent = pago ? 'Editar pago de docente' : 'Registrar pago de docente';
            document.getElementById('boton-guardar-pago').textContent = pago ? 'Actualizar pago' : 'Guardar pago';

            if (!pago && rellenar) {
                formPago.reset(); // pago nuevo: el formulario vuelve a quedar limpio
                const fecha = document.getElementById('input-pago-fecha');
                fecha._flatpickr?.setDate(fecha.value);
            }

            if (pago && rellenar) {
                document.getElementById('input-pago-profesor').value = pago.id_profesor;
                document.getElementById('input-pago-periodo').value = pago.periodo;
                document.getElementById('input-pago-fecha')._flatpickr?.setDate(pago.fecha_pago);
                document.getElementById('input-pago-metodo').value = pago.id_metodo_pago_docente;
                document.getElementById('input-pago-estado').value = pago.id_estado_pago_docente;
                document.getElementById('input-pago-observaciones').value = pago.observaciones ?? '';
            }

            abrirModal('modal-pago-docente');
            actualizarDniDocente();
            calcularMonto();
        }

        // Al tocar "Guardar pago": en vez de enviar directo, pregunta
        // "¿Está seguro de abonar $X a tal docente?". El botón "Sí, confirmar"
        // envía el formulario (form.submit() no vuelve a pasar por acá).
        formPago.addEventListener('submit', function (evento) {
            evento.preventDefault();
            if (montoCalculado === null) {
                document.getElementById('detalle-pago-monto').classList.add('font-bold');
                return; // sin horas designadas o sin valor cargado no hay nada que pagar
            }
            const docente = document.getElementById('input-pago-profesor').selectedOptions[0].dataset.nombreBase;
            const monto = montoCalculado.toLocaleString('es-AR');
            document.getElementById('texto-confirmar-pago').textContent = pagoEditando
                ? `¿Está seguro de actualizar el pago a ${docente} por $ ${monto}?`
                : `¿Está seguro de abonar $ ${monto} a ${docente}?`;
            document.getElementById('bloque-motivo-pago').classList.toggle('hidden', !pagoEditando);
            document.getElementById('error-motivo-pago').classList.add('hidden');
            abrirModal('modal-confirmar-pago');
        });

        // "Sí, confirmar": al editar, no deja seguir sin motivo.
        function confirmarPago() {
            const motivo = document.getElementById('input-motivo-pago');
            if (pagoEditando && motivo.value.trim().length < 5) {
                document.getElementById('error-motivo-pago').classList.remove('hidden');
                motivo.focus();
                return;
            }
            if (!pagoEditando) motivo.value = ''; // al registrar no se manda motivo
            formPago.submit();
        }

        @if (! $errors->any() && request('nuevo'))
            // Viene de "Registrar pago" en Liquidación de horas: abre el modal con el docente y el mes.
            abrirPago();
            document.getElementById('input-pago-periodo').value = @json(request('periodo_nuevo'));
            actualizarProfesoresDisponibles();
            document.getElementById('input-pago-profesor').value = @json(request('nuevo'));
            actualizarDniDocente();
            calcularMonto();
        @endif

        @if ($errors->any())
            // Si volvió con error, reabre el modal (en modo edición si se estaba editando).
            abrirPago(@json(old('id_pago_editando') ? ['id' => old('id_pago_editando'), 'id_profesor' => old('id_profesor'), 'periodo' => old('periodo')] : null), false);
        @endif
    </script>
@endsection
