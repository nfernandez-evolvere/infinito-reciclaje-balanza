@props(['source', 'description' => 'Actividad del día por zona y turno. Una zona puede ocupar varias filas, una por turno.'])

{{--
    Desglose por zona y turno de un servicio. El select elige el servicio (siempre
    hay uno seleccionado por defecto — el primero); tabla y donut muestran solo las
    zonas de ese servicio. `servicioFiltro` es la fuente de verdad; el donut (hermano)
    se sincroniza por el evento window `desglose-servicio` y por su propio default.
--}}
<x-ui.card
    variant="elevated"
    x-data="{ servicioFiltro: '' }"
    x-init="servicioFiltro = servicioDefault('{{ $source }}')"
    x-effect="(() => {
        const ids = serviciosDesglose('{{ $source }}').map(s => String(s.id));
        if (ids.length && !ids.includes(String(servicioFiltro))) servicioFiltro = ids[0];
    })()"
>
    <x-ui.card.header>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="space-y-1.5">
                <x-ui.card.title>Por zona y turno</x-ui.card.title>
                <x-ui.card.description>{{ $description }}</x-ui.card.description>
            </div>

            {{-- Selector de servicio: cada zona pertenece a un servicio. Solo si hay más de uno. --}}
            <div x-show="serviciosDesglose('{{ $source }}').length > 1" x-cloak class="sm:w-56 sm:shrink-0">
                <div @select-change.stop="window.dispatchEvent(new CustomEvent('desglose-servicio', { detail: { source: '{{ $source }}', value: $event.detail.value } }))">
                    <x-ui.select x-modelable="value" x-model="servicioFiltro" size="sm">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Servicio…" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <template x-for="s in serviciosDesglose('{{ $source }}')" :key="s.id">
                                <div
                                    role="option"
                                    x-init="$dispatch('select-item-init', { value: String(s.id), label: s.nombre, disabled: false })"
                                    @click="select(String(s.id))"
                                    @mouseenter="focusIdx = items.findIndex(o => String(o.value) === String(s.id))"
                                    :class="{ 'bg-accent text-accent-foreground': focusIdx === items.findIndex(o => String(o.value) === String(s.id)) }"
                                    class="relative flex items-center select-none outline-none rounded-md pl-8 pr-2 py-1.5 text-sm hover:bg-primary/10 cursor-pointer"
                                >
                                    <span class="absolute left-2 flex items-center justify-center size-4" x-show="String(value) === String(s.id)" aria-hidden="true">
                                        <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                    </span>
                                    <span x-text="s.nombre"></span>
                                </div>
                            </template>
                        </x-ui.select.content>
                    </x-ui.select>
                </div>
            </div>
        </div>
    </x-ui.card.header>
    <x-ui.card.content class="pt-0">
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 items-start">
            <div class="xl:col-span-9 xl:order-2 min-w-0">

                {{-- Mobile: cards --}}
                <div class="sm:hidden space-y-1.5">
                    <template x-for="fila in desgloseFiltrado('{{ $source }}', servicioFiltro)" :key="fila.nombre">
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
                                    <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                        <span x-text="fila.kg_por_ha !== null ? fmt(fila.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
                                        <span>·</span>
                                        <span x-text="fila.kg_por_hab !== null ? fmt(fila.kg_por_hab, 2) + ' kg/hab' : '—'"></span>
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
                            <x-ui.table.head>Zona y turno</x-ui.table.head>
                            <x-ui.table.head>Viajes</x-ui.table.head>
                            <x-ui.table.head>Toneladas</x-ui.table.head>
                            <x-ui.table.head>kg/viaje</x-ui.table.head>
                            <x-ui.table.head>kg/ha</x-ui.table.head>
                            <x-ui.table.head>kg/hab</x-ui.table.head>
                            <x-ui.table.head>Porcentaje</x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        <template x-for="fila in desgloseFiltrado('{{ $source }}', servicioFiltro)" :key="fila.nombre">
                            <x-ui.table.row>
                                <x-ui.table.cell data-label="Zona y turno" class="font-medium">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full shrink-0 bg-muted-foreground/30" :style="desgloseColor('{{ $source }}', fila.nombre) ? { backgroundColor: desgloseColor('{{ $source }}', fila.nombre) } : {}"></span>
                                        <span x-text="fila.nombre"></span>
                                    </span>
                                </x-ui.table.cell>
                                <x-ui.table.cell data-label="Viajes" class="tabular-nums" x-text="fila.pesajes"></x-ui.table.cell>
                                <x-ui.table.cell data-label="Toneladas" class="tabular-nums" x-text="fmt(fila.toneladas, 2) + ' t'"></x-ui.table.cell>
                                <x-ui.table.cell data-label="kg/viaje" class="tabular-nums text-muted-foreground" x-text="fila.kg_por_viaje + ' kg'"></x-ui.table.cell>
                                <x-ui.table.cell data-label="kg/ha" class="tabular-nums">
                                    <span x-show="fila.kg_por_ha !== null" class="flex flex-col items-start sm:items-center gap-0.5">
                                        <span x-text="fmt(fila.kg_por_ha, 1) + ' kg'"></span>
                                        <span class="text-xs text-muted-foreground" x-text="fmt(fila.kg_por_ha / 1000, 2) + ' t/ha'"></span>
                                    </span>
                                    <span x-show="fila.kg_por_ha === null" class="text-muted-foreground">—</span>
                                </x-ui.table.cell>
                                <x-ui.table.cell data-label="kg/hab" class="tabular-nums">
                                    <span x-show="fila.kg_por_hab !== null" class="flex flex-col items-start sm:items-center gap-0.5">
                                        <span x-text="fmt(fila.kg_por_hab, 2) + ' kg'"></span>
                                        <span class="text-xs text-muted-foreground" x-text="fmt(fila.kg_por_hab / 1000, 3) + ' t/hab'"></span>
                                    </span>
                                    <span x-show="fila.kg_por_hab === null" class="text-muted-foreground">—</span>
                                </x-ui.table.cell>
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
