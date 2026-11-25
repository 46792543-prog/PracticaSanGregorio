@extends('layouts.director')

@section('titulo', 'Configuración de Cuotas')
@section('subtitulo', 'Monto, vencimiento e interés por mora aplicados automáticamente a todos los alumnos activos')

@section('contenido')
    <x-sg.encabezado titulo="Configuración de Cuotas" subtitulo="Al guardar, el sistema genera automáticamente la cuota de cada mes para todos los alumnos con inscripción activa en el año elegido." eyebrow="Finanzas" icono="settings" />

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="relative bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#1565C0] px-6 py-4 overflow-hidden">
            <span class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[10px] border-[#FFC107]/25"></span>
            <h2 class="relative text-white font-display font-bold flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-[#FFD54F]"><x-sg.icono nombre="settings" class="h-5 w-5" /></span> Configurar cuota mensual</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion-cuotas.store') }}" id="form-configuracion-cuota">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">PERÍODO LECTIVO *</label>
                        <select name="id_anio_lectivo" id="select-anio-config" required class="sg-input w-full">
                            @foreach ($anios as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === old('id_anio_lectivo', $anioActivoId))>{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">DÍA DE VENCIMIENTO (1-31) *</label>
                        <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" data-max-valor="31" name="dia_vencimiento" id="input-dia-vencimiento" value="10" required class="sg-input w-full">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-1">
                    <div>
                        <label class="sg-label">COBRAR CUOTAS DESDE *</label>
                        <select name="mes_desde" id="input-mes-desde" required class="sg-input w-full">
                            @foreach ($meses as $mes)
                                <option value="{{ $mes->id_mes }}" @selected((int) $mes->id_mes === 3)>{{ $mes->nombre_mes }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">HASTA *</label>
                        <select name="mes_hasta" id="input-mes-hasta" required class="sg-input w-full">
                            @foreach ($meses as $mes)
                                <option value="{{ $mes->id_mes }}" @selected((int) $mes->id_mes === 12)>{{ $mes->nombre_mes }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mb-4">Todos los alumnos deben la cuota de cada mes de este período, sin importar el día en que se los cargó en el sistema.</p>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">MONTO DE LA CUOTA MENSUAL *</label>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" max="1000000" data-max-digitos="7" data-max-valor="1000000" name="monto_mensual" id="input-monto-mensual" required class="sg-input w-full">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Valor desde el primer mes del período. Los aumentos se cargan más abajo.</p>
                    </div>
                    <div>
                        <label class="sg-label !text-amber-600">INTERÉS POR MORA (%) *</label>
                        <div class="flex items-center gap-1">
                            <input type="number" step="0.01" min="0" max="100" name="porcentaje_interes_mora" id="input-interes-mora" value="0" required class="sg-input w-full border-amber-200">
                            <span class="text-slate-400 text-sm">%</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Se suma una sola vez apenas la cuota vence sin pagar. Si lo cambiás, se recalcula en todas las cuotas vencidas impagas.</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sg-label">CONCEPTO</label>
                    <input type="text" name="concepto" data-solo="letras" data-max-len="120" maxlength="120" placeholder="Cuota mensual" class="sg-input w-full sm:w-1/2">
                </div>

                <p class="sg-aviso sg-aviso--info text-xs mb-4">
                    Al guardar, se genera automáticamente la cuota de cada mes (del período elegido arriba) para todos los alumnos con inscripción activa en el período elegido. No se duplican cuotas que ya existan, y las que todavía no se pagaron (también las de meses anteriores) pasan al monto nuevo; las ya pagadas no se tocan.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Guardar y generar cuotas</button>
                </div>
            </form>
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="chart" /></span>
                <div><h3>Aumentos de cuota</h3><p>Desde el mes elegido en adelante, la cuota pasa al monto nuevo</p></div>
            </div>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion-cuotas.aumentos.store') }}" class="grid sm:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="sg-label">PERÍODO LECTIVO *</label>
                    <select name="aumento_id_anio_lectivo" id="select-anio-aumento" required class="sg-input w-full">
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id_anio_lectivo }}" @selected((int) $anio->id_anio_lectivo === (int) old('aumento_id_anio_lectivo', $anioActivoId))>{{ $anio->anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">A PARTIR DE *</label>
                    <select name="aumento_mes_desde" required class="sg-input w-full">
                        @foreach ($meses as $mes)
                            <option value="{{ $mes->id_mes }}" @selected((int) $mes->id_mes === (int) old('aumento_mes_desde', now()->month))>{{ $mes->nombre_mes }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">MONTO NUEVO *</label>
                    <div class="flex items-center gap-1">
                        <span class="text-slate-400 text-sm">$</span>
                        <input type="number" step="0.01" min="0.01" max="1000000" data-max-digitos="7" data-max-valor="1000000" name="aumento_monto" value="{{ old('aumento_monto') }}" required class="sg-input w-full">
                    </div>
                </div>
                <div class="flex sm:justify-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Cargar aumento</button>
                </div>
            </form>

            <p class="sg-aviso sg-aviso--info text-xs mt-4">
                Las cuotas que se deben no quedan al precio viejo: si un alumno debe marzo y la cuota subió en julio, marzo se cobra al valor de julio, y la mora se calcula sobre ese monto. Las cuotas ya pagadas no se tocan. Si el aumento es de un mes que todavía no llegó, las cuotas adeudadas suben solas cuando llegue ese mes.
            </p>

            <div class="overflow-x-auto mt-4">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Año lectivo</th>
                            <th>Desde</th>
                            <th>Monto</th>
                            <th style="text-align:center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="sg-filas">
                        @php $hayAumentos = false; @endphp
                        @foreach ($configuraciones->sortByDesc(fn ($c) => $c->anioLectivo->anio) as $configuracion)
                            @foreach ($configuracion->aumentos as $aumento)
                                @php $hayAumentos = true; $nombreMesAumento = $meses->firstWhere('id_mes', $aumento->mes_desde)?->nombre_mes; @endphp
                                <tr>
                                    <td class="font-semibold text-slate-700">{{ $configuracion->anioLectivo->anio }}</td>
                                    <td class="text-slate-500">{{ $nombreMesAumento }}</td>
                                    <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($aumento->monto, 0, ',', '.') }}</td>
                                    <td><div class="flex justify-center gap-1">
                                        {{-- Lápiz: muestra/oculta la fila de edición de abajo --}}
                                        <button type="button" onclick="document.getElementById('editar-aumento-{{ $aumento->id_aumento_cuota }}').classList.toggle('hidden')"
                                                class="h-8 w-8 inline-grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar aumento"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                        <form method="POST" action="{{ route('director.configuracion-cuotas.aumentos.destroy', $aumento) }}" class="inline"
                                              onsubmit="return confirm('¿Eliminar el aumento desde {{ $nombreMesAumento }}? Las cuotas impagas vuelven al precio anterior.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="h-8 w-8 inline-grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar aumento"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                        </form>
                                    </div></td>
                                </tr>
                                {{-- Fila de edición (oculta hasta tocar el lápiz) --}}
                                <tr id="editar-aumento-{{ $aumento->id_aumento_cuota }}" class="hidden bg-slate-50">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('director.configuracion-cuotas.aumentos.update', $aumento) }}" class="flex flex-wrap items-end gap-3">
                                            @csrf
                                            @method('PUT')
                                            <div>
                                                <label class="sg-label">A PARTIR DE</label>
                                                <select name="aumento_mes_desde" class="sg-input">
                                                    @foreach ($meses as $mes)
                                                        <option value="{{ $mes->id_mes }}" @selected((int) $mes->id_mes === $aumento->mes_desde)>{{ $mes->nombre_mes }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="sg-label">MONTO</label>
                                                <input type="number" step="0.01" min="0.01" max="1000000" data-max-digitos="7" data-max-valor="1000000" name="aumento_monto" value="{{ (float) $aumento->monto }}" required class="sg-input">
                                            </div>
                                            <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check" /> Guardar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        @unless ($hayAumentos)
                            <tr><td colspan="4"><x-sg.vacio icono="chart">Todavía no se cargó ningún aumento.</x-sg.vacio></td></tr>
                        @endunless
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--verde"><x-sg.icono nombre="chart" /></span>
                <div><h3>Configuraciones guardadas</h3><p>Una por período lectivo</p></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Año lectivo</th>
                        <th>Monto mensual</th>
                        <th>Período</th>
                        <th>Día de vencimiento</th>
                        <th>Interés por mora</th>
                        <th>Concepto</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($configuraciones as $configuracion)
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $configuracion->anioLectivo->anio }}</td>
                            <td class="font-display font-bold text-[#0B3D91]">
                                $ {{ number_format($configuracion->monto_mensual, 0, ',', '.') }}
                                @if ($configuracion->aumentos->isNotEmpty())
                                    <span class="block text-xs font-normal text-slate-500">Vigente hoy: $ {{ number_format($configuracion->montoVigente(), 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $meses->firstWhere('id_mes', $configuracion->mes_desde)?->nombre_mes }} a {{ $meses->firstWhere('id_mes', $configuracion->mes_hasta)?->nombre_mes }}</td>
                            <td class="text-slate-500">Día {{ $configuracion->dia_vencimiento }}</td>
                            <td><span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">{{ rtrim(rtrim($configuracion->porcentaje_interes_mora, '0'), '.') }}%</span></td>
                            <td class="text-slate-500">{{ $configuracion->concepto ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-sg.vacio icono="settings">Todavía no se configuró ninguna cuota.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $configuracionesJs = $configuraciones->mapWithKeys(fn ($c) => [$c->id_anio_lectivo => [
            'monto_mensual' => (float) $c->monto_mensual,
            'mes_desde' => $c->mes_desde,
            'mes_hasta' => $c->mes_hasta,
            'dia_vencimiento' => $c->dia_vencimiento,
            'porcentaje_interes_mora' => (float) $c->porcentaje_interes_mora,
        ]]);
    @endphp

    <script>
        (function () {
            const configuraciones = @json($configuracionesJs);
            const selectAnio = document.getElementById('select-anio-config');
            const montoInput = document.getElementById('input-monto-mensual');
            const diaInput = document.getElementById('input-dia-vencimiento');
            const interesInput = document.getElementById('input-interes-mora');

            function precargarConfiguracion() {
                const config = configuraciones[selectAnio.value];
                if (! config) return;
                montoInput.value = config.monto_mensual;
                document.getElementById('input-mes-desde').value = config.mes_desde;
                document.getElementById('input-mes-hasta').value = config.mes_hasta;
                diaInput.value = config.dia_vencimiento;
                interesInput.value = config.porcentaje_interes_mora;
            }

            selectAnio.addEventListener('change', precargarConfiguracion);
            precargarConfiguracion();
        })();
    </script>
@endsection
