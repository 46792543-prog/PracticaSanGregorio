@extends('layouts.admin')

@section('titulo', 'Horarios de Carrera')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Horarios de Carrera</h1>
        <p class="text-sm text-slate-400">Armá y editá el cuadro de horarios de cada carrera y turno</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-6">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">CARRERA</label>
                <select name="id_carrera" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm min-w-[240px]">
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id_carrera }}" @selected($idCarrera === $carrera->id_carrera)>{{ $carrera->nombre_carrera }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">TURNO</label>
                <select name="id_turno_cursada" onchange="this.form.submit()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
                    @foreach ($turnos as $turno)
                        <option value="{{ $turno->id_turno_cursada }}" @selected($idTurno === $turno->id_turno_cursada)>{{ $turno->nombre_turno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ml-auto">
                <button type="button" onclick="abrirModalModulo()"
                        class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-semibold text-sm px-5 py-2.5">
                    + Agregar módulo
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-slate-400 uppercase bg-slate-50">
                        <th class="px-4 py-3 font-semibold">Módulo</th>
                        <th class="px-4 py-3 font-semibold">Horario</th>
                        @foreach ($dias as $dia)
                            <th class="px-4 py-3 font-semibold">{{ $dia }}</th>
                        @endforeach
                        <th class="px-4 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($modulos as $modulo)
                        <tr class="hover:bg-slate-50">
                            @php
                                $horaInicioFmt = $modulo->hora_inicio->format('H:i');
                                $horaFinFmt = $modulo->hora_fin->format('H:i');
                            @endphp
                            <td class="px-4 py-3 font-bold text-slate-700 align-top">{{ $modulo->modulo }}°</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap align-top">{{ $horaInicioFmt }} a {{ $horaFinFmt }}</td>
                            @foreach ($dias as $dia)
                                @php
                                    $celda = $modulo->celdasPorDia->get($dia);
                                    $contexto = $modulo->modulo . '° — ' . $horaInicioFmt . ' a ' . $horaFinFmt . ' — ' . $dia;
                                @endphp
                                <td class="px-1 py-1.5 align-top">
                                    <button type="button"
                                            onclick="abrirModalCelda({{ $modulo->id_horario_modulo }}, {{ \Illuminate\Support\Js::from($dia) }}, {{ \Illuminate\Support\Js::from($contexto) }}, {{ \Illuminate\Support\Js::from($celda->contenido ?? '') }})"
                                            class="w-full min-h-[48px] text-left rounded-lg px-2.5 py-2 text-xs leading-snug transition
                                                   {{ $celda?->contenido ? 'bg-blue-50 text-slate-700 font-medium hover:bg-blue-100' : 'text-slate-300 hover:bg-slate-50 hover:text-slate-400' }}">
                                        {{ $celda->contenido ?? '+ Agregar' }}
                                    </button>
                                </td>
                            @endforeach
                            @php
                                $payloadModulo = [
                                    'id' => $modulo->id_horario_modulo,
                                    'modulo' => $modulo->modulo,
                                    'hora_inicio' => $horaInicioFmt,
                                    'hora_fin' => $horaFinFmt,
                                ];
                            @endphp
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col gap-1.5 text-xs font-semibold whitespace-nowrap">
                                    <button type="button" class="text-[#1E4D8C] hover:underline text-left"
                                            onclick="editarModulo({{ \Illuminate\Support\Js::from($payloadModulo) }})">Editar</button>
                                    <form method="POST" action="{{ route('admin.horarios.modulos.destroy', $modulo) }}" onsubmit="return confirm('¿Eliminar este módulo y todo su contenido cargado?');">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($dias) + 3 }}" class="px-6 py-10 text-center text-slate-400">
                                Todavía no hay módulos cargados para esta carrera y turno. Usá "+ Agregar módulo" para empezar a armar el cuadro.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal: agregar / editar módulo --}}
    <div id="modal-modulo" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800" id="modal-modulo-titulo">➕ Agregar módulo</h3>
                <button type="button" onclick="cerrarModalModulo()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('admin.horarios.modulos.store') }}" id="form-modulo">
                @csrf
                <input type="hidden" name="_method" id="input-modulo-method" value="">
                <input type="hidden" name="id_carrera" value="{{ $idCarrera }}">
                <input type="hidden" name="id_turno_cursada" value="{{ $idTurno }}">

                <label class="block text-xs font-semibold text-slate-500 mb-1">NÚMERO DE MÓDULO *</label>
                <input type="number" name="modulo" id="input-modulo-numero" min="1" max="20" required placeholder="Ej: 1"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">HORA INICIO *</label>
                        <input type="time" name="hora_inicio" id="input-modulo-hora-inicio" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">HORA FIN *</label>
                        <input type="time" name="hora_fin" id="input-modulo-hora-fin" required
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalModulo()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" id="input-modulo-submit" class="flex-1 rounded-lg bg-[#D4A017] hover:brightness-105 transition text-white text-sm font-semibold py-2">Guardar módulo</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: editar contenido de una celda --}}
    <div id="modal-celda" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800">📝 Cargar contenido</h3>
                <button type="button" onclick="cerrarModalCelda()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <p id="modal-celda-contexto" class="text-xs text-slate-400 mb-4"></p>
            <form method="POST" action="" id="form-celda">
                @csrf
                @method('PUT')
                <input type="hidden" name="dia_semana" id="input-celda-dia">

                <label class="block text-xs font-semibold text-slate-500 mb-1">MATERIA / DOCENTE</label>
                <textarea name="contenido" id="input-celda-contenido" rows="3" maxlength="150" placeholder="Ej: ANATOMÍA Y FISIOLOGÍA HUMANA — MEDICO VERA OMAR GREGORIO"
                          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-1"></textarea>
                <p class="text-[11px] text-slate-400 mb-4">Dejalo vacío y guardá para borrar el contenido de esta celda.</p>

                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalCelda()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rutaModulosStore = @json(route('admin.horarios.modulos.store'));
        const rutaModulosBase = @json(url('/admin/horarios/modulos'));

        function abrirModalModulo() {
            document.getElementById('form-modulo').reset();
            document.getElementById('form-modulo').action = rutaModulosStore;
            document.getElementById('input-modulo-method').value = '';
            document.getElementById('modal-modulo-titulo').textContent = '➕ Agregar módulo';
            document.getElementById('input-modulo-submit').textContent = 'Guardar módulo';
            const modal = document.getElementById('modal-modulo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalModulo() {
            const modal = document.getElementById('modal-modulo');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function editarModulo(modulo) {
            document.getElementById('form-modulo').action = rutaModulosBase + '/' + modulo.id;
            document.getElementById('input-modulo-method').value = 'PUT';
            document.getElementById('input-modulo-numero').value = modulo.modulo;
            document.getElementById('input-modulo-hora-inicio').value = modulo.hora_inicio;
            document.getElementById('input-modulo-hora-fin').value = modulo.hora_fin;
            document.getElementById('modal-modulo-titulo').textContent = '✏️ Editar módulo';
            document.getElementById('input-modulo-submit').textContent = 'Guardar cambios';
            const modal = document.getElementById('modal-modulo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function abrirModalCelda(idModulo, dia, contexto, contenidoActual) {
            document.getElementById('form-celda').action = rutaModulosBase + '/' + idModulo + '/celdas';
            document.getElementById('input-celda-dia').value = dia;
            document.getElementById('input-celda-contenido').value = contenidoActual;
            document.getElementById('modal-celda-contexto').textContent = contexto;
            const modal = document.getElementById('modal-celda');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalCelda() {
            const modal = document.getElementById('modal-celda');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
