<x-domain.balanza.paso numero="1" titulo="Vehículo" completo="vehiculo" abierto="paso1Editando">

    <x-slot:resumen>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1.5 bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium">
                <span class="font-semibold" x-text="vehiculo?.patente"></span>
                <span class="text-muted-foreground" x-text="'· int. ' + vehiculo?.interno"></span>
            </span>
            <span class="inline-flex items-center bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium" x-text="vehiculo?.tipo"></span>
            <span class="inline-flex items-center bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium" x-text="vehiculo?.titular"></span>
            <span class="inline-flex items-center gap-1 bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium">
                Tara: <span class="font-semibold tabular-nums" x-text="vehiculo ? fmtKg(vehiculo.tara) : ''"></span>
            </span>
        </div>
    </x-slot:resumen>

    {{-- Input con popper --}}
    <div class="relative">
        <x-ui.input
            x-ref="inputVehiculo"
            x-model="query"
            @input="onQuery()"
            @focus="showSugg = true"
            @blur="setTimeout(() => showSugg = false, 150)"
            @keydown.enter.prevent="enterVehiculo()"
            placeholder="Ingresá la patente o el número interno del camión"
            autocomplete="off"
            x-bind:class="vehiculo ? 'bg-success-subtle text-success-subtle-foreground font-semibold border-success/50!' : ''"
        >
            <x-slot:leading>
                <x-lucide-search x-show="!vehiculo" class="size-4.5" />
                <x-lucide-circle-check x-show="vehiculo" x-cloak class="size-4.5 text-success" />
            </x-slot:leading>
        </x-ui.input>

        <div
            x-show="showSugg && matches.length > 0"
            x-cloak
            class="absolute left-0 right-0 top-full mt-1 bg-popover border border-border rounded-lg shadow-md overflow-hidden z-30 max-h-70 overflow-y-auto"
        >
            <template x-for="v in matches" :key="v.id">
                <div
                    class="px-4 py-4 cursor-pointer text-base flex flex-col gap-1 hover:bg-success-subtle transition-colors"
                    @mousedown.prevent="seleccionar(v)"
                >
                    <div>
                        <b class="font-semibold" x-text="v.patente"></b>
                        <span class="text-muted-foreground text-xs font-normal" x-text="' · int. ' + v.interno"></span>
                    </div>
                    <div class="text-xs text-muted-foreground tabular-nums">
                        <span x-text="v.tipo"></span> · Tara <span x-text="fmtKg(v.tara)"></span> · <span x-text="v.titular"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Info vehículo seleccionado --}}
    <div x-show="vehiculo" x-cloak class="flex flex-wrap gap-2 mt-3">
        <span class="inline-flex items-baseline gap-1.5 bg-success-subtle border border-success/20 rounded-full px-3.5 py-1.5 text-sm text-success-subtle-foreground">
            Tara: <b class="font-bold tabular-nums" x-text="vehiculo ? fmtKg(vehiculo.tara) : ''"></b>
        </span>
        <span class="inline-flex items-baseline gap-1.5 bg-success-subtle border border-success/20 rounded-full px-3.5 py-1.5 text-sm text-success-subtle-foreground">
            Tipo: <b class="font-bold" x-text="vehiculo?.tipo"></b>
        </span>
        <span class="inline-flex items-baseline gap-1.5 bg-success-subtle border border-success/20 rounded-full px-3.5 py-1.5 text-sm text-success-subtle-foreground">
            Titular: <b class="font-bold" x-text="vehiculo?.titular"></b>
        </span>
        <span class="inline-flex items-baseline gap-1.5 bg-success-subtle border border-success/20 rounded-full px-3.5 py-1.5 text-sm text-success-subtle-foreground">
            Interno: <b class="font-bold" x-text="vehiculo?.interno"></b>
        </span>
    </div>

</x-domain.balanza.paso>
