@props(['evolucion'])

<x-ui.card>
    <x-ui.card.header>
        <div class="flex items-start justify-between">
            <div>
                <x-ui.card.title>Evolución diaria</x-ui.card.title>
                <x-ui.card.description>
                    Promedio: <strong>{{ number_format($evolucion['promedio'], 1) }} ton/día</strong>
                    &nbsp;·&nbsp; Máx: {{ number_format($evolucion['maximo'], 1) }} t
                    &nbsp;·&nbsp; Mín: {{ number_format($evolucion['minimo'], 1) }} t
                </x-ui.card.description>
            </div>
        </div>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0 overflow-x-auto">
        <div x-data="evolucionRangoChart(@js($evolucion))">
            <div :style="`min-width:${chartMinWidth}px`">
                <div x-ref="chart"></div>
            </div>
        </div>
    </x-ui.card.content>
</x-ui.card>
