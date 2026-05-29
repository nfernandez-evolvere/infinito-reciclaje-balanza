@props(['evolucion'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <div class="flex items-center justify-between gap-4 w-full">
            <div>
                <x-ui.card.title>Evolución diaria</x-ui.card.title>
                <x-ui.card.description>Toneladas netas recolectadas por día en el período</x-ui.card.description>
            </div>
            <div class="hidden sm:flex items-center gap-5 shrink-0">
                <div class="flex flex-col items-end gap-0.5">
                    <span class="text-overline">Promedio</span>
                    <span class="text-sm font-semibold tabular-nums">{{ number_format($evolucion['promedio'], 1) }} t/d</span>
                </div>
                <div class="flex flex-col items-end gap-0.5">
                    <span class="text-overline">Máx</span>
                    <span class="text-sm font-semibold tabular-nums text-success">{{ number_format($evolucion['maximo'], 1) }} t</span>
                </div>
                <div class="flex flex-col items-end gap-0.5">
                    <span class="text-overline">Mín</span>
                    <span class="text-sm font-semibold tabular-nums text-muted-foreground">{{ number_format($evolucion['minimo'], 1) }} t</span>
                </div>
            </div>
        </div>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0 px-4 pb-4 overflow-x-auto">
        <div x-data="evolucionRangoChart(@js($evolucion))">
            <div :style="`min-width:${chartMinWidth}px`">
                <div x-ref="chart"></div>
            </div>
        </div>
    </x-ui.card.content>
</x-ui.card>
