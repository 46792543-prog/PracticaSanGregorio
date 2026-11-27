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
                <div class="grid sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="sg-label">PERÍODO LECTIVO *</label>
                        <select name="id_anio_lectivo" id="select-anio-config-matricula" required class="sg-input w-full">
                            @foreach ($anios as $anio)
                                <option value="{{ $anio->id_anio_lectivo }}" @selected($anio->id_anio_lectivo === old('id_anio_lectivo', $anioActivoId))>{{ $anio->anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">CARRERA *</label>
                        <select name="id_carrera" id="select-carrera-config-matricula" class="sg-input w-full">
                            <option value="" @selected(old('id_carrera') === null)>Aplica a todas las carreras</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id_carrera }}" @selected((int) $carrera->id_carrera === (int) old('id_carrera'))>{{ $carrera->nombre_carrera }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">MONTO DE LA MATRÍCULA *</label>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 text-sm">$</span>
                            <input data-miles data-max-valor="9999999.99" name="monto" id="input-monto-matricula" required placeholder="Ej: 50.000" class="sg-input w-full">
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
                        <input type="text" inputmode="numeric" data-solo="numeros" data-max-len="2" data-max-valor="28" name="dia_limite" id="input-dia-limite" value="10" required class="sg-input w-full">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="sg-label">CONCEPTO</label>
                    <input type="text" name="concepto" data-solo="letras" data-max-len="50" maxlength="50" placeholder="Matrícula" class="sg-input w-full sm:w-1/2">
                </div>

                <p class="sg-aviso sg-aviso--info text-xs mb-4">
                    Al guardar, se genera automáticamente la matrícula (una sola por alumno, no una por mes) para los alumnos con inscripción activa en el período y la carrera elegidos (o en todas, si se eligió "Aplica a todas las carreras"). Si una carrera tiene su propia configuración, esa manda sobre la general. No se duplican las que ya existan. Hasta que la paguen, el sistema no les va a dejar pagar cuotas de ese año.
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
                <div><h3>Configuraciones guardadas</h3><p>Una por período lectivo y carrera (o general)</p></div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Año lectivo</th>
                        <th>Carrera</th>
                        <th>Monto</th>
                        <th>Habilitada desde</th>
                        <th>Día límite</th>
                        <th>Concepto</th>
                        <th style="text-align:center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($configuraciones->sortBy(fn ($c) => [-$c->anioLectivo->anio, $c->carrera?->nombre_carrera ?? '']) as $configuracion)
                        <tr>
                            <td class="font-semibold text-slate-700">{{ $configuracion->anioLectivo->anio }}</td>
                            <td class="text-slate-500">{{ $configuracion->carrera?->nombre_carrera ?? 'Todas las carreras' }}</td>
                            <td class="font-display font-bold text-[#0B3D91]">$ {{ number_format($configuracion->monto, 0, ',', '.') }}</td>
                            <td class="text-slate-500">{{ $meses->firstWhere('id_mes', $configuracion->mes_habilitacion)?->nombre_mes }}</td>
                            <td class="text-slate-500">Día {{ $configuracion->dia_limite }}</td>
                            <td class="text-slate-500">
                                @if ($configuracion->concepto)
                                    {{-- Solo el ojito: al tocarlo se ve el concepto completo --}}
                                    <button type="button" data-ver-concepto="{{ $configuracion->concepto }}"
                                            class="h-8 w-8 inline-grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Ver concepto"><x-sg.icono nombre="eye" class="h-4 w-4" /></button>
                                @else
                                    —
                                @endif
                            </td>
                            <td><div class="flex justify-center gap-1">
                                {{-- Lápiz: carga esta configuración en el formulario de arriba para modificarla --}}
                                <button type="button" data-editar-config="{{ $configuracion->id_anio_lectivo }}" data-editar-carrera="{{ $configuracion->id_carrera }}"
                                        class="h-8 w-8 inline-grid place-items-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-[#0B3D91] transition-colors" title="Editar configuración"><x-sg.icono nombre="pencil" class="h-4 w-4" /></button>
                                <form method="POST" action="{{ route('director.configuracion-matricula.destroy', $configuracion) }}" class="inline"
                                      data-confirm="¿Eliminar la configuración de matrícula de {{ $configuracion->carrera?->nombre_carrera ?? 'todas las carreras' }} {{ $configuracion->anioLectivo->anio }}? Se borran las matrículas impagas que le corresponden. Si algún alumno ya la pagó, no se puede eliminar.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="h-8 w-8 inline-grid place-items-center rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors" title="Eliminar configuración"><x-sg.icono nombre="trash" class="h-4 w-4" /></button>
                                </form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-sg.vacio icono="settings">Todavía no se configuró ninguna matrícula.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Ventanita para leer el concepto completo --}}
    <div id="modal-concepto" class="hidden fixed inset-0 z-[100] items-center justify-center bg-[#04153A]/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl">
            <h3 class="font-display text-lg font-bold text-slate-800 mb-2">Concepto</h3>
            <p id="modal-concepto-texto" class="text-sm text-slate-600 break-words rounded-2xl bg-slate-50 p-4"></p>
            <div class="flex justify-end mt-4">
                <button type="button" data-cerrar-concepto class="sg-btn sg-btn--borde">Cerrar</button>
            </div>
        </div>
    </div>

    @php
        $configuracionesJs = $configuraciones->mapWithKeys(fn ($c, $clave) => [$clave => [
            'monto' => (float) $c->monto,
            'mes_habilitacion' => $c->mes_habilitacion,
            'dia_limite' => $c->dia_limite,
            'concepto' => $c->concepto,
        ]]);
    @endphp

    <script>
        (function () {
            const configuraciones = @json($configuracionesJs);
            const selectAnio = document.getElementById('select-anio-config-matricula');
            const selectCarrera = document.getElementById('select-carrera-config-matricula');
            const montoInput = document.getElementById('input-monto-matricula');
            const mesInput = document.getElementById('input-mes-habilitacion');
            const diaInput = document.getElementById('input-dia-limite');
            const conceptoInput = document.querySelector('input[name="concepto"]');

            function precargarConfiguracion() {
                // Clave "año-carrera" ('todas' si es la general): cada carrera
                // puede tener su propia configuración, o una general.
                const clave = selectAnio.value + '-' + (selectCarrera.value || 'todas');
                const config = configuraciones[clave];
                if (! config) return;
                sgMiles(montoInput, config.monto);
                mesInput.value = config.mes_habilitacion;
                diaInput.value = config.dia_limite;
                conceptoInput.value = config.concepto || '';
            }

            // Lápiz: elige ese año y carrera arriba, carga sus datos y sube al formulario.
            const form = document.getElementById('form-configuracion-matricula');
            document.querySelectorAll('[data-editar-config]').forEach(boton => boton.addEventListener('click', () => {
                selectAnio.value = boton.dataset.editarConfig;
                selectCarrera.value = boton.dataset.editarCarrera; // vacío = todas las carreras
                precargarConfiguracion();
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                montoInput.focus({ preventScroll: true });
            }));

            // Día límite: al salir del campo, si quedó en 0 pasa a 1 (el tope de 28 se aplica al escribir).
            diaInput.addEventListener('blur', () => { if (diaInput.value !== '' && parseInt(diaInput.value, 10) < 1) diaInput.value = '1'; });

            // Ojito: muestra el concepto completo.
            const modalConcepto = document.getElementById('modal-concepto');
            const cerrarConcepto = () => { modalConcepto.classList.add('hidden'); modalConcepto.classList.remove('flex'); };
            document.querySelectorAll('[data-ver-concepto]').forEach(boton => boton.addEventListener('click', () => {
                document.getElementById('modal-concepto-texto').textContent = boton.dataset.verConcepto;
                modalConcepto.classList.remove('hidden');
                modalConcepto.classList.add('flex');
            }));
            modalConcepto.addEventListener('click', e => { if (e.target === modalConcepto || e.target.closest('[data-cerrar-concepto]')) cerrarConcepto(); });

            selectAnio.addEventListener('change', precargarConfiguracion);
            selectCarrera.addEventListener('change', precargarConfiguracion);
            precargarConfiguracion();
        })();
    </script>
@endsection
