@props([
    'source' => null,
    'zonas' => null,
    'title' => 'Mapa de calor',
    'description' => 'Intensidad de recolección por zona, sumando todos sus turnos. Elegí la métrica para ver dónde se concentra la actividad.',
])

{{--
    Panel de mapa de calor embebible (Dashboard y Reportes), en una card elevada
    para mantener consistencia con los desgloses (x-domain.dashboard.desglose-zona).
    Reutiliza el selector de métricas, el mapa y el ranking. El dataset llega por:
      - source: clave de window.__dashboardData (Dashboard, reactivo)
      - zonas:  colección del informe (Reportes, estático)
    El empty-state lo decide Alpine (hayMapa) para servir ambos modos con la misma
    plantilla.
--}}
<div
    x-data="mapaCalor(@js($source ? ['source' => $source] : ['zonas' => $zonas ?? []]))"
    x-cloak
>
    <x-ui.card variant="elevated">
        <x-ui.card.header>
            <x-ui.card.title>{{ $title }}</x-ui.card.title>
            <x-ui.card.description>{{ $description }}</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0">

            {{-- Sin zonas con geometría --}}
            <div x-show="!hayMapa" class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <x-lucide-map class="size-6" />
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-medium">Todavía no hay zonas en el mapa</p>
                    <p class="max-w-96 text-sm text-muted-foreground">
                        Dibujá el área de tus zonas para ver el mapa de calor. Cada zona con polígono se colorea según la métrica elegida.
                    </p>
                </div>
                <x-ui.button size="sm" :href="route('admin.tipos-servicio.index')" class="mt-1">
                    <x-lucide-map-pin class="size-4" />
                    Ir a Servicios
                </x-ui.button>
            </div>

            {{-- Mapa --}}
            <div x-show="hayMapa" class="flex flex-col gap-4">
                <x-domain.mapa-calor.selector-metrica />
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <x-domain.mapa-calor.mapa />
                    </div>
                    <div>
                        <x-domain.mapa-calor.lista />
                    </div>
                </div>
            </div>

        </x-ui.card.content>
    </x-ui.card>
</div>
