@php
    $hayMapa = $zonas->where('tiene_geometria', true)->isNotEmpty();

    $rangoLabel = $desde->isSameDay($hasta)
        ? $desde->format('d/m/Y')
        : $desde->format('d/m/Y') . ' – ' . $hasta->format('d/m/Y');
@endphp

<x-layouts.app title="Mapa de calor">
<div class="flex flex-col gap-6" x-data="mapaCalor({{ Js::from($zonas) }})">

    <div class="flex flex-col gap-1">
        <x-ui.typography as="h2">Mapa de calor</x-ui.typography>
        <x-ui.typography as="muted">
            Intensidad de recolección por zona. Elegí la métrica y el período para ver dónde se concentra la actividad.
        </x-ui.typography>
        <span class="text-caption mt-0.5">Período · {{ $rangoLabel }}</span>
    </div>

    @unless($hayMapa)
        <x-ui.empty-state
            icon="map"
            title="Todavía no hay zonas en el mapa"
            description="Dibujá el área de tus zonas para ver el mapa de calor. Cada zona con polígono se colorea según la métrica elegida."
        >
            <x-ui.button size="sm" :href="route('admin.zonas.index')">
                <x-lucide-map-pin class="size-4" />
                Ir a Zonas
            </x-ui.button>
        </x-ui.empty-state>
    @else
        <div class="flex flex-wrap items-end justify-between gap-3">
            <x-domain.mapa-calor.selector-metrica />
            <x-domain.mapa-calor.filtros :desde="$desde" :hasta="$hasta" />
        </div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-domain.mapa-calor.mapa />
            </div>
            <div>
                <x-domain.mapa-calor.lista />
            </div>
        </div>
    @endunless

</div>
</x-layouts.app>
