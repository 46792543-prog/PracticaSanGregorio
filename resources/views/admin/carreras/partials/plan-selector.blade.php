{{--
    Selector del plan de estudios que se está viendo (Materias, Correlativas y Ver plan).
    Recibe: $carrera, $plan, $planesCarrera y $editable (true = pantalla que modifica el plan).
--}}
@php
    $coloresEstado = ['Vigente' => 'sg-badge--verde', 'En extinción' => 'sg-badge--amarillo', 'Caducado' => 'sg-badge--rojo', 'Próximo' => 'sg-badge--azul'];
@endphp
<div class="sg-card p-4 mb-6 sg-revelar">
    <div class="flex flex-wrap items-center gap-2">
        <span class="sg-label !mb-0 mr-1">Plan de estudios:</span>
        @foreach ($planesCarrera as $p)
            <a href="{{ url()->current() }}?plan={{ $p->id_plan_estudio }}"
               class="sg-btn sg-btn--chico {{ $p->id_plan_estudio === $plan->id_plan_estudio ? 'sg-btn--azul' : 'sg-btn--borde' }}">{{ $p->nombre }}</a>
        @endforeach
        <a href="{{ route('admin.carreras.planes', $carrera) }}" class="sg-link text-sm ml-auto"><x-sg.icono nombre="settings" class="!h-4 !w-4" /> Gestionar planes</a>
    </div>
    <p class="text-xs text-slate-500 mt-3">
        <span class="sg-badge {{ $coloresEstado[$plan->estado()] ?? 'sg-badge--gris' }} sg-badge--sin-punto mr-1">{{ $plan->estado() }}</span>
        Rige desde {{ $plan->anio_desde }}
        · Inscripciones {{ $plan->anio_cierre_inscripcion ? 'hasta ' . $plan->anio_cierre_inscripcion : 'abiertas' }}
        · {{ $plan->anio_caducidad ? 'Caduca en ' . $plan->anio_caducidad : 'Sin fecha de caducidad' }}
        @if ($plan->resolucion_ministerial) · Res. {{ $plan->resolucion_ministerial }} @endif
    </p>
    @if (! empty($editable) && $plan->tieneAlumnos())
        <p class="sg-aviso sg-aviso--alerta text-xs mt-3"><span>
            Este plan ya tiene alumnos inscriptos: sus materias y correlativas <strong>no se pueden modificar</strong>.
            Para cambiarlas, creá un plan nuevo en <a href="{{ route('admin.carreras.planes', $carrera) }}" class="underline font-semibold">Gestionar planes</a> (se copia este como base).
        </span></p>
    @endif
</div>
