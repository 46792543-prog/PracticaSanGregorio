@extends('layouts.admin')

@section('titulo', 'Nueva mesa de examen')
@section('subtitulo', 'Gestión académica / Configurar mesa')

@section('contenido')
    <x-sg.encabezado titulo="Nueva mesa de examen" subtitulo="Gestión académica · Configurar mesa" eyebrow="Mesas de examen" icono="clipboard">
        <a href="{{ route('admin.mesas.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Volver</a>
    </x-sg.encabezado>

    @php
        $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
    @endphp

    @if ($corteActivo)
        <div @class([
                'sg-aviso mb-6 sg-revelar',
                'sg-aviso--error' => $corteActivo->anio != now()->year,
                'sg-aviso--alerta' => $corteActivo->anio == now()->year,
            ])>
            <x-sg.icono nombre="alert" class="h-5 w-5 shrink-0" />
            <span>
                Esta mesa se va a crear en el <strong>corte {{ $corteActivo->anio }}</strong>{{ $corteActivo->estadoAnio?->nombre_estado !== 'Activo' ? ' (Cerrado)' : '' }}.
                @if ($corteActivo->anio != now()->year)
                    Estás en {{ now()->year }} pero el corte activo es {{ $corteActivo->anio }}: si no es el año que querés cargar, cambialo arriba con el selector de corte antes de guardar.
                @else
                    Verificá que sea el año correcto antes de guardar.
                @endif
            </span>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3 items-start">
    <div class="sg-card sg-revelar xl:col-span-2">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--azul"><x-sg.icono nombre="plus" /></span>
                <div><h3>Completar datos de la mesa</h3><p>Los campos con * son obligatorios</p></div>
            </div>
        </div>
        <div class="sg-card__body">

        <form method="POST" action="{{ route('admin.mesas.store') }}" class="space-y-5" id="form-mesa">
            @csrf
            <div class="flex items-center gap-3 pt-2">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="book" /></span>
                <p class="font-display font-bold text-sm text-slate-800">Materia</p>
                <span class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"></span>
            </div>
            <div>
                <label class="sg-label">SELECCIONAR MATERIA *</label>
                <select name="id_materia" required class="sg-input w-full">
                    <option value="">Ej: Fundamentos de Enfermería Básica y Comunitaria</option>
                    @foreach ($carreras as $carrera)
                        <optgroup label="{{ $carrera->nombre_carrera }}">
                            @foreach ($carrera->materias as $materia)
                                <option value="{{ $materia->id_materia }}">{{ $materia->nombre }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <span class="sg-chip sg-chip--chico sg-chip--celeste"><x-sg.icono nombre="calendar" /></span>
                <p class="font-display font-bold text-sm text-slate-800">Fechas</p>
                <span class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"></span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="sg-label">APERTURA DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_inicio_inscripcion" id="fecha_inicio_inscripcion" required class="sg-input w-full">
                </div>
                <div>
                    <label class="sg-label">CIERRE DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_fin_inscripcion" id="fecha_fin_inscripcion" required class="sg-input w-full">
                </div>
            </div>

            <div>
                <label class="sg-label">FECHA DEL EXAMEN *</label>
                <input type="date" name="fecha_examen" id="fecha_examen" required class="sg-input w-full sm:w-64">
                <p class="sg-ayuda">Debe ser al menos una semana después de la apertura de inscripción.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="settings" /></span>
                <p class="font-display font-bold text-sm text-slate-800">Organización</p>
                <span class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"></span>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="sg-label">TURNO EXAMEN *</label>
                    <div class="flex gap-2">
                        <select name="id_turno" id="id_turno" required class="sg-input min-w-0 flex-1">
                            <option value="">Seleccionar...</option>
                            @foreach ($turnos as $t)
                                <option value="{{ $t->id_turno }}" data-mes-desde="{{ $t->mes_desde }}" data-mes-hasta="{{ $t->mes_hasta }}">{{ $t->nombre_turno }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="abrirModalTurnos()" title="Gestionar turnos de examen"
                                class="shrink-0 w-[46px] flex items-center justify-center rounded-xl bg-blue-100 text-[#0B3D91] hover:bg-[#0B3D91] hover:text-white transition-colors"><x-sg.icono nombre="plus" /></button>
                    </div>
                    <p class="sg-ayuda">Se sugiere solo al elegir la fecha del examen; podés cambiarlo a mano.</p>
                </div>
                <div>
                    <label class="sg-label">CUPO MÁXIMO (OPCIONAL)</label>
                    <input type="number" name="cupo_maximo" min="1" placeholder="Sin límite" class="sg-input w-full">
                </div>
            </div>

            <div>
                <label class="sg-label">LLAMADO *</label>
                <div class="flex gap-2 w-full sm:w-64">
                    <select name="id_llamado" id="id_llamado" required class="sg-input min-w-0 flex-1">
                        <option value="">Seleccionar...</option>
                        @foreach ($llamados as $l)
                            <option value="{{ $l->id_llamado }}">{{ $l->nombre_llamado }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirModalLlamados()" title="Gestionar llamados"
                            class="shrink-0 w-[46px] flex items-center justify-center rounded-xl bg-blue-100 text-[#0B3D91] hover:bg-[#0B3D91] hover:text-white transition-colors"><x-sg.icono nombre="plus" /></button>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <span class="sg-chip sg-chip--chico sg-chip--violeta"><x-sg.icono nombre="users" /></span>
                <p class="font-display font-bold text-sm text-slate-800">Tribunal examinador</p>
                <span class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent"></span>
            </div>
            <div>
                <label class="sg-label">TRIBUNAL EXAMINADOR (PRESIDENTE / VOCALES / SUPLENTE)</label>
                <div class="grid sm:grid-cols-4 gap-3">
                    <select name="presidente_id" id="presidente_id" class="sg-input tribunal-select w-full">
                        <option value="">Presidente...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal1_id" id="vocal1_id" class="sg-input tribunal-select w-full">
                        <option value="">Vocal 1...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal2_id" id="vocal2_id" class="sg-input tribunal-select w-full">
                        <option value="">Vocal 2...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="suplente_id" id="suplente_id" class="sg-input tribunal-select w-full">
                        <option value="">Suplente...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <p id="error-tribunal" class="hidden text-xs font-semibold text-red-500 mt-2">No se puede asignar el mismo docente en más de un rol del tribunal.</p>
            </div>

            <div class="flex justify-end gap-3 pt-5 border-t border-slate-100">
                <a href="{{ route('admin.mesas.index') }}" class="sg-btn sg-btn--borde">Cancelar</a>
                <button type="submit" class="sg-btn sg-btn--primario"><x-sg.icono nombre="check" /> Crear mesa</button>
            </div>
        </form>
        </div>
    </div>

    <aside class="sg-card sg-revelar p-6 xl:sticky xl:top-24 bg-gradient-to-br from-blue-50 to-white">
        <span class="sg-chip sg-chip--marca sg-chip--grande mb-4"><x-sg.icono nombre="info" /></span>
        <h3 class="font-display font-bold text-slate-800 mb-3">Cómo armar la mesa</h3>
        <ol class="space-y-3 text-sm text-slate-600">
            <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">1</span> Elegí la materia de la carrera.</li>
            <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">2</span> El examen debe ser al menos una semana después de abrir la inscripción.</li>
            <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">3</span> El turno se sugiere solo según el mes del examen.</li>
            <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-[#0B3D91] text-[11px] font-bold text-white">4</span> Un mismo docente no puede ocupar dos roles del tribunal.</li>
        </ol>
    </aside>
    </div>

    @include('admin.mesas._modales-turnos-llamados')

    <script>
        // Fecha mínima del examen: apertura de inscripción + 1 semana.
        const inputApertura = document.getElementById('fecha_inicio_inscripcion');
        const inputExamen = document.getElementById('fecha_examen');
        inputApertura.addEventListener('change', function () {
            if (!this.value) return;
            const minimo = new Date(this.value + 'T00:00:00');
            minimo.setDate(minimo.getDate() + 7);
            inputExamen._flatpickr.set('minDate', minimo);
        });

        // Sugerir turno de examen automáticamente según el mes de la fecha del examen.
        const selectTurno = document.getElementById('id_turno');
        inputExamen.addEventListener('change', function () {
            if (!this.value) return;
            const mes = parseInt(this.value.split('-')[1], 10);

            for (const opcion of selectTurno.options) {
                const desde = parseInt(opcion.dataset.mesDesde, 10);
                const hasta = parseInt(opcion.dataset.mesHasta, 10);
                if (!desde || !hasta) continue;

                const coincide = desde <= hasta
                    ? (mes >= desde && mes <= hasta)
                    : (mes >= desde || mes <= hasta);

                if (coincide) {
                    selectTurno.value = opcion.value;
                    break;
                }
            }
        });

        // Tribunal: no permitir el mismo docente en más de un rol.
        const selectsTribunal = document.querySelectorAll('.tribunal-select');
        const errorTribunal = document.getElementById('error-tribunal');
        function hayDuplicados() {
            const valores = Array.from(selectsTribunal).map(s => s.value).filter(v => v !== '');
            return new Set(valores).size !== valores.length;
        }
        function chequearTribunal() {
            const duplicado = hayDuplicados();
            errorTribunal.classList.toggle('hidden', !duplicado);
            selectsTribunal.forEach(s => s.classList.toggle('border-red-400', duplicado));
            return duplicado;
        }
        selectsTribunal.forEach(s => s.addEventListener('change', chequearTribunal));

        document.getElementById('form-mesa').addEventListener('submit', function (e) {
            if (chequearTribunal()) {
                e.preventDefault();
            }
        });
    </script>
@endsection
