@props(['source', 'description' => 'Distribución de flota del día'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>Por tipo de vehículo</x-ui.card.title>
        <x-ui.card.description>{{ $description }}</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">
            <div class="xl:col-span-9 xl:order-2 min-w-0">

                {{-- Mobile: cards --}}
                <div class="sm:hidden space-y-1.5">
                    <template x-for="fila in {{ $source }}" :key="fila.nombre">
                        <div class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-border bg-background">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0 bg-muted-foreground/30" :style="desgloseColor('{{ $source }}', fila.nombre) ? { backgroundColor: desgloseColor('{{ $source }}', fila.nombre) } : {}"></span>
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <span class="font-medium text-sm truncate" x-text="fila.nombre"></span>
                                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <span><span class="tabular-nums" x-text="fila.pesajes"></span> viajes</span>
                                        <span>·</span>
                                        <span><span class="tabular-nums" x-text="fmt(fila.toneladas, 2)"></span> ton</span>
                                        <span>·</span>
                                        <span><span class="tabular-nums" x-text="fila.kg_por_viaje"></span> kg/v</span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-sm font-semibold tabular-nums shrink-0 text-muted-foreground" x-text="fila.porcentaje + '%'"></span>
                        </div>
                    </template>
                </div>

                {{-- Desktop: tabla --}}
                <x-ui.table variant="flat" class="hidden sm:block">
                    <x-ui.table.header>
                        <x-ui.table.row>
                            <x-ui.table.head>Tipo</x-ui.table.head>
                            <x-ui.table.head>Viajes</x-ui.table.head>
                            <x-ui.table.head>Toneladas</x-ui.table.head>
                            <x-ui.table.head>KG/viaje</x-ui.table.head>
                            <x-ui.table.head>Porcentaje</x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        <template x-for="fila in {{ $source }}" :key="fila.nombre">
                            <x-ui.table.row>
                                <x-ui.table.cell data-label="Tipo" class="font-medium">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full shrink-0 bg-muted-foreground/30" :style="desgloseColor('{{ $source }}', fila.nombre) ? { backgroundColor: desgloseColor('{{ $source }}', fila.nombre) } : {}"></span>
                                        <span x-text="fila.nombre"></span>
                                    </span>
                                </x-ui.table.cell>
                                <x-ui.table.cell data-label="Viajes" class="tabular-nums" x-text="fila.pesajes"></x-ui.table.cell>
                                <x-ui.table.cell data-label="Toneladas" class="tabular-nums" x-text="fmt(fila.toneladas, 2) + ' t'"></x-ui.table.cell>
                                <x-ui.table.cell data-label="kg/viaje" class="tabular-nums text-muted-foreground" x-text="fila.kg_por_viaje + ' kg'"></x-ui.table.cell>
                                <x-ui.table.cell data-label="Porcentaje" class="tabular-nums text-muted-foreground" x-text="fila.porcentaje + '%'"></x-ui.table.cell>
                            </x-ui.table.row>
                        </template>
                    </x-ui.table.body>
                </x-ui.table>
            </div>
            <div class="xl:col-span-3 xl:order-1 w-full max-w-xs sm:max-w-sm md:max-w-md xl:max-w-xs mx-auto" x-data="desgloseChart('{{ $source }}')">
                <div x-ref="chart"></div>
            </div>
        </div>
    </x-ui.card.content>
</x-ui.card>
