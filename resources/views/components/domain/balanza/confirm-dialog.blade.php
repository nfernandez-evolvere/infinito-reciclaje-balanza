{{--
    Bridge: expone `open` (que espera x-ui.dialog.content) mapeado a `confirmOpen` del scope padre (balanza()).
    Alpine 3 resuelve `confirmOpen` subiendo por la cadena de prototipos de scopes.
--}}
<div x-data="{
    get open() { return confirmOpen },
    set open(v) { confirmOpen = v }
}">
    <x-ui.dialog.content :showCloseButton="false">

        <x-ui.dialog.header>
            <x-ui.dialog.title>Confirmar pesaje</x-ui.dialog.title>
            <x-ui.dialog.description>Revisá los datos antes de guardar.</x-ui.dialog.description>
        </x-ui.dialog.header>

        <div class="px-6 py-4 grid grid-cols-2 gap-x-6 gap-y-4">
            <div class="col-span-2">
                <div class="text-overline mb-0.5">Vehículo</div>
                <div class="text-sm font-semibold" x-text="vehiculo?.patente"></div>
                <div class="text-xs text-muted-foreground" x-text="'Int. ' + vehiculo?.interno + ' · ' + vehiculo?.titular + ' · ' + vehiculo?.tipo"></div>
            </div>
            <div>
                <div class="text-overline mb-0.5">Servicio</div>
                <div class="text-sm font-semibold" x-text="servicioNombre"></div>
            </div>
            <div>
                <div class="text-overline mb-0.5">Origen</div>
                <div class="text-sm font-semibold" x-text="zonaNombre"></div>
            </div>
            <template x-if="turno">
                <div class="col-span-2">
                    <div class="text-overline mb-0.5">Turno</div>
                    <div class="text-sm font-semibold" x-text="turno"></div>
                </div>
            </template>
            <div>
                <div class="text-overline mb-0.5">Peso bruto</div>
                <div class="text-sm font-mono tabular-nums" x-text="fmtKg(brutoN)"></div>
            </div>
            <div>
                <div class="text-overline mb-0.5">Tara</div>
                <div class="text-sm font-mono tabular-nums" x-text="fmtKg(vehiculo?.tara)"></div>
            </div>
            <div class="col-span-2 pt-3 border-t border-border">
                <div class="text-overline mb-1">Peso neto estimado</div>
                <div class="text-3xl font-bold font-mono tabular-nums text-success" x-text="fmtKg(neto)"></div>
            </div>
        </div>

        <x-ui.dialog.footer>
            <x-ui.button variant="ghost" @click="open = false">
                <x-lucide-arrow-left class="size-4" />
                Revisar
            </x-ui.button>
            <x-ui.button @click="confirmar()">
                <x-lucide-check class="size-4" />
                Confirmar pesaje
            </x-ui.button>
        </x-ui.dialog.footer>

    </x-ui.dialog.content>
</div>
