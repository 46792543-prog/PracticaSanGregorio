@php
    $meses = $meses ?? [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
@endphp

{{-- Modal: gestionar turnos --}}
<div id="modal-turnos" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
    <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="calendar" /></span> Turnos de examen</h3>
            <button type="button" onclick="cerrarModalTurnos()" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
        </div>

        @if ($errors->has('nombre_turno'))
            <p class="text-xs text-red-500 mb-3">{{ $errors->first('nombre_turno') }}</p>
        @endif

        <div class="space-y-2 mb-5">
            @foreach ($turnos as $t)
                <div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors">
                    <form method="POST" action="{{ route('admin.mesas.turnos.update', $t) }}" class="flex flex-1 gap-2 items-center min-w-0">
                        @csrf @method('PUT')
                        <input type="text" name="nombre_turno" value="{{ $t->nombre_turno }}" required maxlength="100"
                               class="sg-input sg-input--chico min-w-0 flex-1">
                        <select name="mes_desde" class="sg-input sg-input--chico">
                            @foreach ($meses as $num => $nombre)
                                <option value="{{ $num }}" @selected($t->mes_desde == $num)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-slate-400">a</span>
                        <select name="mes_hasta" class="sg-input sg-input--chico">
                            @foreach ($meses as $num => $nombre)
                                <option value="{{ $num }}" @selected($t->mes_hasta == $num)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        <button type="submit" title="Guardar cambios" class="sg-btn sg-btn--suave sg-btn--chico shrink-0">Guardar</button>
                    </form>
                    <form method="POST" action="{{ route('admin.mesas.turnos.destroy', $t) }}" onsubmit="return confirm('¿Eliminar este turno?');">
                        @csrf @method('DELETE')
                        <button type="submit" title="Eliminar turno" class="sg-btn sg-btn--peligro sg-btn--icono shrink-0"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                    </form>
                </div>
            @endforeach
        </div>

        <p class="sg-label mb-2">Agregar nuevo turno</p>
        <form method="POST" action="{{ route('admin.mesas.turnos.store') }}" class="flex gap-2 items-center">
            @csrf
            <input type="text" name="nombre_turno" required maxlength="100" placeholder="Ej: Turno Julio/Agosto"
                   class="sg-input sg-input--chico min-w-0 flex-1">
            <select name="mes_desde" required class="sg-input sg-input--chico">
                <option value="">Desde...</option>
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
            <select name="mes_hasta" required class="sg-input sg-input--chico">
                <option value="">Hasta...</option>
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico shrink-0">Agregar</button>
        </form>
    </div>
</div>

{{-- Modal: gestionar llamados --}}
<div id="modal-llamados" class="hidden fixed inset-0 bg-[#04153A]/50 backdrop-blur-sm z-[70] items-center justify-center p-4">
    <div class="sg-modal bg-white rounded-3xl shadow-2xl ring-1 ring-slate-200/60 w-full max-w-sm p-6">
        <div class="flex items-start justify-between mb-4">
            <h3 class="font-display font-bold text-slate-800 flex items-center gap-3"><span class="sg-chip sg-chip--chico sg-chip--amarillo"><x-sg.icono nombre="bell" /></span> Llamados</h3>
            <button type="button" onclick="cerrarModalLlamados()" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" aria-label="Cerrar"><x-sg.icono nombre="x" /></button>
        </div>

        @if ($errors->has('nombre_llamado'))
            <p class="text-xs text-red-500 mb-3">{{ $errors->first('nombre_llamado') }}</p>
        @endif

        <div class="space-y-2 mb-5">
            @foreach ($llamados as $l)
                <div class="flex gap-2 items-center border border-slate-200 rounded-xl p-2 hover:border-blue-200 transition-colors">
                    <form method="POST" action="{{ route('admin.mesas.llamados.update', $l) }}" class="flex flex-1 gap-2 items-center min-w-0">
                        @csrf @method('PUT')
                        <input type="text" name="nombre_llamado" value="{{ $l->nombre_llamado }}" required maxlength="50"
                               class="sg-input sg-input--chico min-w-0 flex-1">
                        <button type="submit" title="Guardar cambios" class="sg-btn sg-btn--suave sg-btn--chico shrink-0">Guardar</button>
                    </form>
                    <form method="POST" action="{{ route('admin.mesas.llamados.destroy', $l) }}" onsubmit="return confirm('¿Eliminar este llamado?');">
                        @csrf @method('DELETE')
                        <button type="submit" title="Eliminar llamado" class="sg-btn sg-btn--peligro sg-btn--icono shrink-0"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                    </form>
                </div>
            @endforeach
        </div>

        <p class="sg-label mb-2">Agregar nuevo llamado</p>
        <form method="POST" action="{{ route('admin.mesas.llamados.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nombre_llamado" required maxlength="50" placeholder="Ej: Tercer llamado"
                   class="sg-input sg-input--chico min-w-0 flex-1">
            <button type="submit" class="sg-btn sg-btn--azul sg-btn--chico shrink-0">Agregar</button>
        </form>
    </div>
</div>

<script>
    function abrirModalTurnos() {
        document.getElementById('modal-turnos').classList.remove('hidden');
        document.getElementById('modal-turnos').classList.add('flex');
    }
    function cerrarModalTurnos() {
        document.getElementById('modal-turnos').classList.add('hidden');
        document.getElementById('modal-turnos').classList.remove('flex');
    }
    function abrirModalLlamados() {
        document.getElementById('modal-llamados').classList.remove('hidden');
        document.getElementById('modal-llamados').classList.add('flex');
    }
    function cerrarModalLlamados() {
        document.getElementById('modal-llamados').classList.add('hidden');
        document.getElementById('modal-llamados').classList.remove('flex');
    }
</script>
