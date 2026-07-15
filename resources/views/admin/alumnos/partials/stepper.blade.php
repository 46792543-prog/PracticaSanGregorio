@php
    $pasos = [1 => 'Datos Personales', 2 => 'Datos Académicos', 3 => 'Acceso al Sistema'];
@endphp
<div class="w-56 shrink-0 space-y-6">
    @foreach ($pasos as $numero => $texto)
        <div class="flex items-center gap-3">
            <span @class([
                    'h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0',
                    'bg-green-600' => $numero < $paso,
                    'bg-[#D4A017]' => $numero === $paso,
                    'bg-slate-200 text-slate-400' => $numero > $paso,
                ])>
                {{ $numero < $paso ? '✓' : $numero }}
            </span>
            <span @class([
                    'text-sm font-semibold',
                    'text-green-600' => $numero < $paso,
                    'text-[#1E4D8C]' => $numero === $paso,
                    'text-slate-400' => $numero > $paso,
                ])>{{ $texto }}</span>
        </div>
    @endforeach
</div>
