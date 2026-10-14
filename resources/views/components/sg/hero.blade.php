{{-- Banner de bienvenida de los paneles principales. Slots: default (texto bajo el saludo), "acciones", "extra" (mini indicadores). --}}
@props(['saludo', 'etiqueta' => 'Instituto Superior San Gregorio'])
<section {{ $attributes->merge(['class' => 'sg-hero mb-8 sg-revelar']) }}>
    <span class="sg-hero__puntos"></span>
    <span class="sg-hero__blob sg-hero__blob--celeste"></span>
    <span class="sg-hero__blob sg-hero__blob--amarillo"></span>
    <span class="sg-hero__blob sg-hero__blob--anillo"></span>
    <div class="px-6 sm:px-9 py-8 sm:py-9">
        <div class="flex items-start justify-between gap-6 flex-wrap">
            <div class="min-w-0">
                <span class="sg-hero__tag"><span class="h-1.5 w-1.5 rounded-full bg-[#FFC107]"></span>{{ $etiqueta }}</span>
                <h1 class="sg-hero__saludo mt-4">{!! $saludo !!}</h1>
                <div class="mt-2 text-sm text-blue-100/85">{{ $slot }}</div>
            </div>
            @isset($acciones)
                <div class="flex items-center gap-3 flex-wrap">{{ $acciones }}</div>
            @endisset
        </div>
        @isset($extra)
            <div class="mt-7 grid gap-3 grid-cols-2 xl:grid-cols-4">{{ $extra }}</div>
        @endisset
    </div>
</section>
