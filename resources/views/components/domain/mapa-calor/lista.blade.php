{{-- Ranking de zonas por la métrica activa. Vive bajo el x-data="mapaCalor" del padre. --}}
<x-ui.card variant="elevated">
    <x-ui.card.header class="pb-3">
        <x-ui.card.title>Ranking de zonas</x-ui.card.title>
        <x-ui.card.description>
            Ordenado por <span x-text="metricaActual().label.toLowerCase()"></span>.
        </x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="space-y-1.5">
        <template x-for="z in listaOrdenada" :key="z.id">
            <div class="flex items-center justify-between gap-3 rounded-lg border border-border px-3 py-2">
                <div class="flex min-w-0 items-center gap-2">
                    <span class="size-3 shrink-0 rounded-sm" :style="{ backgroundColor: colorFor(z.metricas[metric]) }"></span>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="truncate text-sm font-medium" x-text="z.nombre"></span>
                            <template x-if="!z.tiene_geometria">
                                <span class="rounded-full bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">sin área</span>
                            </template>
                        </div>
                        <span class="text-xs text-muted-foreground tabular-nums">
                            <span x-text="z.metricas.pesajes"></span> viajes · <span x-text="fmt(z.metricas.toneladas, 2)"></span> t
                        </span>
                    </div>
                </div>
                <span class="shrink-0 text-sm font-semibold tabular-nums" x-text="valorMetrica(z)"></span>
            </div>
        </template>
    </x-ui.card.content>
</x-ui.card>
