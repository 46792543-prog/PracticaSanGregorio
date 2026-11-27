@extends('layouts.director')

@section('titulo', 'Libro de Caja')
@section('subtitulo', 'Ingresos y egresos de la institución · ' . \App\Support\FechaEsp::mesAnio($mes))

@section('contenido')
    @php
        $tarjetas = [
            ['label' => 'Hoy', 'detalle' => \App\Support\FechaEsp::corta(now()), 'datos' => $resumenHoy, 'icono' => 'clock', 'tono' => 'amarillo'],
            ['label' => 'Esta semana', 'detalle' => 'Últimos 7 días', 'datos' => $resumenSemana, 'icono' => 'calendar', 'tono' => 'celeste'],
            ['label' => 'Este mes', 'detalle' => \App\Support\FechaEsp::mesAnio($mes), 'datos' => $resumenMes, 'icono' => 'chart', 'tono' => 'verde'],
        ];
    @endphp

    <x-sg.encabezado titulo="Libro de Caja" :subtitulo="'Todos los movimientos de dinero: cuotas, matrículas y honorarios docentes se asientan solos · ' . \App\Support\FechaEsp::mesAnio($mes)" eyebrow="Finanzas" icono="book" />

    <div class="grid md:grid-cols-3 gap-5 mb-8" data-sg-escalonar>
        @foreach ($tarjetas as $tarjeta)
            <div class="sg-card sg-card--hover sg-stat sg-tono--{{ $tarjeta['tono'] }}">
                <span class="sg-stat__deco"></span>
                <div class="relative flex items-center gap-3 mb-4">
                    <span class="sg-chip sg-chip--{{ $tarjeta['tono'] }}"><x-sg.icono :nombre="$tarjeta['icono']" /></span>
                    <div>
                        <p class="font-display font-bold text-slate-800">{{ $tarjeta['label'] }}</p>
                        <p class="text-xs text-slate-400">{{ $tarjeta['detalle'] }}</p>
                    </div>
                </div>
                <div class="relative space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 flex items-center gap-1.5"><x-sg.icono nombre="trending-up" class="h-4 w-4 text-emerald-500" /> Ingresos</span>
                        <span class="font-bold text-emerald-600">$ {{ number_format($tarjeta['datos']['ingresos'], 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 flex items-center gap-1.5"><x-sg.icono nombre="trending-down" class="h-4 w-4 text-rose-500" /> Egresos</span>
                        <span class="font-bold text-rose-500">$ {{ number_format($tarjeta['datos']['egresos'], 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="relative flex justify-between items-end mt-4 pt-4 border-t border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Saldo</p>
                        <p class="font-display text-2xl font-extrabold text-[#0B3D91]">$ {{ number_format($tarjeta['datos']['saldo'], 0, ',', '.') }}</p>
                    </div>
                    <span class="sg-badge sg-badge--gris sg-badge--sin-punto">{{ $tarjeta['datos']['cantidad'] }} mov.</span>
                </div>
                <span class="sg-stat__barra"></span>
            </div>
        @endforeach
    </div>

    <div class="sg-card sg-revelar overflow-hidden mb-8 border-t-4 border-t-rose-400">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--rojo"><x-sg.icono nombre="trending-down" /></span>
                <div><h3>Registrar egreso</h3><p>Toda salida de dinero de la institución. Los honorarios docentes se asientan solos al registrar el pago en Pago de Docentes.</p></div>
            </div>
        </div>
        <form method="POST" action="{{ route('director.caja.egresos.store') }}" class="p-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end"
              data-confirm="¿Registrar este egreso en el Libro de Caja?" data-confirm-tipo="guardar">
            @csrf
            <div>
                <label class="sg-label">Categoría *</label>
                <div class="flex gap-2 min-w-0">
                    <select name="id_concepto" id="input-categoria" required onchange="actualizarBotonEditarCategoria()" class="sg-input min-w-0 flex-1">
                        <option value="">Elegí la categoría...</option>
                        @foreach ($categoriasManuales as $categoria)
                            <option value="{{ $categoria->id_concepto }}" @selected((int) old('id_concepto') === $categoria->id_concepto)>{{ $categoria->nombre_concepto }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirCategoria(false)" title="Agregar categoría nueva" class="sg-btn sg-btn--borde sg-btn--icono shrink-0"><x-sg.icono nombre="plus" /></button>
                    <button type="button" id="boton-editar-categoria" onclick="abrirCategoria(true)" disabled title="Editar el nombre de la categoría elegida" class="sg-btn sg-btn--borde sg-btn--icono shrink-0 disabled:opacity-40"><x-sg.icono nombre="pencil" /></button>
                </div>
            </div>
            <div class="lg:col-span-1">
                <label class="sg-label">Concepto *</label>
                <input type="text" maxlength="150" name="concepto" value="{{ old('concepto') }}" required placeholder="Ej: Resmas de hojas A4 y lapiceras" class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Beneficiario / proveedor</label>
                <input type="text" maxlength="100" name="beneficiario" value="{{ old('beneficiario') }}" placeholder="Ej: Librería Central" class="sg-input w-full">
            </div>
            <div>
                <label class="sg-label">Monto *</label>
                <div class="sg-campo-icono">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-bold text-slate-400">$</span>
                    <input data-miles data-max-valor="9999999.99" name="monto" value="{{ old('monto') }}" required placeholder="0" class="sg-input w-full !pl-8">
                </div>
            </div>
            <div>
                <label class="sg-label">Fecha *</label>
                <input type="date" name="fecha_movimiento" value="{{ old('fecha_movimiento', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required class="sg-input w-full">
            </div>
            <div class="lg:col-span-2 flex justify-end">
                <button type="submit" class="sg-btn sg-btn--peligro-lleno">
                    <x-sg.icono nombre="trending-down" /> Registrar egreso
                </button>
            </div>
        </form>

        @if ($egresosPorCategoria->isNotEmpty())
            {{-- En qué se usó el dinero este mes --}}
            <div class="px-6 pb-6">
                <p class="sg-label mb-2">Egresos del mes por categoría</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($egresosPorCategoria as $item)
                        <a href="{{ route('director.caja.index', ['mes' => $mes->format('Y-m'), 'filtro' => $item['id']]) }}"
                           class="rounded-full border border-rose-100 bg-rose-50 px-3 py-1 text-xs text-rose-700 hover:border-rose-300">
                            {{ $item['nombre'] }}: <strong>$ {{ number_format($item['total'], 0, ',', '.') }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="book" /></span>
                <div><h3>Planilla de movimientos de caja</h3><p>{{ \App\Support\FechaEsp::mesAnio($mes) }}{{ $filtroNombre ? ' · ' . $filtroNombre : '' }}</p></div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <form method="GET" class="flex items-center gap-2">
                    {{-- Ver todo, solo ingresos, solo egresos, o una categoría de egreso --}}
                    <select name="filtro" onchange="this.form.submit()" class="sg-input sg-input--chico w-auto">
                        <option value="">Todos los movimientos</option>
                        <option value="Ingreso" @selected($filtro === 'Ingreso')>Solo ingresos</option>
                        <option value="Egreso" @selected($filtro === 'Egreso')>Solo egresos</option>
                        <optgroup label="Egresos por categoría">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id_concepto }}" @selected((string) $filtro === (string) $categoria->id_concepto)>{{ $categoria->nombre_concepto }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <input type="month" name="mes" value="{{ $mes->format('Y-m') }}" onchange="this.form.submit()" class="sg-input sg-input--chico w-auto">
                </form>
                <a href="{{ route('director.caja.pdf', ['mes' => $mes->format('Y-m')]) }}" target="_blank"
                   class="sg-btn sg-btn--azul sg-btn--chico">
                    <x-sg.icono nombre="printer" /> PDF
                </a>
                <a href="{{ route('director.caja.excel', ['mes' => $mes->format('Y-m')]) }}"
                   class="sg-btn sg-btn--verde sg-btn--chico">
                    <x-sg.icono nombre="download" /> Excel
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>N°</th>
                        <th>Fecha y hora</th>
                        <th>Concepto</th>
                        <th>Tipo</th>
                        <th>Registrado por</th>
                        <th class="text-right">Ingreso</th>
                        <th class="text-right">Egresos</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($movimientos as $i => $mov)
                        @php $esIngreso = $mov->tipo === 'Ingreso'; @endphp
                        <tr>
                            <td><span class="inline-grid h-7 w-7 place-items-center rounded-lg bg-slate-100 text-xs font-bold text-slate-500">{{ str_pad($movimientos->firstItem() + $i, 2, '0', STR_PAD_LEFT) }}</span></td>
                            <td class="whitespace-nowrap">{{ $mov->fecha_movimiento->format('d/m') }} · {{ $mov->fecha_movimiento->format('H:i') }}</td>
                            <td>
                                <span class="font-medium text-slate-800">{{ $mov->concepto->nombre_concepto }}</span>
                                @if ($mov->descripcion_detalle)
                                    <p class="text-xs text-slate-400">{{ $mov->descripcion_detalle }}</p>
                                @endif
                                @if ($mov->beneficiario)
                                    <p class="text-xs text-slate-500">Beneficiario: <strong>{{ $mov->beneficiario }}</strong></p>
                                @endif
                            </td>
                            <td>
                                <span class="sg-badge {{ $esIngreso ? 'sg-badge--verde' : 'sg-badge--rojo' }}">
                                    {{ $mov->tipo }}
                                </span>
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-2 whitespace-nowrap">
                                    <span class="sg-avatar !h-7 !w-7 !rounded-lg !text-[10px]">{{ mb_substr($mov->secretarioRegistra->nombre, 0, 1) }}{{ mb_substr($mov->secretarioRegistra->apellido, 0, 1) }}</span>
                                    <span class="text-slate-600 text-xs">{{ $mov->secretarioRegistra->nombre }} {{ $mov->secretarioRegistra->apellido }}</span>
                                    <span class="sg-badge sg-badge--sin-punto !py-0.5 {{ $mov->secretarioRegistra->usuario?->esDirector() ? 'sg-badge--amarillo' : 'sg-badge--gris' }}">
                                        {{ $mov->secretarioRegistra->usuario?->esDirector() ? 'Dir.' : 'Sec.' }}
                                    </span>
                                </span>
                            </td>
                            <td class="text-right font-display font-bold !text-emerald-600 whitespace-nowrap">{{ $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                            <td class="text-right font-display font-bold !text-rose-500 whitespace-nowrap">{{ ! $esIngreso ? '$ ' . number_format($mov->monto, 0, ',', '.') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="book">No hay movimientos registrados en este período.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
                @if ($movimientos->isNotEmpty())
                    <tfoot>
                        <tr class="bg-blue-50 font-bold text-slate-700">
                            <td colspan="5" class="px-6 py-4 text-right text-xs uppercase tracking-wider text-[#0B3D91]">Totales del mes</td>
                            <td class="px-6 py-4 text-right font-display text-emerald-600 whitespace-nowrap">$ {{ number_format($resumenMes['ingresos'], 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right font-display text-rose-500 whitespace-nowrap">$ {{ number_format($resumenMes['egresos'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        @if ($movimientos->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $movimientos->links() }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-10 px-10 py-8 text-center text-sm text-slate-500 border-t border-slate-100">
            <p class="border-t-2 border-dashed border-slate-200 pt-2">Secretaria</p>
            <p class="border-t-2 border-dashed border-slate-200 pt-2">Directora — Directora General</p>
        </div>
    </div>
    {{-- Mini ventana: agregar o editar una categoría de egreso --}}
    <div id="modal-categoria" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[80] items-center justify-center p-4" onclick="if (event.target === this) cerrarCategoria()">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 id="titulo-categoria" class="font-display font-extrabold text-lg text-[#0B3D91]">Nueva categoría de egreso</h3>
                <button type="button" onclick="cerrarCategoria()" class="text-slate-400 hover:text-slate-700 text-xl" title="Cerrar">✕</button>
            </div>
            <label class="sg-label">Nombre *</label>
            <input type="text" id="input-nombre-categoria" maxlength="60" placeholder="Ej: Fotocopias" class="sg-input w-full"
                   onkeydown="if (event.key === 'Enter') { event.preventDefault(); guardarCategoria(); }">
            <p id="ayuda-categoria" class="text-xs text-slate-400 mt-1"></p>
            <p id="error-categoria" class="hidden text-xs text-red-600 mt-2"></p>
            <div class="flex justify-end gap-3 mt-5">
                <button type="button" onclick="cerrarCategoria()" class="sg-btn sg-btn--borde">Cancelar</button>
                <button type="button" onclick="guardarCategoria()" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar</button>
            </div>
        </div>
    </div>

    @php
        $rutasCategoriaJs = ['store' => route('director.caja.categorias.store'), 'base' => url('/director/caja/categorias')];
    @endphp
    <script>
        const rutasCategoria = @json($rutasCategoriaJs);
        const selectCategoria = document.getElementById('input-categoria');
        let editandoCategoria = false;

        // El lápiz solo se habilita con una categoría elegida.
        function actualizarBotonEditarCategoria() {
            document.getElementById('boton-editar-categoria').disabled = !selectCategoria.value;
        }
        function abrirCategoria(editar) {
            editandoCategoria = editar;
            const elegida = selectCategoria.selectedOptions[0];
            document.getElementById('titulo-categoria').textContent = editar ? 'Editar categoría' : 'Nueva categoría de egreso';
            document.getElementById('input-nombre-categoria').value = editar ? elegida.textContent.trim() : '';
            document.getElementById('ayuda-categoria').textContent = editar
                ? 'Los egresos ya cargados en esta categoría pasan al nombre nuevo.'
                : 'Queda disponible para todos los egresos.';
            document.getElementById('error-categoria').classList.add('hidden');
            const modal = document.getElementById('modal-categoria');
            modal.classList.remove('hidden'); modal.classList.add('flex');
            document.getElementById('input-nombre-categoria').focus();
        }
        function cerrarCategoria() {
            const modal = document.getElementById('modal-categoria');
            modal.classList.add('hidden'); modal.classList.remove('flex');
        }
        async function guardarCategoria() {
            const error = document.getElementById('error-categoria');
            const nombre = document.getElementById('input-nombre-categoria').value.trim();
            if (nombre.length < 3) { error.textContent = 'Escribí un nombre de al menos 3 letras.'; return error.classList.remove('hidden'); }
            const r = await fetch(editandoCategoria ? `${rutasCategoria.base}/${selectCategoria.value}` : rutasCategoria.store, {
                method: editandoCategoria ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('[name=_token]').value },
                body: JSON.stringify({ nombre }),
            });
            const res = await r.json();
            if (!r.ok) { error.textContent = Object.values(res.errors || {})[0]?.[0] || 'No se pudo guardar.'; return error.classList.remove('hidden'); }
            if (editandoCategoria) {
                selectCategoria.selectedOptions[0].textContent = res.nombre;
                // También en el filtro de la planilla.
                document.querySelectorAll(`select[name="filtro"] option[value="${res.id}"]`).forEach(o => o.textContent = res.nombre);
            } else {
                selectCategoria.add(new Option(res.nombre, res.id, true, true));
            }
            actualizarBotonEditarCategoria();
            cerrarCategoria();
        }
        actualizarBotonEditarCategoria();
    </script>
@endsection
