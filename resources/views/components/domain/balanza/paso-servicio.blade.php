@props(['servicios'])

<x-domain.balanza.paso numero="2" titulo="Tipo de servicio y origen" completo="servicioCompleto" inactivo="!vehiculo" abierto="paso2Editando">

    <x-slot:resumen>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium" x-text="servicioNombre"></span>
            <span class="inline-flex items-center bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium" x-text="zonaNombre"></span>
            <span x-show="turno" x-cloak class="inline-flex items-center bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium" x-text="turno"></span>
        </div>
    </x-slot:resumen>

    <div class="flex flex-col gap-5">
        {{-- Select servicio --}}
        <div class="flex flex-col gap-2.5">
            <x-ui.label>Tipo de servicio</x-ui.label>
            <div x-ref="wrapServicio" @select-change.stop="onSelectServicio($event.detail)">
                <x-ui.select>
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Seleccionar servicio…" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        @foreach($servicios as $s)
                            <x-ui.select.item value="{{ $s->id }}">{{ $s->nombre }}</x-ui.select.item>
                        @endforeach
                    </x-ui.select.content>
                </x-ui.select>
            </div>
        </div>

        {{-- Select origen --}}
        <div x-show="servicioId" x-cloak class="flex flex-col gap-2.5">
            <x-ui.label>Origen</x-ui.label>
            <div x-ref="wrapOrigen" @select-change.stop="onZonaChange($event.detail)">
                <x-ui.select>
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Seleccionar origen…" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <template x-for="z in zonasDisponibles" :key="z.id">
                            <div
                                role="option"
                                x-init="$dispatch('select-item-init', { value: String(z.id), label: z.nombre, disabled: false })"
                                :aria-selected="String(value) === String(z.id)"
                                @click="select(String(z.id))"
                                @mouseenter="focusIdx = items.findIndex(o => String(o.value) === String(z.id))"
                                :class="{ 'bg-accent text-accent-foreground': focusIdx === items.findIndex(o => String(o.value) === String(z.id)) }"
                                class="relative flex items-center select-none outline-none rounded-md pl-8 pr-2 py-2.5 text-base hover:bg-primary/10 cursor-pointer"
                            >
                                <span class="absolute left-2 flex items-center justify-center size-4" x-show="String(value) === String(z.id)" aria-hidden="true">
                                    <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                </span>
                                <span x-text="z.nombre"></span>
                            </div>
                        </template>
                    </x-ui.select.content>
                </x-ui.select>
            </div>
        </div>

        {{-- Select turno --}}
        <div x-show="turnosDisponibles.length > 0" x-cloak class="flex flex-col gap-2.5">
            <x-ui.label>Turno <span class="font-normal text-muted-foreground ml-1">— requerido para este servicio.</span></x-ui.label>
            <div x-ref="wrapTurno" @select-change.stop="turno = $event.detail.value">
                <x-ui.select>
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Seleccionar turno…" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <template x-for="t in turnosDisponibles" :key="t">
                            <div
                                role="option"
                                x-init="$dispatch('select-item-init', { value: t, label: t, disabled: false })"
                                :aria-selected="value === t"
                                @click="select(t)"
                                @mouseenter="focusIdx = items.findIndex(o => o.value === t)"
                                :class="{ 'bg-accent text-accent-foreground': focusIdx === items.findIndex(o => o.value === t) }"
                                class="relative flex items-center select-none outline-none rounded-md pl-8 pr-2 py-2.5 text-base hover:bg-primary/10 cursor-pointer"
                            >
                                <span class="absolute left-2 flex items-center justify-center size-4" x-show="value === t" aria-hidden="true">
                                    <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                </span>
                                <span x-text="t"></span>
                            </div>
                        </template>
                    </x-ui.select.content>
                </x-ui.select>
            </div>
        </div>
    </div>

    {{-- Badge tipos habituales --}}
    <div x-show="tiposSugeridos.length" x-cloak class="flex flex-wrap items-center gap-2 mt-3">
        <span class="inline-flex items-baseline gap-1.5 bg-info-subtle border border-info-border rounded-md px-2.5 py-1.5 text-xs text-info-subtle-foreground">
            <span x-text="tiposSugeridos.length === 1 ? 'Tipo habitual:' : 'Tipos habituales:'"></span>
            <b class="font-semibold" x-text="tiposSugeridos.join(', ')"></b>
        </span>
    </div>

    {{-- Cascade warning --}}
    <div x-show="tipoMismatch" x-cloak
        class="mt-3 bg-warning-subtle border-l-[3px] border-warning rounded-md px-3 py-2.5 text-sm text-foreground flex gap-2 items-start"
    >
        <x-lucide-triangle-alert class="size-4 shrink-0 mt-0.5 text-warning" />
        <div>
            <b>No es un tipo habitual para este servicio.</b><br>
            Para <b x-text="servicioNombre"></b> se espera
            <span x-text="tiposSugeridos.length === 1 ? 'un' : 'uno de'"></span>
            <b x-text="tiposSugeridos.join(', ')"></b>; este vehículo es <b x-text="vehiculo?.tipo"></b>. El pesaje se guarda igual.
        </div>
    </div>

</x-domain.balanza.paso>
