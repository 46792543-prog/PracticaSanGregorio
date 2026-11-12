@extends('layouts.director')

@section('titulo', 'Configuración de Matrícula')
@section('subtitulo', 'Monto, mes de habilitación y día límite de pago, generados automáticamente a todos los alumnos activos')

@section('contenido')
    <x-sg.encabezado titulo="Configuración de Matrícula" subtitulo="Al guardar, el sistema genera automáticamente la matrícula para todos los alumnos con inscripción activa en el año elegido." eyebrow="Finanzas" icono="settings" />

    <div class="sg-card sg-revelar overflow-hidden mb-8">
        <div class="relative bg-gradient-to-r from-[#082A66] via-[#0B3D91] to-[#1565C0] px-6 py-4 overflow-hidden">
            <span class="absolute -right-8 -top-10 h-28 w-28 rounded-full border-[10px] border-[#FFC107]/25"></span>
            <h2 class="relative text-white font-display font-bold flex items-center gap-3"><span class="grid h-9 w-9 place-items-center rounded-xl bg-white/15 text-[#FFD54F]"><x-sg.icono nombre="settings" class="h-5 w-5" /></span> Configurar matrícula</h2>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('director.configuracion-matricula.store') }}" id="form-configuracion-matricula">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">PERÍODO LECTIVO *</label>
                        <select name="id_anio_lectivo" id="select-anio-config-matricula" required class="sg-input w-full">
                            @foreach ($anios as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === old('id_anio_lectivo', $anioActivoId))>{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">MONTO DE LA MATRÍCULA *</label>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" id="input-monto-matricula" required class="sg-input w-full">
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="sg-label">MES DESDE EL QUE SE HABILITA EL PAGO *</label>
                        <select name="mes_habilitacion" id="input-mes-habilitacion" required class="sg-input w-full">
                            @foreach ($meses as $mes)
                                <option value="{{ $mes->id_mes }}">{{ $mes->nombre_mes }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">DÍA LÍMITE DE PAGO (1-28) *</label>
                        <input type="number" name="dia_limite" id="input-dia-limite" min="1" max="28" value="10" required class="sg-input w-full">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sg-label">CONCEPTO</label>
                    <input type="text" name="concepto" data-solo="alfanumerico" data-max-len="20" maxlength="20" placeholder="Matrícula" class="sg-input w-full sm:w-1/2">
                </div>

                <p class="sg-aviso sg-aviso--info text-xs mb-4">
                    Al guardar, se genera automáticamente la matrícula (una sola por alumno, no una por mes) para todos los alumnos con inscripción activa en el período elegido. No se duplican las que ya existan. Hasta que la paguen, el sistema no les va a dejar pagar cuotas de ese año.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="sg-btn sg-btn--azul"><x-sg.icono nombre="check-circle" /> Guardar y generar matrículas</button>
                </div>
            </form>
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
                        <th>Monto</th>
                        <th>Habilitada desde</th>
                        <th>Día límite</th>
                        <th>Concepto</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($configuraciones as $configuracion)
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $configuracion->anioLectivo->anio }}</td>
                            <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($configuracion->monto, 0, ',', '.') }}</td>
                            <td class="text-slate-500">{{ $meses->firstWhere('id_mes', $configuracion->mes_habilitacion)?->nombre_mes }}</td>
                            <td class="text-slate-500">Día {{ $configuracion->dia_limite }}</td>
                            <td class="text-slate-500">{{ $configuracion->concepto ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-sg.vacio icono="settings">Todavía no se configuró ninguna matrícula.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @php
        $configuracionesJs = $configuraciones->mapWithKeys(fn ($c) => [$c->id_anio_lectivo => [
            'monto' => (float) $c->monto,
            'mes_habilitacion' => $c->mes_habilitacion,
            'dia_limite' => $c->dia_limite,
        ]]);
    @endphp

    <script>
        (function () {
            const configuraciones = @json($configuracionesJs);
            const selectAnio = document.getElementById('select-anio-config-matricula');
            const montoInput = document.getElementById('input-monto-matricula');
            const mesInput = document.getElementById('input-mes-habilitacion');
            const diaInput = document.getElementById('input-dia-limite');

            function precargarConfiguracion() {
                const config = configuraciones[selectAnio.value];
                if (! config) return;
                montoInput.value = config.monto;
                mesInput.value = config.mes_habilitacion;
                diaInput.value = config.dia_limite;
            }

            selectAnio.addEventListener('change', precargarConfiguracion);
            precargarConfiguracion();
        })();
    </script>
@endsection
