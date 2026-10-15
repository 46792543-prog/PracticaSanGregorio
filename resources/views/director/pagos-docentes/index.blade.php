@extends('layouts.director')

@section('titulo', 'Pago de Docentes')
@section('subtitulo', 'Gestioná y consultá los pagos realizados a los docentes de la institución')

@section('contenido')
    @php
        $estadoEstilos = [
            'Pagado' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'Pendiente' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'No pagado' => 'bg-rose-50 text-rose-600 ring-rose-200',
            'Anulado' => 'bg-rose-50 text-rose-600 ring-rose-200',
        ];
    @endphp

    <x-sg.encabezado titulo="Pago de Docentes" subtitulo="Registrá y consultá los pagos realizados a los docentes de la institución" eyebrow="Finanzas" icono="briefcase">
        <button type="button" onclick="abrirModal('modal-pago-docente')" class="sg-btn sg-btn--primario">
            <x-sg.icono nombre="plus" /> Registrar pago
        </button>
    </x-sg.encabezado>

    <div class="sg-card sg-revelar overflow-hidden">
        <div class="sg-card__head">
            <div class="sg-card__titulo">
                <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="cash" /></span>
                <div><h3>Pagos registrados</h3><p>El estado se cambia directamente desde la lista</p></div>
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <div class="flex-1 min-w-[200px]">
                <label class="sg-label">Buscar docente</label>
                <div class="sg-campo-icono">
                    <x-sg.icono nombre="search" />
                    <input type="text" name="q" value="{{ $busqueda }}" placeholder="Nombre, apellido o DNI..." maxlength="25"
                           oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü0-9\s]/g, '')"
                           class="sg-input w-full">
                </div>
            </div>
            <div>
                <label class="sg-label">Fecha de pago</label>
                <input type="date" name="fecha_pago" value="{{ $fechaPago }}" class="sg-input w-auto">
            </div>
            <div>
                <label class="sg-label">Período abonado</label>
                <input type="month" name="periodo" value="{{ $periodo }}" class="sg-input w-auto">
            </div>
            <button class="sg-btn sg-btn--azul"><x-sg.icono nombre="filter" /> Buscar</button>
            @if ($busqueda || $fechaPago || $periodo)
                <a href="{{ route('director.pagos-docentes.index') }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="x" /> Limpiar</a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="sg-tabla">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Período</th>
                        <th>Fecha de pago</th>
                        <th class="!text-right">Monto</th>
                        <th>Método de pago</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody class="sg-filas">
                    @forelse ($pagos as $pago)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="sg-avatar">{{ mb_substr($pago->profesor->nombre, 0, 1) }}{{ mb_substr($pago->profesor->apellido, 0, 1) }}</span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800">{{ $pago->profesor->apellido }}, {{ $pago->profesor->nombre }}</p>
                                        <p class="text-xs text-slate-400">DNI {{ $pago->profesor->dni }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::mesAnio($pago->periodo) }}</td>
                            <td class="whitespace-nowrap">{{ \App\Support\FechaEsp::corta($pago->fecha_pago) }}</td>
                            <td class="!text-right font-display font-bold !text-slate-800 whitespace-nowrap">$ {{ number_format($pago->monto, 0, ',', '.') }}</td>
                            <td>{{ $pago->metodoPago->nombre_metodo }}</td>
                            <td>
                                <form method="POST" action="{{ route('director.pagos-docentes.estado', $pago) }}">
                                    @csrf
                                    @method('PUT')
                                    <select name="id_estado_pago_docente" onchange="this.form.submit()" title="Cambiar estado del pago"
                                            class="text-xs font-semibold rounded-full pl-3 pr-8 py-1.5 border-0 ring-1 cursor-pointer {{ $estadoEstilos[$pago->estadoPago->nombre_estado] ?? 'bg-slate-50 text-slate-600 ring-slate-200' }}">
                                        @foreach ($estadosPago as $opcion)
                                            <option value="{{ $opcion->id_estado_pago_docente }}" @selected($pago->id_estado_pago_docente === $opcion->id_estado_pago_docente)>{{ $opcion->nombre_estado }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @if ($pago->observaciones)
                                    <p class="text-xs text-slate-400 mt-1" title="{{ $pago->observaciones }}">{{ \Illuminate\Support\Str::limit($pago->observaciones, 30) }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-sg.vacio icono="briefcase" titulo="Sin pagos">No hay pagos a docentes registrados con estos filtros.</x-sg.vacio></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pagos->hasPages())
            <div class="sg-card__foot">{{ $pagos->links() }}</div>
        @endif
    </div>

    {{-- Modal: registrar pago --}}
    <div id="modal-pago-docente" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-start justify-between mb-5">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="briefcase" /></span> Registrar pago de docente</h3>
                <button type="button" onclick="cerrarModal('modal-pago-docente')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.store') }}">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div class="sm:col-span-2">
                        <label class="sg-label">Docente *</label>
                        <select name="id_profesor" id="input-pago-profesor" required onchange="actualizarDniDocente()" class="sg-input w-full">
                            <option value="">Seleccioná docente...</option>
                            @foreach ($profesores as $profesor)
                                <option value="{{ $profesor->id_profesor }}" @selected(old('id_profesor') == $profesor->id_profesor)>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="sg-label">DNI</label>
                        <input type="text" id="input-pago-dni" readonly placeholder="—" class="sg-input w-full !bg-slate-50 text-slate-500">
                    </div>
                    <div>
                        <label class="sg-label">Período abonado *</label>
                        <input type="month" name="periodo" required value="{{ old('periodo', now()->format('Y-m')) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Fecha de pago *</label>
                        <input type="date" name="fecha_pago" required value="{{ old('fecha_pago', now()->toDateString()) }}" class="sg-input w-full">
                    </div>
                    <div>
                        <label class="sg-label">Monto pagado *</label>
                        <div class="sg-campo-icono">
                            <x-sg.icono nombre="cash" />
                            <input type="number" step="0.01" min="0.01" name="monto" required placeholder="0,00" value="{{ old('monto') }}"
                                   onkeydown="if (['e', 'E', '+', '-'].includes(event.key)) event.preventDefault();"
                                   class="sg-input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="sg-label">Método de pago *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_metodo_pago_docente" required class="sg-input min-w-0 flex-1">
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo->id_metodo_pago_docente }}" @selected(old('id_metodo_pago_docente') == $metodo->id_metodo_pago_docente)>{{ $metodo->nombre_metodo }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModal('modal-metodo-pago')" title="Agregar nuevo método de pago" class="sg-btn sg-btn--borde sg-btn--icono shrink-0"><x-sg.icono nombre="plus" /></button>
                        </div>
                    </div>
                    <div>
                        <label class="sg-label">Estado *</label>
                        <div class="flex gap-2 min-w-0">
                            <select name="id_estado_pago_docente" required class="sg-input min-w-0 flex-1">
                                @foreach ($estadosPago as $estado)
                                    <option value="{{ $estado->id_estado_pago_docente }}" @selected(old('id_estado_pago_docente') ? old('id_estado_pago_docente') == $estado->id_estado_pago_docente : $estado->nombre_estado === 'Pagado')>{{ $estado->nombre_estado }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="abrirModal('modal-estado-pago')" title="Agregar nuevo estado" class="sg-btn sg-btn--borde sg-btn--icono shrink-0"><x-sg.icono nombre="plus" /></button>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="sg-label">Observaciones</label>
                        <textarea name="observaciones" rows="2" maxlength="255" placeholder="Observaciones opcionales..." class="sg-input w-full">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-pago-docente')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center"><x-sg.icono nombre="check" /> Guardar pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo método de pago --}}
    <div id="modal-metodo-pago" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[80] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="card" /></span> Nuevo método de pago</h3>
                <button type="button" onclick="cerrarModal('modal-metodo-pago')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.metodos-pago.store') }}">
                @csrf
                <label class="sg-label">Nombre del método</label>
                <input type="text" name="nombre_metodo" required maxlength="30" placeholder="Ej: Depósito bancario" class="sg-input w-full mb-5">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-metodo-pago')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center">Guardar método</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: nuevo estado --}}
    <div id="modal-estado-pago" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[80] items-center justify-center p-4">
        <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="check-circle" /></span> Nuevo estado</h3>
                <button type="button" onclick="cerrarModal('modal-estado-pago')" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
            </div>
            <form method="POST" action="{{ route('director.pagos-docentes.estados.store') }}">
                @csrf
                <label class="sg-label">Nombre del estado</label>
                <input type="text" name="nombre_estado" required maxlength="30" placeholder="Ej: No pagado" class="sg-input w-full mb-5">
                <div class="flex gap-3">
                    <button type="button" onclick="cerrarModal('modal-estado-pago')" class="sg-btn sg-btn--borde flex-1 justify-center">Cancelar</button>
                    <button type="submit" class="sg-btn sg-btn--primario flex-1 justify-center">Guardar estado</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const dniPorProfesor = @json($profesores->mapWithKeys(fn ($p) => [$p->id_profesor => $p->dni]));

        function abrirModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        function actualizarDniDocente() {
            const id = document.getElementById('input-pago-profesor').value;
            document.getElementById('input-pago-dni').value = dniPorProfesor[id] ?? '';
        }

        @if ($errors->any())
            actualizarDniDocente();
            abrirModal('modal-pago-docente');
        @endif
    </script>
@endsection
