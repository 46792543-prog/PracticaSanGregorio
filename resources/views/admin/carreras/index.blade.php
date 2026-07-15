@extends('layouts.admin')

@section('titulo', 'Carreras y Planes de Estudio')

@section('contenido')
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Carreras y Planes de Estudio</h1>
        <p class="text-sm text-slate-400">Gestión de oferta académica e itinerarios curriculares</p>
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <p class="text-sm text-slate-400">Configuración académica / Carreras y planes</p>
        <a href="{{ route('admin.carreras.create') }}" class="rounded-xl bg-[#D4A017] shadow-sm hover:shadow-md hover:brightness-105 transition text-white font-semibold text-sm px-5 py-2.5">
            + Nueva carrera
        </a>
    </div>

    <form method="GET" class="mb-6">
        <input type="text" name="q" value="{{ $busqueda }}" placeholder="🔍 Buscar por nombre de carrera o código de plan..." onchange="this.form.submit()"
               class="w-full rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm shadow-sm">
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-400 uppercase bg-slate-50">
                    <th class="px-6 py-3 font-semibold">Nº Resolución</th>
                    <th class="px-6 py-3 font-semibold">Carrera institucional</th>
                    <th class="px-6 py-3 font-semibold">Duración</th>
                    <th class="px-6 py-3 font-semibold">Materias</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($carreras as $carrera)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">
                            <p class="font-bold text-slate-700">Res. N° {{ $carrera->resolucion_ministerial ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-semibold text-slate-700">{{ $carrera->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $carrera->familia_profesional ? "Familia Profesional: {$carrera->familia_profesional}" : '' }}</p>
                        </td>
                        <td class="px-6 py-4 text-slate-500">{{ $carrera->duracion_anios }} Años</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.carreras.plan', $carrera) }}" class="text-blue-600 font-semibold hover:underline">{{ $carrera->materias_count }} asignaturas</a>
                        </td>
                        <td class="px-6 py-4">
                            <span @class([
                                    'text-xs font-semibold rounded-full px-3 py-1',
                                    'bg-green-100 text-green-700' => $carrera->estado === 'activa',
                                    'bg-slate-100 text-slate-500' => $carrera->estado === 'en_espera',
                                    'bg-red-100 text-red-600' => $carrera->estado === 'inactiva',
                                ])>
                                {{ ['activa' => 'Activa', 'en_espera' => 'En espera', 'inactiva' => 'Inactiva'][$carrera->estado] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 flex gap-3 text-xs font-semibold">
                            <a href="{{ route('admin.carreras.plan', $carrera) }}" class="text-blue-600 hover:underline">Ver plan</a>
                            <a href="{{ route('admin.carreras.materias', $carrera) }}" class="text-[#1E4D8C] hover:underline">Materias</a>
                            <a href="{{ route('admin.carreras.correlativas', $carrera) }}" class="text-[#1E4D8C] hover:underline">Correlativas</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">No hay carreras cargadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
