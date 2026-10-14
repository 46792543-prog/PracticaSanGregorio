@php
    $pasos = [1 => ['Datos Personales', 'Identidad y contacto', 'user'], 2 => ['Datos Académicos', 'Carrera e inscripción', 'cap'], 3 => ['Acceso al Sistema', 'Usuario y contraseña', 'key']];
@endphp
<aside class="sg-card sg-revelar p-5 lg:w-72 shrink-0 self-start">
    <p class="sg-eyebrow mb-4">Paso {{ $paso }} de 3</p>
    <div class="sg-progreso mb-6"><span style="width: {{ round($paso / 3 * 100) }}%"></span></div>
    <ol class="space-y-1">
        @foreach ($pasos as $numero => [$texto, $detalle, $icono])
            <li @class([
                    'relative flex items-center gap-3 rounded-xl p-2.5 transition-colors',
                    'bg-blue-100/70' => $numero === $paso,
                ])>
                <span @class([
                        'h-10 w-10 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 transition-all',
                        'bg-emerald-500 text-white shadow-md shadow-emerald-500/30' => $numero < $paso,
                        'bg-[#FFC107] text-[#082A66] shadow-md shadow-amber-400/40' => $numero === $paso,
                        'bg-slate-100 text-slate-400' => $numero > $paso,
                    ])>
                    @if ($numero < $paso)
                        <x-sg.icono nombre="check" class="h-5 w-5" trazo="2.5" />
                    @else
                        <x-sg.icono :nombre="$icono" class="h-5 w-5" />
                    @endif
                </span>
                <span class="min-w-0">
                    <span @class([
                            'block text-sm font-semibold',
                            'text-emerald-600' => $numero < $paso,
                            'text-[#0B3D91]' => $numero === $paso,
                            'text-slate-400' => $numero > $paso,
                        ])>{{ $texto }}</span>
                    <span class="block text-xs text-slate-400">{{ $detalle }}</span>
                </span>
            </li>
        @endforeach
    </ol>
</aside>
