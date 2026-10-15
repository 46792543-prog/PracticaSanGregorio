@extends('layouts.director')

@section('titulo', 'Cobro de Cuotas')
@section('subtitulo', 'Al registrar el pago, se actualiza el estado del alumno y se asienta como ingreso en el Libro de Caja')

@section('contenido')
    <x-sg.encabezado titulo="Cobro de Cuotas" subtitulo="Al registrar el pago, se actualiza el estado del alumno y se asienta como ingreso en el Libro de Caja" eyebrow="Finanzas" icono="card" />

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="relative bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#1565C0] px-6 py-4 overflow-hidden">
            <span class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[10px] border-[#FFC107]/25"></span>
            <h2 class="relative text-white font-display font-bold flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-[#FFD54F]"><x-sg.icono nombre="card" class="h-5 w-5" /></span> Registrar pago de cuota</h2>
        </div>

        <div class="p-6">
            <form method="GET" class="grid sm:grid-cols-[1fr_auto] gap-4 mb-2">
                <div>
                    <label class="sg-label">BUSCAR ALUMNO *</label>
                    <div class="sg-campo-icono">
                        <x-sg.icono nombre="search" />
                        <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." autofocus data-busqueda maxlength="20"
                               oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                               class="sg-input w-full py-3">
                    </div>
                </div>
                <div class="flex items-end">
                    <button class="sg-btn sg-btn--azul py-3"><x-sg.icono nombre="search" /> Buscar</button>
                </div>
            </form>

            @if ($resultados->isNotEmpty())
                <div class="grid gap-2 sm:grid-cols-2 mt-4" data-sg-escalonar>
                    @foreach ($resultados as $alumno)
                        <a href="{{ route('director.cuotas.index', ['alumno' => $alumno->id_usuario]) }}"
                           class="group flex items-center justify-between gap-3 rounded-2xl border-[1.5px] border-slate-100 hover:border-[#4FC3F7] hover:bg-blue-50/40 hover:-translate-y-0.5 px-4 py-3 transition-all">
                            <div class="flex items-center gap-3">
                                <span class="sg-avatar">
                                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-700 text-sm">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }} · {{ $alumno->persona->inscripcionesCarrera->first()?->carrera?->nombre_carrera }}</p>
                                </div>
                            </div>
                            @if ($alumno->cuotas_pendientes_count > 0)
                                <span class="sg-badge sg-badge--amarillo shrink-0">{{ $alumno->cuotas_pendientes_count }} cuota(s) pendiente(s)</span>
                            @else
                                <span class="sg-badge sg-badge--verde shrink-0">Al día</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @elseif ($busqueda)
                <p class="sg-aviso sg-aviso--info mt-4">No se encontraron alumnos que coincidan con "{{ $busqueda }}".</p>
            @endif

            @if ($alumnoSeleccionado)
                @php
                    $cuotasPendientes = $alumnoSeleccionado->persona->cuotas->where('pagado', false);
                    $mesesAdeudados = $estadoMensual->reject(fn ($item) => $item['pagado'])->pluck('mes');
                @endphp
                <div class="mt-6 rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50/80 to-white p-5 sm:p-6 sg-revelar">
                    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <span class="sg-avatar sg-avatar--marca !h-12 !w-12 !rounded-2xl">{{ mb_substr($alumnoSeleccionado->nombre, 0, 1) }}{{ mb_substr($alumnoSeleccionado->apellido, 0, 1) }}</span>
                            <div>
                            <p class="font-display font-bold text-slate-900">{{ $alumnoSeleccionado->apellido }}, {{ $alumnoSeleccionado->nombre }}</p>
                            <p class="text-xs text-slate-400">DNI {{ $alumnoSeleccionado->dni }} · {{ $alumnoSeleccionado->persona->inscripcionesCarrera->first()?->carrera?->nombre_carrera }}</p>
                            </div>
                        </div>
                        <a href="{{ route('director.cuotas.index') }}" class="sg-btn sg-btn--borde sg-btn--chico"><x-sg.icono nombre="refresh" /> Cambiar alumno</a>
                    </div>

                    @if ($estadoMensual->isNotEmpty())
                        <div class="mb-5">
                            <p class="sg-label mb-2">Estado mes a mes (ciclo {{ now()->year }})</p>
                            <div class="grid grid-cols-3 sm:grid-cols-6 lg:grid-cols-12 gap-2">
                                @foreach ($estadoMensual as $item)
                                    <span @class([
                                            'flex flex-col items-center gap-1 rounded-xl py-2 text-[11px] font-bold ring-1',
                                            'bg-emerald-50 text-emerald-700 ring-emerald-100' => $item['pagado'],
                                            'bg-rose-50 text-rose-600 ring-rose-100' => ! $item['pagado'],
                                        ])>
                                        <x-sg.icono :nombre="$item['pagado'] ? 'check-circle' : 'x-circle'" class="h-4 w-4" />
                                        {{ \Illuminate\Support\Str::limit($item['mes']->nombre_mes, 3, '') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php
                        $cuotasPorAnio = $alumnoSeleccionado->persona->cuotas->groupBy('id_anio_lectivo')->map(fn ($c) => $c->pluck('id_mes'));
                        $anioVigenteId = $corteActivo?->id_anio_lectivo ?? $anios->first()?->id_anio_lectivo;
                    @endphp

                    <details class="mb-5 rounded-2xl border-[1.5px] border-dashed border-slate-200 bg-white open:border-[#BDE5FA] transition-colors">
                        <summary class="cursor-pointer list-none flex items-center gap-3 px-4 py-3 font-semibold text-[#0B3D91] text-sm"><span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="plus" /></span> Generar cuota nueva <x-sg.icono nombre="chevron-down" class="h-4 w-4 ml-auto" /></summary>
                        <div class="px-4 pb-4 pt-1 border-t border-slate-100">
                            <form method="POST" action="{{ route('director.cuotas.generar') }}" id="form-generar-cuota">
                                @csrf
                                <input type="hidden" name="alumno_usuario_id" value="{{ $alumnoSeleccionado->id_usuario }}">
                                <input type="hidden" name="persona_id" value="{{ $alumnoSeleccionado->persona->id_persona }}">

                                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="sg-label">AÑO LECTIVO *</label>
                                        <select name="id_anio_lectivo" id="select-anio-generar" required class="sg-input w-full">
                                            @foreach ($anios as $anio)
                                                <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === $anioVigenteId)>{{ $anio->anio }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="sg-label">DÍA DE VENCIMIENTO (1-28) *</label>
                                        <input type="number" name="dia_vencimiento" min="1" max="28" value="10" required
                                               class="sg-input w-full">
                                    </div>
                                </div>

                                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="sg-label">CONCEPTO</label>
                                        <input type="text" name="concepto" data-solo="alfanumerico" data-max-len="20" maxlength="20" placeholder="Cuota mensual"
                                               class="sg-input w-full">
                                    </div>
                                    <div>
                                        <label class="sg-label">MONTO *</label>
                                        <div class="flex items-center gap-1">
                                            <span class="text-slate-400 text-sm">$</span>
                                            <input type="number" step="0.01" min="0" name="monto" required
                                                   class="sg-input w-full">
                                        </div>
                                    </div>
                                </div>

                                <p class="sg-label mb-2">Meses a generar</p>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-4" id="grid-meses-generar">
                                    @foreach ($meses as $mes)
                                        <label class="flex items-center gap-2 rounded-xl border-[1.5px] border-slate-200 px-3 py-2 text-sm cursor-pointer transition-colors hover:border-[#4FC3F7] has-[:checked]:border-[#0B3D91] has-[:checked]:bg-blue-50 has-[:checked]:text-[#0B3D91] has-[:disabled]:opacity-40 has-[:disabled]:cursor-not-allowed">
                                            <input type="checkbox" name="meses[]" value="{{ $mes->id_mes }}" class="chk-mes-generar h-4 w-4 rounded" data-mes="{{ $mes->id_mes }}">
                                            {{ $mes->nombre_mes }}
                                        </label>
                                    @endforeach
                                </div>
                                <p class="text-xs text-slate-400 mb-4" id="aviso-meses-existentes"></p>

                                <div class="flex justify-end">
                                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="plus" /> Generar cuota(s)</button>
                                </div>
                            </form>
                        </div>
                    </details>

                    <script>
                        (function () {
                            const cuotasPorAnio = @json($cuotasPorAnio);
                            const selectAnio = document.getElementById('select-anio-generar');
                            const aviso = document.getElementById('aviso-meses-existentes');

                            function actualizarMesesDisponibles() {
                                const mesesExistentes = cuotasPorAnio[selectAnio.value] || [];
                                document.querySelectorAll('.chk-mes-generar').forEach(chk => {
                                    const yaExiste = mesesExistentes.includes(parseInt(chk.dataset.mes));
                                    chk.disabled = yaExiste;
                                    chk.checked = false;
                                });
                                aviso.textContent = mesesExistentes.length
                                    ? 'Los meses deshabilitados ya tienen una cuota generada para ese año.'
                                    : '';
                            }

                            selectAnio.addEventListener('change', actualizarMesesDisponibles);
                            actualizarMesesDisponibles();
                        })();
                    </script>

                    @if ($cuotasPendientes->isEmpty() && $mesesAdeudados->isNotEmpty())
                        <p class="sg-aviso sg-aviso--alerta">
                            Debe {{ $mesesAdeudados->pluck('nombre_mes')->join(', ') }}, pero todavía no se le generó la cuota de {{ $mesesAdeudados->count() > 1 ? 'esos meses' : 'ese mes' }}. Generala más abajo para poder cobrarla.
                        </p>
                    @elseif ($cuotasPendientes->isEmpty())
                        <p class="sg-aviso sg-aviso--exito"><x-sg.icono nombre="check-circle" class="h-5 w-5 shrink-0" /> Este alumno no tiene cuotas pendientes.</p>
                    @else
                        <form id="form-cobrar-cuotas" method="POST" action="{{ route('director.cuotas.cobrar') }}">
                            @csrf
                            <input type="hidden" name="persona_id" value="{{ $alumnoSeleccionado->persona->id_persona }}">
                        </form>

                        @if ($primerMesSinGenerar)
                            <p class="sg-aviso sg-aviso--alerta mb-3">
                                Debe {{ $primerMesSinGenerar->nombre_mes }}, pero todavía no se le generó esa cuota. Las cuotas de meses posteriores quedan bloqueadas hasta que la generes y la cobres.
                            </p>
                        @endif

                        <p class="sg-label mb-1">Cuotas pendientes — Seleccioná las que se están pagando y ajustá el monto si corresponde:</p>
                        <p class="text-xs text-slate-400 mb-2">Se cobran en orden: al tildar una cuota se tildan también las anteriores, y no se puede saltear ningún mes.</p>
                        <div class="space-y-2 mb-4">
                            @foreach ($cuotasPendientes->values() as $cuota)
                                @php
                                    $bloqueada = $primerMesSinGenerar
                                        && $cuota->id_anio_lectivo == $primerMesSinGenerar->id_anio_lectivo
                                        && $cuota->id_mes > $primerMesSinGenerar->id_mes;
                                @endphp
                                <div class="rounded-2xl bg-white border-[1.5px] border-slate-200 px-4 py-3 transition-colors has-[.cuota-check:checked]:border-[#4FC3F7] has-[.cuota-check:checked]:shadow-md">
                                    <div class="flex items-center justify-between gap-3">
                                        <label class="flex items-center gap-3 cursor-pointer flex-1">
                                            <input type="checkbox" form="form-cobrar-cuotas" name="cuotas[{{ $cuota->id_cuota }}][pagar]" value="1" class="h-4 w-4 rounded cuota-check" data-id="{{ $cuota->id_cuota }}" data-orden="{{ $loop->index }}" @if ($bloqueada) disabled @else checked @endif>
                                            <span class="font-semibold text-slate-700 text-sm">{{ $cuota->concepto ?: 'Cuota ' . $cuota->mes->nombre_mes }}</span>
                                            @if ($bloqueada)
                                                <span class="sg-badge sg-badge--gris">Bloqueada: falta generar {{ $primerMesSinGenerar->nombre_mes }}</span>
                                            @endif
                                        </label>
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button type="button" onclick="document.getElementById('editar-cuota-{{ $cuota->id_cuota }}').classList.toggle('hidden')"
                                                    class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar cuota"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                            <form method="POST" action="{{ route('director.cuotas.eliminar', $cuota) }}" onsubmit="return confirm('¿Eliminar esta cuota? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="alumno_usuario_id" value="{{ $alumnoSeleccionado->id_usuario }}">
                                                <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar cuota"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3">
                                        <div>
                                            <label class="sg-label">Monto</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-slate-400 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" form="form-cobrar-cuotas" name="cuotas[{{ $cuota->id_cuota }}][monto]" value="{{ $cuota->monto }}"
                                                       class="sg-input sg-input--chico cuota-monto w-full" data-id="{{ $cuota->id_cuota }}">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="sg-label !text-amber-600">Recargo por mora</label>
                                            <div class="flex items-center gap-1">
                                                <span class="text-slate-400 text-sm">$</span>
                                                <input type="number" step="0.01" min="0" form="form-cobrar-cuotas" name="cuotas[{{ $cuota->id_cuota }}][recargo]" value="0"
                                                       class="sg-input sg-input--chico cuota-recargo w-full border-amber-200" data-id="{{ $cuota->id_cuota }}">
                                            </div>
                                        </div>
                                        <div class="col-span-2 sm:col-span-1">
                                            <label class="sg-label">Subtotal</label>
                                            <p class="cuota-subtotal font-display font-bold text-[#0B3D91] text-base py-1" data-id="{{ $cuota->id_cuota }}">
                                                $ {{ number_format($cuota->monto, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div id="editar-cuota-{{ $cuota->id_cuota }}" class="hidden mt-3 pt-3 border-t border-dashed border-slate-200">
                                        <form method="POST" action="{{ route('director.cuotas.actualizar', $cuota) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="alumno_usuario_id" value="{{ $alumnoSeleccionado->id_usuario }}">
                                            <div class="col-span-2">
                                                <label class="sg-label">Concepto</label>
                                                <input type="text" name="concepto" data-solo="alfanumerico" data-max-len="20" maxlength="20" value="{{ $cuota->concepto }}"
                                                       class="sg-input sg-input--chico w-full">
                                            </div>
                                            <div>
                                                <label class="sg-label">Monto</label>
                                                <input type="number" step="0.01" min="0" name="monto" value="{{ $cuota->monto }}" required
                                                       class="sg-input sg-input--chico w-full">
                                            </div>
                                            <div>
                                                <label class="sg-label">Vencimiento</label>
                                                <input type="date" name="fecha_vencimiento" value="{{ $cuota->fecha_vencimiento?->toDateString() }}"
                                                       class="sg-input sg-input--chico w-full">
                                            </div>
                                            <div class="col-span-2 sm:col-span-4 flex justify-end">
                                                <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico">Guardar cambios de la cuota</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="sg-label">FECHA DE PAGO *</label>
                                <input type="date" form="form-cobrar-cuotas" name="fecha_pago" value="{{ now()->toDateString() }}" required
                                       class="sg-input w-full">
                            </div>
                            <div>
                                <label class="sg-label">MEDIO DE PAGO *</label>
                                <select form="form-cobrar-cuotas" name="medio_pago" required class="sg-input w-full">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-gradient-to-r from-[#0B3D91] to-[#1565C0] text-white px-5 py-4 mb-4 shadow-lg shadow-blue-900/20">
                            <span class="font-semibold text-blue-100 text-sm flex items-center gap-2"><x-sg.icono nombre="banknotes" /> Total a registrar</span>
                            <span id="total-cobro" class="font-display font-extrabold text-2xl text-[#FFD54F]">$ {{ number_format($cuotasPendientes->sum('monto'), 0, ',', '.') }}</span>
                        </div>

                        <p class="sg-aviso sg-aviso--exito mb-4 text-xs">
                            Al confirmar: el estado de {{ $alumnoSeleccionado->apellido }}, {{ $alumnoSeleccionado->nombre }} se actualizará y se registrará automáticamente un ingreso en el Libro de Caja.
                        </p>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('director.cuotas.index') }}" class="sg-btn sg-btn--borde">Cancelar</a>
                            <button type="submit" form="form-cobrar-cuotas" class="sg-btn sg-btn--verde"><x-sg.icono nombre="check-circle" /> Confirmar cobro y registrar en Caja</button>
                        </div>

                        <script>
                            function recalcularCuota(id) {
                                const monto = parseFloat(document.querySelector(`.cuota-monto[data-id="${id}"]`).value) || 0;
                                const recargo = parseFloat(document.querySelector(`.cuota-recargo[data-id="${id}"]`).value) || 0;
                                document.querySelector(`.cuota-subtotal[data-id="${id}"]`).textContent = '$ ' + (monto + recargo).toLocaleString('es-AR');
                                recalcularTotal();
                            }
                            function recalcularTotal() {
                                let total = 0;
                                document.querySelectorAll('.cuota-check:checked').forEach(cb => {
                                    const id = cb.dataset.id;
                                    const monto = parseFloat(document.querySelector(`.cuota-monto[data-id="${id}"]`).value) || 0;
                                    const recargo = parseFloat(document.querySelector(`.cuota-recargo[data-id="${id}"]`).value) || 0;
                                    total += monto + recargo;
                                });
                                document.getElementById('total-cobro').textContent = '$ ' + total.toLocaleString('es-AR');
                            }
                            // Cobro en orden: tildar una cuota tilda las anteriores; destildarla destilda las posteriores.
                            document.querySelectorAll('.cuota-check').forEach(cb => cb.addEventListener('change', () => {
                                const orden = parseInt(cb.dataset.orden);
                                document.querySelectorAll('.cuota-check:not(:disabled)').forEach(otro => {
                                    const ordenOtro = parseInt(otro.dataset.orden);
                                    if (cb.checked && ordenOtro < orden) otro.checked = true;
                                    if (! cb.checked && ordenOtro > orden) otro.checked = false;
                                });
                                recalcularTotal();
                            }));
                            recalcularTotal();
                            document.querySelectorAll('.cuota-monto, .cuota-recargo').forEach(input => {
                                input.addEventListener('input', () => recalcularCuota(input.dataset.id));
                            });
                        </script>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="clock" /></span>
                <div><h3>Historial de cobros de cuotas</h3><p>Pagos registrados recientemente</p></div>
            </div>
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="h_alumno" value="{{ $filtroHistorialAlumno }}" placeholder="Buscar alumno..." maxlength="25"
                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '')"
                   class="sg-input w-auto py-2">
            <button class="sg-btn sg-btn--suave sg-btn--chico"><x-sg.icono nombre="filter" /> Filtrar</button>
        </form>
        </div>
        <div class="overflow-x-auto">
        <table class="sg-tabla">
            <thead>
                <tr>
                    <th>Alumno</th>
                    <th>Cuota</th>
                    <th>Fecha pago</th>
                    <th>Monto</th>
                    <th>Recargo</th>
                    <th>Medio</th>
                </tr>
            </thead>
            <tbody class="sg-filas">
                @forelse ($historial as $cuota)
                    <tr>
                        <td><div class="flex items-center gap-3"><span class="sg-avatar">{{ mb_substr($cuota->personaAlumno->nombre, 0, 1) }}{{ mb_substr($cuota->personaAlumno->apellido, 0, 1) }}</span><span class="font-semibold text-slate-800">{{ $cuota->personaAlumno->apellido }}, {{ $cuota->personaAlumno->nombre }}</span></div></td>
                        <td class="text-slate-500">{{ $cuota->concepto ?: 'Cuota ' . $cuota->mes->nombre_mes }}</td>
                        <td class="text-slate-500">{{ $cuota->fecha_pago ? \App\Support\FechaEsp::corta($cuota->fecha_pago) : '—' }}</td>
                        <td class="font-display font-bold !text-emerald-600 whitespace-nowrap">$ {{ number_format($cuota->monto, 0, ',', '.') }}</td>
                        <td>
                            @if ($cuota->recargo > 0)
                                <span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">+$ {{ number_format($cuota->recargo, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="text-slate-500">{{ $cuota->movimientoCaja?->medioPago?->nombre_medio ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-sg.vacio icono="banknotes">Todavía no hay pagos registrados.</x-sg.vacio></td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if ($historial->hasPages())
            <div class="sg-card__foot">{{ $historial->links() }}</div>
        @endif
    </div>
@endsection
