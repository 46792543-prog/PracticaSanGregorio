@extends('layouts.admin')

@section('titulo', 'Requisitos de documentación')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Requisitos de documentación</h1>
        <p class="text-sm text-slate-400">Configurá qué documentos se les solicitan a los alumnos al inscribirse</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 overflow-hidden">
        <form method="GET" class="p-4 border-b border-slate-100">
            <input type="text" name="q" value="{{ $busqueda }}" placeholder="Buscar requisito..." onchange="this.form.submit()"
                   class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
        </form>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase bg-slate-50">
                    <th class="px-6 py-3 font-semibold">Nombre del documento</th>
                    <th class="px-6 py-3 font-semibold">Tipo</th>
                    <th class="px-6 py-3 font-semibold">Aplica</th>
                    <th class="px-6 py-3 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($requisitos as $requisito)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $requisito->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $requisito->descripcion }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-semibold rounded-full px-3 py-1 {{ $requisito->obligatorio ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $requisito->obligatorio ? 'Obligatorio' : 'Opcional' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $requisito->carrera?->nombre ?? 'Todas las carreras' }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.documentacion.requisitos.destroy', $requisito) }}" onsubmit="return confirm('¿Eliminar este requisito?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 text-xs font-semibold hover:underline">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <p class="text-xs font-semibold text-slate-400 uppercase mb-4">Agregar nuevo requisito</p>
        <form method="POST" action="{{ route('admin.documentacion.requisitos.store') }}" class="grid sm:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Nombre del documento</label>
                <input type="text" name="nombre" required placeholder="Ej: Partida de nacimiento"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Descripción breve</label>
                <input type="text" name="descripcion" placeholder="Aclaración para el alumno"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Tipo</label>
                <select name="obligatorio" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="1">Obligatorio</option>
                    <option value="0">Opcional</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">Aplica a carrera</label>
                <select name="carrera_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todas las carreras</option>
                    @foreach ($carreras as $carrera)
                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex justify-end">
                <button type="submit" class="rounded-xl bg-[#1E4D8C] shadow-sm hover:shadow transition text-white text-sm font-semibold px-6 py-2.5">Guardar requisito</button>
            </div>
        </form>
    </div>
@endsection
