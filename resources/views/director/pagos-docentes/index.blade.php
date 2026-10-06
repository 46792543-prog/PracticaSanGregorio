@extends('layouts.director')

@section('titulo', 'Pago de Docentes')
@section('subtitulo', 'Gestioná y consultá los pagos realizados a los docentes de la institución')

@section('contenido')
    @php
        $estadoEstilos = [
            'Pagado' => 'bg-emerald-100 text-emerald-700',
            'Pendiente' => 'bg-amber-100 text-amber-700',
            'Anulado' => 'bg-red-100 text-red-600',
        ];
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center justify-between gap-4 flex-wrap px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-transparent">
            <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">💼 Pagos a Docentes</h3>
            <button type="button" onclick="abrirModalPago()"
                    class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white text-sm font-semibold px-5 py-2.5 transition">
                + Registrar Pago
            </button>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 px-6 py-4 border-b border-slate-100">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">BUSCAR DOCENTE</label>
                <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." maxlength="25"
                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                       class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DE PAGO</label>
                <input type="date" name="fecha_pago" value="{{ $fechaPago }}"
                       class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">PERÍODO ABONADO</label>
                <input type="month" name="periodo" value="{{ $periodo }}"
                       class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
            </div>
            <button class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-6 py-2.5 transition">Buscar</button>
            @if ($busqueda || $fechaPago || $periodo)
                <a href="{{ route('director.pagos-docentes.index') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-600 self-center">✕ Limpiar filtros</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                        <th class="px-6 py-3 font-semibold">Docente</th>
                        <th class="px-6 py-3 font-semibold">DNI</th>
                        <th class="px-6 py-3 font-semibold">Período</th>
                        <th class="px-6 py-3 font-semibold">Fecha de pago</th>
                        <th class="px-6 py-3 font-semibold text-right">Monto</th>
                        <th class="px-6 py-3 font-semibold">Método de pago</th>
                        <th class="px-6 py-3 font-semibold">Estado</th>
                        <th class="px-6 py-3 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($pagos as $pago)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-6 py-3 font-semibold text-slate-700">{{ $pago->profesor->apellido }}, {{ $pago->profesor->nombre }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $pago->profesor->dni }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ \App\Support\FechaEsp::mesAnio($pago->periodo) }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ \App\Support\FechaEsp::corta($pago->fecha_pago) }}</td>
                            <td class="px-6 py-3 text-right font-semibold text-slate-700">$ {{ number_format($pago->monto, 0, ',', '.') }}</td>
                            <td class="px-6 py-3 text-slate-500">{{ $pago->metodoPago->nombre_metodo }}</td>
                            <td class="px-6 py-3">
                                <form method="POST" action="{{ route('director.pagos-docentes.estado', $pago) }}" onchange="this.submit()">
                                    @csrf
                                    @method('PUT')
                                    <select name="id_estado_pago_docente"
                                            class="text-xs font-semibold rounded-full px-3 py-1 border-0 cursor-pointer {{ $estadoEstilos[$pago->estadoPago->nombre_estado] ?? 'bg-slate-100 text-slate-600' }}">
                                        @foreach ($estadosPago as $opcion)
                                            <option value="{{ $opcion->id_estado_pago_docente }}" @selected($pago->id_estado_pago_docente === $opcion->id_estado_pago_docente)>{{ $opcion->nombre_estado }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @if ($pago->observaciones)
                                    <p class="text-xs text-slate-400 mt-1" title="{{ $pago->observaciones }}">{{ \Illuminate\Support\Str::limit($pago->observaciones, 30) }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $payloadEdicion = [
                                        'id' => $pago->id_pago_docente,
                                        'id_profesor' => $pago->id_profesor,
                                        'periodo' => $pago->periodo->format('Y-m'),
                                        'fecha_pago' => $pago->fecha_pago->format('Y-m-d'),
                                        'monto' => (float) $pago->monto,
                                        'id_metodo_pago_docente' => $pago->id_metodo_pago_docente,
                                        'id_estado_pago_docente' => $pago->id_estado_pago_docente,
                                        'observaciones' => $pago->observaciones,
                                        'dni' => $pago->profesor->dni,
                                    ];
                                @endphp
                                <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap">
                                    <button type="button" class="text-[#1E4D8C] hover:underline"
                                            onclick='editarPago(@json($payloadEdicion))'>Editar</button>
                                    <form method="POST" action="{{ route('director.pagos-docentes.destroy', $pago) }}" onsubmit="return confirm('¿Eliminar este pago? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-500 hover:underline">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-6 py-10 text-center text-slate-400">No hay pagos a docentes registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $pagos->links() }}
            </div>
        </div>
    </div>

    {{-- Modal: registrar pago --}}
    <div id="modal-pago-docente" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800" id="modal-pago-titulo">💼 Registrar Pago de Docente</h3>
                <button type="button" onclick="cerrarModalPago()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.store') }}" id="form-pago-docente">
                @csrf
                <input type="hidden" name="_method" id="input-pago-method" value="">
                <div class="grid sm:grid-cols-2 gap-4 mb-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DOCENTE *</label>
                        <select name="id_profesor" id="input-pago-profesor" required onchange="actualizarDniDocente()"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Seleccioná docente...</option>
                            @foreach ($profesores as $profesor)
                                <option value="{{ $profesor->id_profesor }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DNI</label>
                        <input type="text" id="input-pago-dni" readonly placeholder="—"
                               class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">PERÍODO ABONADO *</label>
                        <input type="month" name="periodo" id="input-pago-periodo" required value="{{ now()->format('Y-m') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DE PAGO *</label>
                        <input type="date" name="fecha_pago" id="input-pago-fecha" required value="{{ now()->toDateString() }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">MONTO PAGADO *</label>
                        <div class="flex items-center gap-1">
                            <span class="text-slate-400 text-sm">$</span>
                            <input type="number" step="0.01" min="0.01" name="monto" id="input-pago-monto" required placeholder="0,00"
                                   onkeydown="if (['e', 'E', '+', '-'].includes(event.key)) event.preventDefault();"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">MÉTODO DE PAGO *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_metodo_pago_docente" id="input-pago-metodo" required class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago_docente }}">{{ $metodo->nombre_metodo }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModalMetodo()" title="Agregar nuevo método de pago"
                                    class="shrink-0 w-10 h-10 flex items-center justify-center rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-lg font-bold leading-none hover:bg-blue-50">+</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">ESTADO *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_estado_pago_docente" id="input-pago-estado" required class="min-w-0 flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach ($estadosPago as $estado)
                                    <option value="{{ $estado->id_estado_pago_docente }}" @selected($estado->nombre_estado === 'Pagado')>{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModalEstado()" title="Agregar nuevo estado"
                                    class="shrink-0 w-10 h-10 flex items-center justify-center rounded-lg border border-[#1E4D8C] text-[#1E4D8C] text-lg font-bold leading-none hover:bg-blue-50">+</button>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">OBSERVACIONES</label>
                        <textarea name="observaciones" id="input-pago-observaciones" rows="2" maxlength="255" placeholder="Observaciones opcionales..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalPago()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" id="input-pago-submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo método de pago --}}
    <div id="modal-metodo-pago" class="hidden fixed inset-0 bg-black/40 z-[60] items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800">➕ Nuevo método de pago</h3>
                <button type="button" onclick="cerrarModalMetodo()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.metodos-pago.store') }}">
                @csrf
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DEL MÉTODO</label>
                <input type="text" name="nombre_metodo" required autofocus maxlength="30" placeholder="Ej: Depósito bancario"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalMetodo()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar método</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo estado --}}
    <div id="modal-estado-pago" class="hidden fixed inset-0 bg-black/40 z-[60] items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800">➕ Nuevo estado</h3>
                <button type="button" onclick="cerrarModalEstado()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.estados.store') }}">
                @csrf
                <label class="block text-xs font-semibold text-slate-500 mb-1">NOMBRE DEL ESTADO</label>
                <input type="text" name="nombre_estado" required autofocus maxlength="30" placeholder="Ej: Retenido"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm mb-4">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModalEstado()" class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">Cancelar</button>
                    <button type="submit" class="flex-1 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-sm font-semibold py-2">Guardar estado</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dniPorProfesor = @json($profesores->mapWithKeys(fn ($p) => [$p->id_profesor => $p->dni]));
        const rutaBasePagosDocentes = @json(url('/director/pagos-docentes'));
        const rutaGuardarPago = @json(route('director.pagos-docentes.store'));

        function abrirModalPago() {
            cancelarEdicionPago();
            const modal = document.getElementById('modal-pago-docente');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalPago() {
            const modal = document.getElementById('modal-pago-docente');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function actualizarDniDocente() {
            const id = document.getElementById('input-pago-profesor').value;
            document.getElementById('input-pago-dni').value = dniPorProfesor[id] ?? '';
        }

        function editarPago(pago) {
            document.getElementById('form-pago-docente').action = rutaBasePagosDocentes + '/' + pago.id;
            document.getElementById('input-pago-method').value = 'PUT';
            document.getElementById('input-pago-profesor').value = pago.id_profesor;
            document.getElementById('input-pago-dni').value = pago.dni ?? '';
            document.getElementById('input-pago-periodo').value = pago.periodo;
            document.getElementById('input-pago-fecha').value = pago.fecha_pago;
            document.getElementById('input-pago-monto').value = pago.monto;
            document.getElementById('input-pago-metodo').value = pago.id_metodo_pago_docente;
            document.getElementById('input-pago-estado').value = pago.id_estado_pago_docente;
            document.getElementById('input-pago-observaciones').value = pago.observaciones ?? '';
            document.getElementById('modal-pago-titulo').textContent = '✏️ Editar Pago de Docente';
            document.getElementById('input-pago-submit').textContent = 'Guardar cambios';

            const modal = document.getElementById('modal-pago-docente');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cancelarEdicionPago() {
            document.getElementById('form-pago-docente').reset();
            document.getElementById('form-pago-docente').action = rutaGuardarPago;
            document.getElementById('input-pago-method').value = '';
            document.getElementById('input-pago-dni').value = '';
            document.getElementById('input-pago-periodo').value = @json(now()->format('Y-m'));
            document.getElementById('input-pago-fecha').value = @json(now()->toDateString());
            document.getElementById('modal-pago-titulo').textContent = '💼 Registrar Pago de Docente';
            document.getElementById('input-pago-submit').textContent = 'Guardar pago';
        }

        function abrirModalMetodo() {
            const modal = document.getElementById('modal-metodo-pago');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalMetodo() {
            const modal = document.getElementById('modal-metodo-pago');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function abrirModalEstado() {
            const modal = document.getElementById('modal-estado-pago');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalEstado() {
            const modal = document.getElementById('modal-estado-pago');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        @if ($errors->any())
            abrirModalPago();
        @endif
    </script>
@endsection
