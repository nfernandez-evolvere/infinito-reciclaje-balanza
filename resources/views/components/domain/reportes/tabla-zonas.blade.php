@props(['zonas'])

<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Por zona y turno</x-ui.card.title>
        <x-ui.card.description>Desglose de viajes y toneladas por zona de recolección y turno.</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($zonas->isEmpty())
            <p class="text-caption py-4 text-center">Sin datos para el período seleccionado.</p>
        @else
        <x-ui.table>
            <x-ui.table.header>
                <x-ui.table.row>
                    <x-ui.table.head>Zona</x-ui.table.head>
                    <x-ui.table.head>Turno</x-ui.table.head>
                    <x-ui.table.head class="text-right">Viajes</x-ui.table.head>
                    <x-ui.table.head class="text-right">Toneladas</x-ui.table.head>
                    <x-ui.table.head class="text-right">kg/viaje</x-ui.table.head>
                    <x-ui.table.head class="text-right">% Total</x-ui.table.head>
                    <x-ui.table.head class="text-right">kg/ha</x-ui.table.head>
                </x-ui.table.row>
            </x-ui.table.header>
            <x-ui.table.body>
                @foreach($zonas as $zona)
                <x-ui.table.row>
                    <x-ui.table.cell class="font-medium" data-label="Zona">{{ $zona['nombre'] }}</x-ui.table.cell>
                    <x-ui.table.cell data-label="Turno">
                        @if($zona['turno'])
                            <x-ui.badge variant="secondary">{{ $zona['turno'] }}</x-ui.badge>
                        @else
                            <span class="text-muted-foreground">—</span>
                        @endif
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="Viajes">{{ number_format($zona['viajes']) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="Toneladas">{{ number_format($zona['toneladas'], 1) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="kg/viaje">{{ number_format($zona['kg_viaje']) }}</x-ui.table.cell>
                    <x-ui.table.cell class="text-right" data-label="% Total">{{ $zona['porcentaje'] }}%</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-mono" data-label="kg/ha">
                        {{ $zona['kg_ha'] !== null ? number_format($zona['kg_ha'], 1) : '—' }}
                    </x-ui.table.cell>
                </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
            <x-ui.table.footer>
                <x-ui.table.row>
                    <x-ui.table.cell class="font-semibold" colspan="2">Total</x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-semibold font-mono">
                        {{ number_format($zonas->sum('viajes')) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell class="text-right font-semibold font-mono">
                        {{ number_format($zonas->sum('toneladas'), 1) }}
                    </x-ui.table.cell>
                    <x-ui.table.cell colspan="3"></x-ui.table.cell>
                </x-ui.table.row>
            </x-ui.table.footer>
        </x-ui.table>
        @endif
    </x-ui.card.content>
</x-ui.card>
