@extends('layouts.director')

@section('titulo', 'Cobro de Cuotas')
@section('subtitulo', 'Al registrar el pago, se actualiza el estado del alumno y se asienta como ingreso en el Libro de Caja')

@section('contenido')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
        <div class="bg-[#1E4D8C] px-6 py-3.5">
            <h2 class="text-white font-bold text-sm flex items-center gap-2">💳 Registrar Pago de Cuota</h2>
        </div>

        <div class="p-6">
            <form method="GET" class="grid sm:grid-cols-[1fr_auto] gap-4 mb-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">BUSCAR ALUMNO *</label>
                    <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." autofocus
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                </div>
                <div class="flex items-end">
                    <button class="rounded-xl bg-[#1E4D8C] hover:shadow-md text-white font-semibold text-sm px-6 py-2.5 transition">Buscar</button>
                </div>
            </form>

            @if ($resultados->isNotEmpty())
                <div class="space-y-2 mt-4">
                    @foreach ($resultados as $alumno)
                        <a href="{{ route('director.cuotas.index', ['alumno' => $alumno->id]) }}"
                           class="flex items-center justify-between rounded-xl border border-slate-100 hover:border-[#1E4D8C]/30 hover:bg-blue-50/40 px-4 py-3 transition">
                            <div class="flex items-center gap-3">
                                <span class="h-9 w-9 rounded-full bg-blue-100 text-blue-700 text-xs font-bold grid place-items-center">
                                    {{ mb_substr($alumno->nombre, 0, 1) }}{{ mb_substr($alumno->apellido, 0, 1) }}
                                </span>
                                <div>
                                    <p class="font-semibold text-slate-700 text-sm">{{ $alumno->apellido }}, {{ $alumno->nombre }}</p>
                                    <p class="text-xs text-slate-400">DNI {{ $alumno->dni }} · {{ $alumno->inscripcionesCarrera->first()?->carrera?->nombre }}</p>
                                </div>
                            </div>
                            @if ($alumno->cuotas_pendientes_count > 0)
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-amber-100 text-amber-700">⚠ {{ $alumno->cuotas_pendientes_count }} cuota(s) pendiente(s)</span>
                            @else
                                <span class="text-xs font-semibold rounded-full px-3 py-1 bg-emerald-100 text-emerald-700">Al día</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @elseif ($busqueda)
                <p class="text-sm text-slate-400 mt-4">No se encontraron alumnos que coincidan con "{{ $busqueda }}".</p>
            @endif

            @if ($alumnoSeleccionado)
                @php $cuotasPendientes = $alumnoSeleccionado->cuotas->where('estado', 'pendiente'); @endphp
                <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50/40 p-5">
                    <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                        <div>
                            <p class="font-bold text-slate-800">{{ $alumnoSeleccionado->apellido }}, {{ $alumnoSeleccionado->nombre }}</p>
                            <p class="text-xs text-slate-400">DNI {{ $alumnoSeleccionado->dni }} · {{ $alumnoSeleccionado->inscripcionesCarrera->first()?->carrera?->nombre }}</p>
                        </div>
                        <a href="{{ route('director.cuotas.index') }}" class="text-xs font-semibold text-[#1E4D8C] hover:underline">✕ Cambiar alumno</a>
                    </div>

                    @if ($cuotasPendientes->isEmpty())
                        <p class="text-sm text-emerald-700 bg-emerald-50 rounded-xl px-4 py-3">✓ Este alumno no tiene cuotas pendientes.</p>
                    @else
                        <form method="POST" action="{{ route('director.cuotas.cobrar') }}">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $alumnoSeleccionado->id }}">

                            <p class="text-xs font-semibold text-slate-500 uppercase mb-2">Cuotas pendientes — Seleccioná las que se están pagando y ajustá monto o recargo si corresponde:</p>
                            <div class="space-y-2 mb-4">
                                @foreach ($cuotasPendientes as $cuota)
                                    <div class="rounded-xl bg-white border border-slate-200 px-4 py-3 has-[:checked]:border-[#1E4D8C] has-[:checked]:bg-blue-50/60">
                                        <label class="flex items-center justify-between gap-3 cursor-pointer">
                                            <span class="flex items-center gap-3">
                                                <input type="checkbox" name="cuotas[{{ $cuota->id }}][pagar]" value="1" class="h-4 w-4 rounded cuota-check" data-id="{{ $cuota->id }}" checked>
                                                <span class="font-semibold text-slate-700 text-sm">{{ $cuota->concepto }}</span>
                                            </span>
                                            <span class="text-xs text-slate-400">Vence: {{ \App\Support\FechaEsp::corta($cuota->fecha_vencimiento) }}</span>
                                        </label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3">
                                            <div>
                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Monto</label>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-slate-400 text-sm">$</span>
                                                    <input type="number" step="0.01" min="0" name="cuotas[{{ $cuota->id }}][monto]" value="{{ $cuota->monto }}"
                                                           class="cuota-monto w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30" data-id="{{ $cuota->id }}">
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-semibold text-amber-600 uppercase mb-1">Recargo por mora</label>
                                                <div class="flex items-center gap-1">
                                                    <span class="text-slate-400 text-sm">$</span>
                                                    <input type="number" step="0.01" min="0" name="cuotas[{{ $cuota->id }}][recargo]" value="0"
                                                           class="cuota-recargo w-full rounded-lg border border-amber-200 px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-300" data-id="{{ $cuota->id }}">
                                                </div>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1">
                                                <label class="block text-[10px] font-semibold text-slate-400 uppercase mb-1">Subtotal</label>
                                                <p class="cuota-subtotal font-bold text-slate-700 text-sm py-1.5" data-id="{{ $cuota->id }}">
                                                    $ {{ number_format($cuota->monto, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">FECHA DE PAGO *</label>
                                    <input type="date" name="fecha_pago" value="{{ now()->toDateString() }}" required
                                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-500 mb-1">MEDIO DE PAGO *</label>
                                    <select name="medio_pago" required class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E4D8C]/30 focus:border-[#1E4D8C]">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>

                            <div class="flex items-center justify-between rounded-xl bg-white border border-slate-200 px-4 py-3 mb-4">
                                <span class="font-semibold text-slate-600 text-sm">Total a registrar:</span>
                                <span id="total-cobro" class="font-bold text-lg text-[#1E4D8C]">$ {{ number_format($cuotasPendientes->sum('monto'), 0, ',', '.') }}</span>
                            </div>

                            <p class="text-xs text-emerald-700 bg-emerald-50 rounded-xl px-4 py-3 mb-4">
                                ✓ Al confirmar: el estado de {{ $alumnoSeleccionado->apellido }}, {{ $alumnoSeleccionado->nombre }} se actualizará y se registrará automáticamente un ingreso en el Libro de Caja.
                            </p>

                            <div class="flex justify-end gap-3">
                                <a href="{{ route('director.cuotas.index') }}" class="rounded-xl border border-slate-300 text-slate-600 font-semibold text-sm px-6 py-2.5">Cancelar</a>
                                <button type="submit" class="rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm px-6 py-2.5 transition">✓ Confirmar cobro y registrar en Caja</button>
                            </div>
                        </form>

                        <script>
                            function recalcularCuota(id) {
                                const monto = parseFloat(document.querySelector(`.cuota-monto[data-id="${id}"]`).value) || 0;
                                const recargo = parseFloat(document.querySelector(`.cuota-recargo[data-id="${id}"]`).value) || 0;
                                document.querySelector(`.cuota-subtotal[data-id="${id}"]`).textContent = '$ ' + (monto + recargo).toLocaleString('es-AR');
                                recalcularTotal();
                            }
                            function recalcularTotal() {
                                let total = 0;
                                document.querySelectorAll('.cuota-check:checked').forEach(cb => {
                                    const id = cb.dataset.id;
                                    const monto = parseFloat(document.querySelector(`.cuota-monto[data-id="${id}"]`).value) || 0;
                                    const recargo = parseFloat(document.querySelector(`.cuota-recargo[data-id="${id}"]`).value) || 0;
                                    total += monto + recargo;
                                });
                                document.getElementById('total-cobro').textContent = '$ ' + total.toLocaleString('es-AR');
                            }
                            document.querySelectorAll('.cuota-check').forEach(cb => cb.addEventListener('change', recalcularTotal));
                            document.querySelectorAll('.cuota-monto, .cuota-recargo').forEach(input => {
                                input.addEventListener('input', () => recalcularCuota(input.dataset.id));
                            });
                        </script>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Historial de pagos</p>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="flex items-center gap-2.5 px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-700 text-sm">📋 Historial de Cobros de Cuotas</h3>
        </div>
        <form method="GET" class="flex flex-wrap gap-3 px-6 py-4 border-b border-slate-100">
            <input type="text" name="h_alumno" value="{{ $filtroHistorialAlumno }}" placeholder="Buscar alumno..."
                   class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
            <button class="rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold px-4">Filtrar</button>
        </form>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] text-slate-400 uppercase tracking-wide bg-slate-50/80">
                    <th class="px-6 py-3 font-semibold">Alumno</th>
                    <th class="px-6 py-3 font-semibold">Cuota</th>
                    <th class="px-6 py-3 font-semibold">Fecha pago</th>
                    <th class="px-6 py-3 font-semibold">Monto</th>
                    <th class="px-6 py-3 font-semibold">Recargo</th>
                    <th class="px-6 py-3 font-semibold">Medio</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse ($historial as $cuota)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-6 py-3 font-semibold text-slate-700">{{ $cuota->user->apellido }}, {{ $cuota->user->nombre }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ $cuota->concepto }}</td>
                        <td class="px-6 py-3 text-slate-500">{{ \App\Support\FechaEsp::corta($cuota->fecha_pago) }}</td>
                        <td class="px-6 py-3 font-semibold text-emerald-600">$ {{ number_format($cuota->monto, 0, ',', '.') }}</td>
                        <td class="px-6 py-3">
                            @if ($cuota->recargo > 0)
                                <span class="text-xs font-semibold rounded-full px-2.5 py-1 bg-amber-100 text-amber-700">+$ {{ number_format($cuota->recargo, 0, ',', '.') }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-slate-500 capitalize">{{ $cuota->medio_pago }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">Todavía no hay pagos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $historial->links() }}
        </div>
    </div>
@endsection
