@props(['source', 'description' => 'Actividad del día por zona de recolección'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por origen</x-ui.card.title>
        <x-ui.card.description>{{ $description }}</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">
            <div class="xl:col-span-9 min-w-0">
                <x-ui.table variant="flat">
                    <x-ui.table.header>
                        <x-ui.table.row>
                            <x-ui.table.head>Origen</x-ui.table.head>
                            <x-ui.table.head>Viajes</x-ui.table.head>
                            <x-ui.table.head>Toneladas</x-ui.table.head>
                            <x-ui.table.head>KG/viaje</x-ui.table.head>
                            <x-ui.table.head>%</x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        <template x-for="fila in {{ $source }}" :key="fila.nombre">
                            <x-ui.table.row>
                                <x-ui.table.cell data-label="Origen" class="font-medium" x-text="fila.nombre"></x-ui.table.cell>
                                <x-ui.table.cell data-label="Viajes" class="tabular-nums" x-text="fila.pesajes"></x-ui.table.cell>
                                <x-ui.table.cell data-label="Toneladas" class="tabular-nums" x-text="fmt(fila.toneladas, 2)"></x-ui.table.cell>
                                <x-ui.table.cell data-label="KG/viaje" class="tabular-nums text-muted-foreground" x-text="fila.kg_por_viaje"></x-ui.table.cell>
                                <x-ui.table.cell data-label="%" class="tabular-nums text-muted-foreground" x-text="fila.porcentaje + '%'"></x-ui.table.cell>
                            </x-ui.table.row>
                        </template>
                    </x-ui.table.body>
                </x-ui.table>
            </div>
            <div class="xl:col-span-3 w-full max-w-xs sm:max-w-sm md:max-w-md xl:max-w-xs mx-auto" x-data="desgloseChart('{{ $source }}')">
                <div x-ref="chart"></div>
            </div>
        </div>
    </x-ui.card.content>
</x-ui.card>
