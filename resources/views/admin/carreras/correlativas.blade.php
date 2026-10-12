@extends('layouts.admin')

@section('titulo', 'Régimen de Correlatividades')

@section('contenido')
    <x-sg.encabezado titulo="Régimen de Correlatividades" :subtitulo="$carrera->nombre_carrera" eyebrow="Carreras y planes" icono="link">
        <a href="{{ route('admin.carreras.materias', $carrera) }}" class="sg-btn sg-btn--borde"><x-sg.icono nombre="arrow-left" /> Materias</a>
        <a href="{{ route('admin.carreras.plan', $carrera) }}" class="sg-btn sg-btn--primario"><x-sg.icono nombre="eye" /> Ver plan de estudio</a>
    </x-sg.encabezado>

    <div class="grid gap-6 xl:grid-cols-5 items-start">
        <div class="sg-card sg-revelar xl:col-span-2 border-t-4 border-t-[#FFC107]">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--amarillo"><x-sg.icono nombre="plus" /></span>
                    <div><h3>Nueva correlativa</h3><p>Prerrequisitos de aprobación o regularidad</p></div>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.carreras.correlativas.store', $carrera) }}" class="sg-card__body space-y-5">
                @csrf
                <div>
                    <label class="sg-label">Materia cursante / destino</label>
                    <select id="select-materia-principal" name="id_materia_principal" required class="sg-input w-full">
                        @foreach ($materias as $materia)
                            <option value="{{ $materia->id_materia }}">{{ $materia->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="sg-label">Materia correlativa / requerida</label>
                    <div class="w-full rounded-xl border-[1.5px] border-slate-200 p-2 text-sm max-h-52 overflow-y-auto space-y-1">
                        @foreach ($materias as $materia)
                            <label class="checkbox-requisito-label flex items-center gap-2.5 rounded-lg px-2.5 py-2 font-normal text-slate-700 cursor-pointer hover:bg-slate-50 has-[:checked]:bg-blue-50 has-[:checked]:text-[#0B3D91]">
                                <input type="checkbox" name="id_materia_requisito[]" value="{{ $materia->id_materia }}" class="checkbox-requisito h-4 w-4 rounded">
                                {{ $materia->nombre }}
                            </label>
                        @endforeach
                    </div>
                    <p class="sg-ayuda">Tildá una o más materias.</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 rounded-xl border-[1.5px] border-slate-200 px-3 py-3 text-sm font-semibold text-slate-600 cursor-pointer has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                        <input type="checkbox" name="requiere_regularizada" value="1" class="h-4 w-4 rounded"> Para cursar
                    </label>
                    <label class="flex items-center gap-2.5 rounded-xl border-[1.5px] border-slate-200 px-3 py-3 text-sm font-semibold text-slate-600 cursor-pointer has-[:checked]:border-[#4FC3F7] has-[:checked]:bg-blue-50 has-[:checked]:text-[#0B3D91]">
                        <input type="checkbox" name="requiere_aprobada" value="1" class="h-4 w-4 rounded"> Para rendir
                    </label>
                </div>
                <button type="submit" class="sg-btn sg-btn--azul w-full"><x-sg.icono nombre="check" /> Guardar correlativa</button>
            </form>
        </div>

        <div class="sg-card sg-revelar xl:col-span-3 overflow-hidden">
            <div class="sg-card__head">
                <div class="sg-card__titulo">
                    <span class="sg-chip sg-chip--chico sg-chip--azul"><x-sg.icono nombre="table" /></span>
                    <div><h3>Matriz de correlatividades</h3><p>Afectan las condiciones de inscripción a examen del alumno</p></div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="sg-tabla">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Requiere</th>
                            <th>Para cursar</th>
                            <th>Para rendir</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="sg-filas">
                        @forelse ($correlativas as $correlativa)
                            <tr>
                                <td class="font-semibold !text-slate-800">{{ $correlativa->materiaPrincipal->nombre }}</td>
                                <td><span class="inline-flex items-center gap-1.5"><x-sg.icono nombre="link" class="h-3.5 w-3.5 text-[#4FC3F7]" />{{ $correlativa->materiaRequisito->nombre }}</span></td>
                                <td>
                                    @if ($correlativa->requiere_regularizada)
                                        <span class="sg-badge sg-badge--verde">Regularizada</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($correlativa->requiere_aprobada)
                                        <span class="sg-badge sg-badge--azul">Aprobada</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.carreras.correlativas.destroy', [$correlativa->id_materia_principal, $correlativa->id_materia_requisito]) }}" onsubmit="return confirm('¿Quitar esta correlativa?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="sg-btn sg-btn--peligro sg-btn--icono" title="Quitar"><x-sg.icono nombre="trash" class="!h-4 !w-4" /></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-sg.vacio icono="link">Todavía no se cargaron correlativas.</x-sg.vacio></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const selectPrincipal = document.getElementById('select-materia-principal');

            function actualizarOpcionesRequisito() {
                document.querySelectorAll('.checkbox-requisito').forEach(function (checkbox) {
                    const esLaMismaMateria = checkbox.value === selectPrincipal.value;
                    checkbox.disabled = esLaMismaMateria;
                    if (esLaMismaMateria) {
                        checkbox.checked = false;
                    }
                    checkbox.closest('.checkbox-requisito-label').classList.toggle('opacity-40', esLaMismaMateria);
                });
            }

            selectPrincipal.addEventListener('change', actualizarOpcionesRequisito);
            actualizarOpcionesRequisito();
        })();
    </script>
@endsection
