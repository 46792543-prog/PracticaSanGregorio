@extends('layouts.portal')

@section('titulo', 'Mesas de examen disponibles')
@section('subtitulo', \App\Support\FechaEsp::larga(now()))

@section('contenido')
    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-slate-500">Solo se muestran mesas según tu situación académica</p>
        <form method="GET">
            <select name="turno" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-amber-400">
                @foreach ($turnos as $valor => $texto)
                    <option value="{{ $valor }}" @selected($turno === $valor)>{{ $texto }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse ($mesas as $mesa)
            @php $bloqueada = (bool) $mesa->bloqueo; @endphp
            <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col {{ $bloqueada ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between mb-4">
                    <div class="h-14 w-14 rounded-lg bg-[#16305a] text-white flex flex-col items-center justify-center leading-none">
                        <span class="text-lg font-bold">{{ $mesa->fecha_examen->format('d') }}</span>
                        <span class="text-[10px] uppercase">{{ \App\Support\FechaEsp::mesAbrev($mesa->fecha_examen) }}</span>
                    </div>
                    <span class="text-xs font-semibold text-blue-600 bg-blue-50 rounded-full px-3 py-1">
                        {{ $mesa->llamado === 'primer_llamado' ? '1er llamado' : '2do llamado' }}
                    </span>
                </div>

                <h3 class="font-bold text-slate-800">{{ $mesa->materia->nombre }}</h3>
                <p class="text-xs text-slate-400 mt-1 mb-4">Examen final · Modalidad presencial</p>

                <div class="mt-auto">
                    @if ($mesa->ya_inscripto)
                        <button type="button" disabled class="w-full rounded-lg bg-green-100 text-green-700 text-sm font-semibold py-2">
                            ✓ Ya inscripto
                        </button>
                    @elseif ($bloqueada)
                        <button type="button" disabled class="w-full rounded-lg bg-slate-100 text-slate-400 text-sm font-semibold py-2">
                            No disponible
                        </button>
                        <p class="text-xs text-red-500 mt-2">✕ Te falta regularizar {{ $mesa->bloqueo->nombre }}</p>
                    @else
                        <button type="button"
                                onclick="abrirModalInscripcion('{{ $mesa->materia->nombre }}', '{{ \App\Support\FechaEsp::corta($mesa->fecha_examen) }}', '{{ $turnos[$mesa->turno] }}', '{{ $mesa->llamado === 'primer_llamado' ? '1er llamado' : '2do llamado' }}', '{{ route('mesas-examen.inscribir', $mesa) }}')"
                                class="w-full rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2 transition">
                            Inscribirme
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm p-8 text-center text-slate-400 text-sm">
                No hay mesas programadas para este turno.
            </div>
        @endforelse
    </div>

    {{-- Modal de confirmación --}}
    <div id="modal-inscripcion" class="hidden fixed inset-0 bg-black/40 z-50 items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-slate-800">📄 Confirmar inscripción</h3>
                <button type="button" onclick="cerrarModalInscripcion()" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <p id="modal-materia" class="font-semibold text-slate-700"></p>
            <p id="modal-detalle" class="text-xs text-slate-400 mb-4"></p>

            <div class="space-y-2 mb-4">
                <p class="text-xs font-semibold text-green-700 bg-green-50 rounded-lg px-3 py-2">✓ Correlativas cumplidas</p>
                <p class="text-xs font-semibold text-green-700 bg-green-50 rounded-lg px-3 py-2">✓ Cuotas al día</p>
            </div>

            <p class="text-xs text-blue-600 bg-blue-50 rounded-lg px-3 py-2 mb-4">
                ℹ Tu inscripción quedará <strong>pendiente</strong> hasta que el secretario académico la apruebe.
            </p>

            <div class="flex gap-3">
                <button type="button" onclick="cerrarModalInscripcion()"
                        class="flex-1 rounded-lg border border-slate-300 text-slate-600 text-sm font-semibold py-2">
                    Cancelar
                </button>
                <form id="modal-form" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2">
                        Inscribirme
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function abrirModalInscripcion(materia, fecha, turno, llamado, url) {
            document.getElementById('modal-materia').textContent = materia;
            document.getElementById('modal-detalle').textContent = `Examen final · ${fecha} · ${turno} · ${llamado}`;
            document.getElementById('modal-form').action = url;
            const modal = document.getElementById('modal-inscripcion');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function cerrarModalInscripcion() {
            const modal = document.getElementById('modal-inscripcion');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
@endsection
