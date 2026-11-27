@extends('layouts.director')

@section('titulo', 'Liquidación de horas')
@section('subtitulo', 'Valor de la hora cátedra y cuánto cobra cada docente por mes')

@section('contenido')
    <x-sg.encabezado titulo="Liquidación de horas" subtitulo="Cada docente cobra sus horas cátedra designadas (horas semanales × 4 × valor de la hora según su carrera y tipo), menos las inasistencias injustificadas." eyebrow="Finanzas" icono="clock" />

    {{-- 1) Valor de la hora cátedra por carrera y tipo de designación --}}
    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="cash" /></span>
                <div><h3>Valor de la hora cátedra</h3><p>Por carrera y tipo de designación, desde el mes en que rige</p></div>
            </div>
        </div>
        <div class="p-6">
            {{-- Valores que rigen en el mes elegido abajo. Tocar una celda la elige en el formulario. --}}
            <div class="overflow-x-auto mb-6">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            @foreach ($tipos as $tipo)<th style="text-align:center">{{ $tipo }}</th>@endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($carreras as $carrera)
                            <tr>
                                <td class="font-semibold text-slate-700">{{ $carrera->nombre_carrera }}</td>
                                @foreach ($tipos as $tipo)
                                    @php $vigente = $valoresVigentes[$carrera->id_carrera][$tipo]; @endphp
                                    <td class="text-center">
                                        <button type="button" onclick="elegirValor({{ $carrera->id_carrera }}, '{{ $tipo }}', {{ $vigente ? (float) $vigente->monto : 'null' }})"
                                                title="Cargar o cambiar el valor de {{ $tipo }} en {{ $carrera->nombre_carrera }}"
                                                class="w-full rounded-lg px-2 py-1.5 transition {{ $vigente ? 'hover:bg-blue-50' : 'bg-amber-50 hover:bg-amber-100' }}">
                                            <span class="block font-display font-bold {{ $vigente ? 'text-[#0B3D91]' : 'text-amber-600 text-sm' }}">{{ $vigente ? '$ ' . number_format($vigente->monto, 0, ',', '.') : 'Sin valor' }}</span>
                                            @if ($vigente)<span class="block text-[11px] text-slate-400">desde {{ $vigente->vigente_desde->translatedFormat('M Y') }}</span>@endif
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tope: valor máximo que se puede cargar por hora cátedra --}}
            <form method="POST" action="{{ route('director.liquidacion-horas.tope.update') }}" class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl bg-slate-50 px-4 py-2 text-sm"
                  data-confirm="¿Cambiar el valor máximo por hora cátedra?" data-confirm-tipo="guardar">
                @csrf
                @method('PUT')
                <span class="text-slate-500">Valor máximo por hora cátedra:</span>
                <span class="text-slate-400">$</span>
                <input data-miles data-max-valor="1000000" name="tope_valor_hora_catedra" value="{{ $tope }}" required class="sg-input !w-32 !py-1.5">
                <button type="submit" class="sg-btn sg-btn--azul !py-1.5 !text-xs"><x-sg.icono nombre="check" class="h-4 w-4" /> Guardar valor máximo</button>
            </form>

            <form method="POST" action="{{ route('director.liquidacion-horas.valores.store') }}" id="form-valor-hora" class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                @csrf
                <div>
                    <label class="sg-label">CARRERA *</label>
                    <select name="id_carrera" id="valor-carrera" required class="sg-input w-full">
                        <option value="">Elegí la carrera...</option>
                        @foreach ($carreras as $carrera)
                            <option value="{{ $carrera->id_carrera }}" @selected((int) old('id_carrera') === $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">TIPO DE DESIGNACIÓN *</label>
                    <select name="tipo" id="valor-tipo" required class="sg-input w-full">
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $tipo }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">VALOR DE LA HORA CÁTEDRA *</label>
                    <div class="flex items-center gap-1">
                        <span class="text-slate-400 text-sm">$</span>
                        <input data-miles data-max-valor="{{ $tope }}" name="monto" id="valor-monto" value="{{ old('monto') }}" required placeholder="Ej: 1.000 (máx. {{ number_format($tope, 0, ',', '.') }})" class="sg-input w-full">
                    </div>
                </div>
                <div>
                    <label class="sg-label">RIGE DESDE *</label>
                    <input type="month" name="vigente_desde" value="{{ old('vigente_desde', $periodo->format('Y-m')) }}" required class="sg-input w-full">
                </div>
                <div class="flex lg:justify-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Guardar valor</button>
                </div>
            </form>

            <p class="sg-aviso sg-aviso--info text-xs mt-4"><span>
                Cada docente cobra según la carrera de la materia que dicta: si da clases en dos carreras, cada materia se paga con el valor de su carrera.
                Para un aumento, cargá el nuevo valor desde el mes en que rige (los meses anteriores siguen con el valor viejo). Misma carrera, tipo y mes: se corrige el valor.
            </span></p>

            @if ($historial->isNotEmpty())
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm font-semibold text-[#0B3D91]">Ver historial de valores ({{ $historial->count() }})</summary>
                    <div class="overflow-x-auto mt-3">
                        <table class="sg-tabla">
                            <thead><tr><th>Carrera</th><th>Tipo</th><th>Rige desde</th><th>Valor</th><th style="text-align:center">Acciones</th></tr></thead>
                            <tbody class="sg-filas">
                                @foreach ($historial as $valor)
                                    <tr>
                                        <td class="text-slate-600">{{ $valor->carrera->nombre_carrera }}</td>
                                        <td class="font-semibold text-slate-700">{{ $valor->tipo }}</td>
                                        <td class="text-slate-500 capitalize">{{ $valor->vigente_desde->translatedFormat('F Y') }}</td>
                                        <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($valor->monto, 2, ',', '.') }}</td>
                                        <td><div class="flex justify-center gap-1">
                                            {{-- Lápiz: muestra/oculta la fila para editar este valor --}}
                                            <button type="button" onclick="document.getElementById('editar-valor-{{ $valor->getKey() }}').classList.toggle('hidden')"
                                                    class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar valor"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                            <form method="POST" action="{{ route('director.liquidacion-horas.valores.destroy', $valor) }}"
                                                  data-confirm="¿Eliminar el valor de {{ $valor->tipo }} en {{ $valor->carrera->nombre_carrera }} desde {{ $valor->vigente_desde->translatedFormat('F Y') }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="h-8 w-8 grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar valor"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                            </form>
                                        </div></td>
                                    </tr>
                                    {{-- Fila de edición (oculta hasta tocar el lápiz) --}}
                                    <tr id="editar-valor-{{ $valor->getKey() }}" class="hidden bg-slate-50">
                                        <td colspan="5">
                                            <form method="POST" action="{{ route('director.liquidacion-horas.valores.update', $valor) }}" class="flex flex-wrap items-end gap-3"
                                                  data-confirm="¿Guardar el nuevo valor de {{ $valor->tipo }} en {{ $valor->carrera->nombre_carrera }}?" data-confirm-tipo="guardar">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label class="sg-label">RIGE DESDE</label>
                                                    <input type="month" name="vigente_desde" value="{{ $valor->vigente_desde->format('Y-m') }}" required class="sg-input">
                                                </div>
                                                <div>
                                                    <label class="sg-label">VALOR DE LA HORA</label>
                                                    <div class="flex items-center gap-1">
                                                        <span class="text-slate-400 text-sm">$</span>
                                                        <input data-miles data-max-valor="{{ $tope }}" name="monto" value="{{ (float) $valor->monto }}" required class="sg-input">
                                                    </div>
                                                </div>
                                                <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        </div>
    </div>

    <script>
        // Tocar una celda de la tabla: elige esa carrera y tipo en el formulario para cargar o cambiar su valor.
        function elegirValor(idCarrera, tipo, monto) {
            document.getElementById('valor-carrera').value = idCarrera;
            document.getElementById('valor-tipo').value = tipo;
            const campo = document.getElementById('valor-monto');
            sgMiles(campo, monto ?? ''); // con el punto de miles
            campo.focus();
            document.getElementById('form-valor-hora').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    </script>

    {{-- 2) Liquidación del mes por docente --}}
    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head flex flex-wrap items-center justify-between gap-3">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="chart" /></span>
                <div><h3>Liquidación de <span class="capitalize">{{ $periodo->translatedFormat('F Y') }}</span></h3><p>Según las designaciones vigentes en el mes</p></div>
            </div>
            <form method="GET" class="flex items-end gap-2">
                <input type="month" name="periodo" value="{{ $periodo->format('Y-m') }}" required onchange="this.form.submit()" class="sg-input">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th style="text-align:center">Horas semanales</th>
                        <th style="text-align:center">Horas del mes</th>
                        <th style="text-align:center">Faltas</th>
                        <th style="text-align:right">Descuento</th>
                        <th style="text-align:right">Total a cobrar</th>
                        <th style="text-align:center">Pago</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($liquidacion as $fila)
                        @php
                            $profesor = $fila['profesor'];
                            $pago = $pagos[$profesor->id_profesor] ?? null;
                        @endphp
                        <tr class="cursor-pointer" onclick="document.getElementById('detalle-{{ $profesor->id_profesor }}').classList.toggle('hidden')" title="Ver detalle por materia">
                            <td>
                                <p class="font-semibold text-slate-700">{{ $profesor->apellido }}, {{ $profesor->nombre }}</p>
                                <p class="text-xs text-slate-400">{{ $fila['detalle']->count() }} designación{{ $fila['detalle']->count() === 1 ? '' : 'es' }} · tocá para ver el detalle</p>
                            </td>
                            <td class="text-center text-slate-600">{{ $fila['horas_semanales'] }} h</td>
                            <td class="text-center text-slate-600">{{ $fila['horas_mes'] }} h</td>
                            <td class="text-center {{ $fila['horas_falta'] ? 'text-red-600 font-semibold' : 'text-slate-400' }}">{{ $fila['horas_falta'] ? $fila['horas_falta'] . ' h' : '—' }}</td>
                            <td class="!text-right whitespace-nowrap {{ $fila['descuento'] ? 'text-red-600' : 'text-slate-400' }}">{{ $fila['descuento'] ? '− $ ' . number_format($fila['descuento'], 0, ',', '.') : '—' }}</td>
                            <td class="!text-right whitespace-nowrap">
                                @if ($fila['tipos_sin_valor']->isNotEmpty())
                                    <span class="sg-badge sg-badge--amarillo" title="Falta cargar el valor de la hora cátedra">Falta valor: {{ $fila['tipos_sin_valor']->join(', ') }}</span>
                                @else
                                    <span class="font-display font-bold text-slate-800">$ {{ number_format($fila['total'], 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-center" onclick="event.stopPropagation()">
                                @if ($pago)
                                    <span class="sg-badge sg-badge--verde">{{ $pago->estadoPago->nombre_estado }}</span>
                                @else
                                    <a href="{{ route('director.pagos-docentes.index', ['nuevo' => $profesor->id_profesor, 'periodo_nuevo' => $periodo->format('Y-m')]) }}" class="sg-btn sg-btn--borde !py-1 !px-3 text-xs">Registrar pago</a>
                                @endif
                            </td>
                        </tr>
                        {{-- Detalle por materia (oculto hasta tocar la fila) --}}
                        <tr id="detalle-{{ $profesor->id_profesor }}" class="hidden bg-slate-50">
                            <td colspan="7">
                                <table class="w-full text-sm">
                                    <thead class="text-[11px] uppercase tracking-wider text-slate-400">
                                        <tr><th class="text-left py-1">Materia</th><th class="text-left">Tipo</th><th class="text-center">Semanales</th><th class="text-center">Mes (× 4)</th><th class="text-right">Valor hora</th><th class="text-center">Faltas</th><th class="text-right">Descuento</th><th class="text-right">Subtotal</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($fila['detalle'] as $d)
                                            <tr class="border-t border-slate-200">
                                                <td class="py-1.5"><span class="font-semibold text-slate-700">{{ $d['materia'] }}</span> <span class="text-xs text-slate-400">{{ $d['carrera'] }}</span></td>
                                                <td>{{ $d['tipo'] }}</td>
                                                <td class="text-center">{{ $d['horas_semanales'] }} h</td>
                                                <td class="text-center">{{ $d['horas_mes'] }} h</td>
                                                <td class="text-right">{{ $d['valor_hora'] !== null ? '$ ' . number_format($d['valor_hora'], 0, ',', '.') : '—' }}</td>
                                                <td class="text-center text-xs">
                                                    {{-- Fechas de las faltas: tachadas las justificadas (no descuentan) --}}
                                                    @forelse ($d['faltas'] as $f)
                                                        <span class="{{ $f['justificada'] ? 'line-through text-slate-400' : 'text-red-600' }}" title="{{ $f['tipo'] }}{{ $f['justificada'] ? ' (no descuenta)' : ' (descuenta)' }}">{{ $f['fecha'] }} ({{ $f['horas'] }} h)</span>
                                                    @empty
                                                        —
                                                    @endforelse
                                                </td>
                                                <td class="text-right text-red-600">{{ $d['descuento'] ? '− $ ' . number_format($d['descuento'], 0, ',', '.') : '—' }}</td>
                                                <td class="text-right font-semibold">{{ $d['valor_hora'] !== null ? '$ ' . number_format($d['subtotal'], 0, ',', '.') : '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="clock">Ningún docente tiene horas cátedra designadas en este mes.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
                @if ($liquidacion->isNotEmpty())
                    <tfoot>
                        <tr class="bg-slate-50 font-bold">
                            <td>Total del mes</td>
                            <td class="text-center">{{ $liquidacion->sum('horas_semanales') }} h</td>
                            <td class="text-center">{{ $liquidacion->sum('horas_mes') }} h</td>
                            <td class="text-center text-red-600">{{ $liquidacion->sum('horas_falta') }} h</td>
                            <td class="!text-right text-red-600">− $ {{ number_format($liquidacion->sum('descuento'), 0, ',', '.') }}</td>
                            <td class="!text-right font-display text-[#0B3D91]">$ {{ number_format($liquidacion->sum('total'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
