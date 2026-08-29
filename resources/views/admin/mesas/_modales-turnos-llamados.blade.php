@php
    $meses = $meses ?? [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
@endphp

{{-- Modal: gestionar turnos --}}
<div id="modal-turnos" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[85vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <h3 class="font-bold text-slate-800">📅 Turnos de examen</h3>
            <button type="button" onclick="cerrarModalTurnos()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        @if ($errors->has('nombre_turno'))
            <p class="text-xs text-red-500 mb-3">{{ $errors->first('nombre_turno') }}</p>
        @endif

        <div class="space-y-2 mb-5">
            @foreach ($turnos as $t)
                <div class="flex gap-2 items-center border border-slate-200 rounded-lg p-2">
                    <form method="POST" action="{{ route('admin.mesas.turnos.update', $t) }}" class="flex flex-1 gap-2 items-center min-w-0">
                        @csrf @method('PUT')
                        <input type="text" name="nombre_turno" value="{{ $t->nombre_turno }}" required maxlength="100"
                               class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                        <select name="mes_desde" class="rounded-lg border border-slate-300 px-1 py-1.5 text-xs">
                            @foreach ($meses as $num => $nombre)
                                <option value="{{ $num }}" @selected($t->mes_desde == $num)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        <span class="text-xs text-slate-400">a</span>
                        <select name="mes_hasta" class="rounded-lg border border-slate-300 px-1 py-1.5 text-xs">
                            @foreach ($meses as $num => $nombre)
                                <option value="{{ $num }}" @selected($t->mes_hasta == $num)>{{ $nombre }}</option>
                            @endforeach
                        </select>
                        <button type="submit" title="Guardar cambios" class="shrink-0 text-[#1E4D8C] hover:underline text-xs font-semibold">Guardar</button>
                    </form>
                    <form method="POST" action="{{ route('admin.mesas.turnos.destroy', $t) }}" onsubmit="return confirm('¿Eliminar este turno?');">
                        @csrf @method('DELETE')
                        <button type="submit" title="Eliminar turno" class="shrink-0 text-red-500 hover:underline text-xs font-semibold">Eliminar</button>
                    </form>
                </div>
            @endforeach
        </div>

        <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Agregar nuevo turno</p>
        <form method="POST" action="{{ route('admin.mesas.turnos.store') }}" class="flex gap-2 items-center">
            @csrf
            <input type="text" name="nombre_turno" required maxlength="100" placeholder="Ej: Turno Julio/Agosto"
                   class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-2 text-xs">
            <select name="mes_desde" required class="rounded-lg border border-slate-300 px-1 py-2 text-xs">
                <option value="">Desde...</option>
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
            <select name="mes_hasta" required class="rounded-lg border border-slate-300 px-1 py-2 text-xs">
                <option value="">Hasta...</option>
                @foreach ($meses as $num => $nombre)
                    <option value="{{ $num }}">{{ $nombre }}</option>
                @endforeach
            </select>
            <button type="submit" class="shrink-0 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-xs font-semibold px-3 py-2">Agregar</button>
        </form>
    </div>
</div>

{{-- Modal: gestionar llamados --}}
<div id="modal-llamados" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="flex items-start justify-between mb-4">
            <h3 class="font-bold text-slate-800">🔔 Llamados</h3>
            <button type="button" onclick="cerrarModalLlamados()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>

        @if ($errors->has('nombre_llamado'))
            <p class="text-xs text-red-500 mb-3">{{ $errors->first('nombre_llamado') }}</p>
        @endif

        <div class="space-y-2 mb-5">
            @foreach ($llamados as $l)
                <div class="flex gap-2 items-center border border-slate-200 rounded-lg p-2">
                    <form method="POST" action="{{ route('admin.mesas.llamados.update', $l) }}" class="flex flex-1 gap-2 items-center min-w-0">
                        @csrf @method('PUT')
                        <input type="text" name="nombre_llamado" value="{{ $l->nombre_llamado }}" required maxlength="50"
                               class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs">
                        <button type="submit" title="Guardar cambios" class="shrink-0 text-[#1E4D8C] hover:underline text-xs font-semibold">Guardar</button>
                    </form>
                    <form method="POST" action="{{ route('admin.mesas.llamados.destroy', $l) }}" onsubmit="return confirm('¿Eliminar este llamado?');">
                        @csrf @method('DELETE')
                        <button type="submit" title="Eliminar llamado" class="shrink-0 text-red-500 hover:underline text-xs font-semibold">Eliminar</button>
                    </form>
                </div>
            @endforeach
        </div>

        <p class="text-xs font-semibold text-slate-400 uppercase mb-2">Agregar nuevo llamado</p>
        <form method="POST" action="{{ route('admin.mesas.llamados.store') }}" class="flex gap-2">
            @csrf
            <input type="text" name="nombre_llamado" required maxlength="50" placeholder="Ej: Tercer llamado"
                   class="min-w-0 flex-1 rounded-lg border border-slate-300 px-2 py-2 text-xs">
            <button type="submit" class="shrink-0 rounded-lg bg-[#1E4D8C] hover:bg-[#173d70] transition text-white text-xs font-semibold px-3 py-2">Agregar</button>
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
