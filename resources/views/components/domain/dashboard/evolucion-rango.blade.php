<x-ui.card variant="elevated">
    <div x-data="evolucionRangoChart(evolucionRango)">
        <x-ui.card.header>
            <x-ui.card.title>Evolución diaria</x-ui.card.title>
            <x-ui.card.description>Toneladas netas por día del período</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0 px-4 pb-4 overflow-x-auto">
            <div x-ref="chart" :style="chartMinWidth ? 'min-width:' + chartMinWidth + 'px' : ''"></div>
        </x-ui.card.content>
    </div>
</x-ui.card>
