<x-ui.card variant="elevated">
    <div x-data="evolucionChart(evolucion7, evolucion15, evolucion90)"
         @periodo-change="periodo = $event.detail">
        <x-ui.card.header>
            <div class="flex items-center justify-between gap-3 w-full">
                <div>
                    <x-ui.card.title>Evolución diaria</x-ui.card.title>
                    <x-ui.card.description>Toneladas netas por día</x-ui.card.description>
                </div>
                <x-ui.tabs value="7"
                    x-init="$watch('active', v => $dispatch('periodo-change', Number(v)))">
                    <x-ui.tabs.list class="shrink-0 h-auto p-0.5 gap-0.5">
                        <x-ui.tabs.trigger value="7"  class="px-2.5 py-1 text-xs">7d</x-ui.tabs.trigger>
                        <x-ui.tabs.trigger value="15" class="px-2.5 py-1 text-xs">15d</x-ui.tabs.trigger>
                        <x-ui.tabs.trigger value="90" class="px-2.5 py-1 text-xs">3m</x-ui.tabs.trigger>
                    </x-ui.tabs.list>
                </x-ui.tabs>
            </div>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0 px-4 pb-4 overflow-x-auto">
            {{-- Estado vacío: sin toneladas en el período activo --}}
            <div x-show="vacio" x-cloak class="flex flex-col items-center justify-center text-center gap-3 py-12">
                <div class="flex size-11 items-center justify-center rounded-full bg-muted">
                    <x-lucide-chart-column class="size-5 text-muted-foreground" />
                </div>
                <div class="space-y-1 max-w-sm">
                    <p class="text-sm font-medium">Sin datos para graficar todavía</p>
                    <p class="text-xs text-muted-foreground">
                        Cuando se registren pesajes en el período, vas a ver acá la evolución de toneladas netas día a día.
                    </p>
                </div>
            </div>
            <div x-show="!vacio" x-ref="chart" :style="chartMinWidth ? 'min-width:' + chartMinWidth + 'px' : ''"></div>
        </x-ui.card.content>
    </div>
</x-ui.card>
