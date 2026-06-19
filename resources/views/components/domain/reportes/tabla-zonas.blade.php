@props(['zonas'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por zona y turno</x-ui.card.title>
        <x-ui.card.description>Desglose de viajes y toneladas por zona y turno. Una zona puede ocupar varias filas, una por turno.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($zonas->isEmpty())
            <div class="flex items-center justify-center py-10">
                <p class="text-caption">Sin datos para el período seleccionado.</p>
            </div>
        @else

            {{-- Mobile: compact rows --}}
            <div class="sm:hidden space-y-1.5">
                @foreach($zonas as $zona)
                    <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-border bg-background">
                        <div class="flex flex-col gap-0.5 min-w-0">
                            <span class="font-medium text-sm truncate">
                                {{ $zona['nombre'] }}
                                @if($zona['turno'])
                                    <span class="text-muted-foreground font-normal"> · {{ $zona['turno'] }}</span>
                                @endif
                            </span>
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <span><span class="tabular-nums">{{ number_format($zona['viajes']) }}</span> viajes</span>
                                <span>·</span>
                                <span><span class="tabular-nums">{{ number_format($zona['toneladas'], 1) }}</span> t</span>
                                <span>·</span>
                                <span><span class="tabular-nums">{{ number_format($zona['kg_viaje']) }}</span> kg/v</span>
                            </div>
                        </div>
                        <span class="text-sm font-semibold tabular-nums shrink-0 text-muted-foreground">
                            {{ $zona['porcentaje'] }}%
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- Desktop: tabla --}}
            <x-ui.table variant="flat" class="hidden sm:block">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Zona</x-ui.table.head>
                        <x-ui.table.head>Turno</x-ui.table.head>
                        <x-ui.table.head>Viajes</x-ui.table.head>
                        <x-ui.table.head>Toneladas</x-ui.table.head>
                        <x-ui.table.head>kg/viaje</x-ui.table.head>
                        <x-ui.table.head>kg/ha</x-ui.table.head>
                        <x-ui.table.head>% Total</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($zonas as $zona)
                    <x-ui.table.row>
                        <x-ui.table.cell data-label="Zona" class="font-medium">{{ $zona['nombre'] }}</x-ui.table.cell>
                        <x-ui.table.cell data-label="Turno">
                            @if($zona['turno'])
                                <x-ui.badge variant="secondary">{{ $zona['turno'] }}</x-ui.badge>
                            @else
                                <span class="text-muted-foreground">—</span>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Viajes" class="tabular-nums">
                            {{ number_format($zona['viajes']) }}
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Toneladas" class="tabular-nums">
                            {{ number_format($zona['toneladas'], 1) }} t
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="kg/viaje" class="tabular-nums text-muted-foreground">
                            {{ number_format($zona['kg_viaje']) }} kg
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="kg/ha" class="tabular-nums">
                            @if($zona['kg_ha'] !== null)
                                <div class="flex flex-col gap-0.5">
                                    <span>{{ number_format($zona['kg_ha'], 1) }} kg</span>
                                    <span class="text-xs text-muted-foreground">{{ number_format($zona['kg_ha'] / 1000, 2) }} t/ha</span>
                                </div>
                            @else
                                <span class="text-muted-foreground">—</span>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="% Total" class="tabular-nums text-muted-foreground">
                            {{ $zona['porcentaje'] }}%
                        </x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
                <x-ui.table.footer>
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-semibold" colspan="2">Total</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($zonas->sum('viajes')) }}</x-ui.table.cell>
                        <x-ui.table.cell class="font-semibold tabular-nums">{{ number_format($zonas->sum('toneladas'), 1) }} t</x-ui.table.cell>
                        <x-ui.table.cell colspan="3"></x-ui.table.cell>
                    </x-ui.table.row>
                </x-ui.table.footer>
            </x-ui.table>

        @endif
    </x-ui.card.content>
</x-ui.card>
