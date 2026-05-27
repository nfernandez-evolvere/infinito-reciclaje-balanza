<x-ui.card variant="elevated">
    <div x-data="evolucionChart(evolucion7, evolucion15, evolucion90)">
        <x-ui.card.header>
            <div class="flex items-center justify-between gap-3 w-full">
                <div>
                    <x-ui.card.title>Evolución diaria</x-ui.card.title>
                    <x-ui.card.description>Toneladas netas por día</x-ui.card.description>
                </div>
                <div class="flex items-center rounded-md border border-border bg-muted/40 p-0.5 gap-0.5 shrink-0">
                    @foreach([7 => '7d', 15 => '15d', 90 => '3m'] as $dias => $label)
                    <button type="button" @click="periodo = {{ $dias }}"
                        :class="periodo === {{ $dias }} ? 'bg-background text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                        class="rounded px-2.5 py-1 text-xs font-medium transition-colors">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0 px-4 pb-4">
            <div x-ref="chart"></div>
        </x-ui.card.content>
    </div>
</x-ui.card>
