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
                        <label class="sg-label">DÍA DE VENCIMIENTO (1-28) *</label>
                        <input type="number" name="dia_vencimiento" id="input-dia-vencimiento" min="1" max="28" value="10" required class="sg-input w-full">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">MONTO DE LA CUOTA MENSUAL *</label>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto_mensual" id="input-monto-mensual" required class="sg-input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="sg-label !text-amber-600">INTERÉS POR MORA (%) *</label>
                        <div class="flex items-center gap-1">
                            <input type="number" step="0.01" min="0" max="100" name="porcentaje_interes_mora" id="input-interes-mora" value="0" required class="sg-input w-full border-amber-200">
                            <span class="text-slate-400 text-sm">%</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Se aplica una sola vez sobre el monto de la cuota cuando se vence sin pagar.</p>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sg-label">CONCEPTO</label>
                    <input type="text" name="concepto" data-solo="alfanumerico" data-max-len="20" maxlength="20" placeholder="Cuota mensual" class="sg-input w-full sm:w-1/2">
                </div>

                <p class="sg-aviso sg-aviso--info text-xs mb-4">
                    Al guardar, se genera automáticamente la cuota de cada mes (desde que el alumno se inscribió hasta diciembre) para todos los alumnos con inscripción activa en el período elegido. No se duplican cuotas que ya existan.
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
                <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="card" /></span>
                <div><h3>Calcular mora de cuotas vencidas</h3><p>Aplica el interés configurado a las cuotas impagas que ya vencieron</p></div>
            </div>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion-cuotas.calcular-mora') }}" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <label class="sg-label">PERÍODO LECTIVO</label>
                    <select name="id_anio_lectivo" required class="sg-input">
                        @foreach ($anios as $anio)
                            <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === $anioActivoId)>{{ $anio->anio }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="sg-btn sg-btn--amarillo"><x-sg.icono nombre="card" /> Calcular moras del día</button>
            </form>
            <p class="text-xs text-slate-400 mt-3">Revisá las cuotas pendientes de pago cuya fecha de vencimiento ya pasó y les aplica el interés por mora una sola vez. Se puede correr las veces que haga falta: a una cuota ya marcada no se le vuelve a aplicar.</p>
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
                        <th>Día de vencimiento</th>
                        <th>Interés por mora</th>
                        <th>Concepto</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($configuraciones as $configuracion)
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $configuracion->anioLectivo->anio }}</td>
                            <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($configuracion->monto_mensual, 0, ',', '.') }}</td>
                            <td class="text-slate-500">Día {{ $configuracion->dia_vencimiento }}</td>
                            <td><span class="sg-badge sg-badge--amarillo sg-badge--sin-punto">{{ rtrim(rtrim($configuracion->porcentaje_interes_mora, '0'), '.') }}%</span></td>
                            <td class="text-slate-500">{{ $configuracion->concepto ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-sg.vacio icono="settings">Todavía no se configuró ninguna cuota.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $configuracionesJs = $configuraciones->mapWithKeys(fn ($c) => [$c->id_anio_lectivo => [
            'monto_mensual' => (float) $c->monto_mensual,
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
                diaInput.value = config.dia_vencimiento;
                interesInput.value = config.porcentaje_interes_mora;
            }

            selectAnio.addEventListener('change', precargarConfiguracion);
            precargarConfiguracion();
        })();
    </script>
@endsection
