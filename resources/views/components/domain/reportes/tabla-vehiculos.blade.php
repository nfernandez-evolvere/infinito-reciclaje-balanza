@props(['vehiculos'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por tipo de vehículo</x-ui.card.title>
        <x-ui.card.description>Distribución de viajes y toneladas por tipo de vehículo.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($vehiculos->isEmpty())
            <div class="flex items-center justify-center py-10">
                <p class="text-caption">Sin datos para el período seleccionado.</p>
            </div>
        @else

            {{-- Mobile: compact rows --}}
            <div class="sm:hidden space-y-1.5">
                @foreach($vehiculos as $veh)
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-border bg-background">
                        <div class="flex flex-col gap-1 min-w-0 flex-1">
                            <span class="font-medium text-sm truncate">{{ $veh['nombre'] }}</span>
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <span><span class="tabular-nums">{{ number_format($veh['viajes']) }}</span> viajes</span>
                                <span>·</span>
                                <span><span class="tabular-nums">{{ number_format($veh['toneladas'], 1) }}</span> t</span>
                                <span>·</span>
                                <span><span class="tabular-nums">{{ number_format($veh['kg_viaje']) }}</span> kg/v</span>
                            </div>
                            <div class="h-1 w-full bg-muted rounded-full overflow-hidden">
                                <div class="bg-primary h-full rounded-full" style="width: {{ $veh['porcentaje'] }}%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-semibold tabular-nums shrink-0 text-muted-foreground">
                            {{ $veh['porcentaje'] }}%
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Desktop: tabla --}}
            <x-ui.table variant="flat" class="hidden sm:block">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Tipo</x-ui.table.head>
                        <x-ui.table.head>Viajes</x-ui.table.head>
                        <x-ui.table.head>Toneladas</x-ui.table.head>
                        <x-ui.table.head>kg/viaje</x-ui.table.head>
                        <x-ui.table.head>% Total</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($vehiculos as $veh)
                    <x-ui.table.row>
                        <x-ui.table.cell data-label="Tipo" class="font-medium">{{ $veh['nombre'] }}</x-ui.table.cell>
                        <x-ui.table.cell data-label="Viajes" class="tabular-nums">{{ number_format($veh['viajes']) }}</x-ui.table.cell>
                        <x-ui.table.cell data-label="Toneladas" class="tabular-nums">{{ number_format($veh['toneladas'], 1) }} t</x-ui.table.cell>
                        <x-ui.table.cell data-label="kg/viaje" class="tabular-nums text-muted-foreground">{{ number_format($veh['kg_viaje']) }} kg</x-ui.table.cell>
                        <x-ui.table.cell data-label="% Total">
                            <div class="flex items-center gap-2.5">
                                <div class="h-1.5 w-20 bg-muted rounded-full overflow-hidden">
                                    <div class="bg-primary h-full rounded-full" style="width: {{ $veh['porcentaje'] }}%"></div>
                                </div>
                                <span class="tabular-nums text-muted-foreground text-sm">{{ $veh['porcentaje'] }}%</span>
                            </div>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
                <x-ui.table.footer>
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-semibold">Total</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($vehiculos->sum('viajes')) }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($vehiculos->sum('toneladas'), 1) }} t</x-ui.table.cell>
                        <x-ui.table.cell colspan="2"></x-ui.table.cell>
                    </x-ui.table.row>
                </x-ui.table.footer>
            </x-ui.table>

        @endif
    </x-ui.card.content>
</x-ui.card>
