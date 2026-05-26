@props(['desglose'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por origen</x-ui.card.title>
        <x-ui.card.description>Actividad del día por zona de recolección</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        @if($desglose->isEmpty())
            <p class="text-sm text-muted-foreground py-4">Sin pesajes registrados hoy.</p>
        @else
            <x-ui.table variant="flat">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Origen</x-ui.table.head>
                        <x-ui.table.head>Pesajes</x-ui.table.head>
                        <x-ui.table.head>Toneladas</x-ui.table.head>
                        <x-ui.table.head>%</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($desglose as $fila)
                    <x-ui.table.row>
                        <x-ui.table.cell class="font-medium" data-label="Origen">{{ $fila['nombre'] }}</x-ui.table.cell>
                        <x-ui.table.cell class="tabular-nums" data-label="Pesajes">{{ $fila['pesajes'] }}</x-ui.table.cell>
                        <x-ui.table.cell class="tabular-nums" data-label="Toneladas">{{ number_format($fila['toneladas'], 2, ',', '.') }}</x-ui.table.cell>
                        <x-ui.table.cell class="tabular-nums text-muted-foreground" data-label="%">{{ $fila['porcentaje'] }}%</x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        @endif
    </x-ui.card.content>
</x-ui.card>
