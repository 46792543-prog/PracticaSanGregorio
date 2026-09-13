@extends('layouts.admin')

@section('titulo', 'Nueva mesa de examen')
@section('subtitulo', 'Gestión académica / Configurar mesa')

@section('contenido')
    @php
        $meses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 max-w-3xl">
        <h2 class="font-bold text-slate-800 flex items-center gap-2 mb-6">📋 Completar datos de la mesa</h2>

        <form method="POST" action="{{ route('admin.mesas.store') }}" class="space-y-5" id="form-mesa">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">SELECCIONAR MATERIA *</label>
                <select name="id_materia" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
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

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">APERTURA DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_inicio_inscripcion" id="fecha_inicio_inscripcion" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">CIERRE DE INSCRIPCIÓN *</label>
                    <input type="date" name="fecha_fin_inscripcion" id="fecha_fin_inscripcion" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DEL EXAMEN *</label>
                <input type="date" name="fecha_examen" id="fecha_examen" required class="w-full sm:w-64 rounded-xl border border-slate-300 px-4 py-3 text-sm">
                <p class="text-xs text-slate-400 mt-1">Debe ser al menos una semana después de la apertura de inscripción.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO EXAMEN *</label>
                    <div class="flex gap-2">
                        <select name="id_turno" id="id_turno" required class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach ($turnos as $t)
                                <option value="{{ $t->id_turno }}" data-mes-desde="{{ $t->mes_desde }}" data-mes-hasta="{{ $t->mes_hasta }}">{{ $t->nombre_turno }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="abrirModalTurnos()" title="Gestionar turnos de examen"
                                class="shrink-0 w-12 h-12 flex items-center justify-center rounded-xl border border-[#1E4D8C] text-[#1E4D8C] text-lg font-bold leading-none hover:bg-blue-50">+</button>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">Se sugiere solo al elegir la fecha del examen; podés cambiarlo a mano.</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">CUPO MÁXIMO (OPCIONAL)</label>
                    <input type="number" name="cupo_maximo" min="1" placeholder="Sin límite" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">LLAMADO *</label>
                <div class="flex gap-2 w-full sm:w-64">
                    <select name="id_llamado" id="id_llamado" required class="min-w-0 flex-1 rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="">Seleccionar...</option>
                        @foreach ($llamados as $l)
                            <option value="{{ $l->id_llamado }}">{{ $l->nombre_llamado }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="abrirModalLlamados()" title="Gestionar llamados"
                            class="shrink-0 w-12 h-12 flex items-center justify-center rounded-xl border border-[#1E4D8C] text-[#1E4D8C] text-lg font-bold leading-none hover:bg-blue-50">+</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-2">TRIBUNAL EXAMINADOR (PRESIDENTE / VOCALES)</label>
                <div class="grid sm:grid-cols-3 gap-3">
                    <select name="presidente_id" id="presidente_id" class="tribunal-select w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Presidente...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal1_id" id="vocal1_id" class="tribunal-select w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Vocal 1...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                    <select name="vocal2_id" id="vocal2_id" class="tribunal-select w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm">
                        <option value="">Vocal 2...</option>
                        @foreach ($profesores as $profesor)
                            <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <p id="error-tribunal" class="hidden text-xs text-red-500 mt-2">No se puede asignar el mismo docente en más de un rol del tribunal.</p>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="submit" class="rounded-xl bg-[#D4A017] hover:brightness-95 text-white font-bold text-sm px-8 py-3.5">Crear mesa</button>
                <a href="{{ route('admin.mesas.index') }}" class="rounded-xl border border-slate-300 text-slate-600 font-bold text-sm px-8 py-3.5">Cancelar</a>
            </div>
        </form>
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
