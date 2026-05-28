@props(['vehiculos'])

<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Por tipo de vehículo</x-ui.card.title>
        <x-ui.card.description>Distribución de viajes y toneladas por tipo de vehículo.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($vehiculos->isEmpty())
            <p class="text-caption py-4 text-center">Sin datos para el período seleccionado.</p>
        @else
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Tipo</x-ui.table.head>
                    <x-ui.table.head class="text-right">Viajes</x-ui.table.head>
                    <x-ui.table.head class="text-right">Toneladas</x-ui.table.head>
                    <x-ui.table.head class="text-right">kg/viaje</x-ui.table.head>
                    <x-ui.table.head class="text-right">% Total</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach($vehiculos as $veh)
                <x-ui.table.row>
                    <x-ui.table.cell class="font-medium" data-label="Tipo">{{ $veh['nombre'] }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="Viajes">{{ number_format($veh['viajes']) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="Toneladas">{{ number_format($veh['toneladas'], 1) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="kg/viaje">{{ number_format($veh['kg_viaje']) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right" data-label="% Total">
                        <div class="flex items-center justify-end gap-2">
                            <span>{{ $veh['porcentaje'] }}%</span>
                            <div class="w-16 bg-muted rounded-full h-1.5">
                                <div class="bg-primary h-1.5 rounded-full"
                                     style="width: {{ $veh['porcentaje'] }}%"></div>
                            </div>
                        </div>
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
            <x-ui.table.footer>
                <x-ui.table.row>
                    <x-ui.table.cell class="font-semibold">Total</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-semibold font-mono">
                        {{ number_format($vehiculos->sum('viajes')) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-semibold font-mono">
                        {{ number_format($vehiculos->sum('toneladas'), 1) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell colspan="2"></x-ui.table.cell>
                </x-ui.table.row>
            </x-ui.table.footer>
        </x-ui.table>
        @endif
    </x-ui.card.content>
</x-ui.card>
