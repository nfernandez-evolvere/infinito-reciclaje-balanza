<x-domain.balanza.paso numero="3" titulo="Peso bruto" completo="brutoN > 0" inactivo="!servicioId" abierto="paso3Editando">

    <x-slot:resumen>
        <div class="flex flex-wrap gap-2">
            <span class="inline-flex items-center gap-1 bg-muted/50 border border-border/60 rounded-full px-3 py-1 text-xs font-medium">
                Bruto: <span class="font-semibold tabular-nums" x-text="fmtKg(brutoN)"></span>
            </span>
            <span class="inline-flex items-center gap-1 bg-success-subtle border border-success/20 rounded-full px-3 py-1 text-xs font-medium text-success-subtle-foreground">
                Neto: <span class="font-bold tabular-nums" x-text="fmtKg(neto)"></span>
            </span>
        </div>
    </x-slot:resumen>

    {{-- Readout shell --}}
    <div
        class="border rounded-lg p-4 sm:p-5 flex flex-col gap-4 sm:flex-row sm:items-end transition-all duration-150"
        x-bind:class="belowTara || aboveHardLimit
            ? 'bg-destructive/5 border-destructive'
            : outOfRange
                ? 'bg-warning-subtle border-warning'
                : inRange
                    ? 'bg-success-subtle border-success-border'
                    : 'bg-muted/40 border-border'"
    >
        <div class="flex-1">
            <div class="text-[11px] font-semibold text-muted-foreground tracking-widest uppercase mb-1.5">Peso bruto en balanza</div>
            <div class="flex items-baseline gap-2">
                <input
                    x-ref="inputBruto"
                    x-model="bruto"
                    @input="onBruto()"
                    @keydown.enter.prevent="canSave && guardar()"
                    inputmode="numeric"
                    placeholder="0"
                    class="w-full bg-transparent border-none outline-none p-0 text-right font-bold tabular-nums transition-colors duration-150 placeholder:text-foreground/20"
                    style="font-size:clamp(44px,10vw,72px);line-height:1;letter-spacing:-0.03em"
                    x-bind:class="belowTara || aboveHardLimit ? 'text-destructive' : outOfRange ? 'text-warning-subtle-foreground' : inRange ? 'text-success-subtle-foreground' : 'text-foreground'"
                />
                <span class="font-semibold text-muted-foreground pb-1" style="font-size:clamp(20px,4vw,28px)">kg</span>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:gap-1.5 sm:min-w-50 border-t sm:border-t-0 sm:border-l border-border pt-3 sm:pt-0 sm:pl-5">
            <div class="flex flex-1 sm:flex-none justify-between items-baseline text-sm">
                <span class="text-muted-foreground">Tara</span>
                <b class="font-semibold tabular-nums text-foreground ml-3" x-text="vehiculo ? fmtKg(vehiculo.tara) : '—'"></b>
            </div>
            <div class="flex flex-1 sm:flex-none justify-between items-baseline text-sm sm:pt-1.5 sm:mt-1 sm:border-t sm:border-border">
                <span class="text-muted-foreground">Neto est.</span>
                <b class="font-semibold tabular-nums text-success ml-3 text-lg sm:text-[22px]" x-text="vehiculo && brutoN > 0 ? fmtKg(neto) : '—'"></b>
            </div>
        </div>
    </div>

    {{-- Error: peso bruto menor a tara --}}
    <div x-show="belowTara" x-cloak class="mt-2.5 flex items-center gap-1.5 text-[13px] font-medium text-destructive">
        <x-lucide-circle-alert class="size-3.5 shrink-0" />
        <span>
            El peso bruto no puede ser menor a la tara del vehículo
            (<span x-text="fmtKg(vehiculo?.tara)"></span>).
        </span>
    </div>

    {{-- Error: peso bruto muy por encima del máximo (probable error de carga) --}}
    <div x-show="aboveHardLimit && !belowTara" x-cloak class="mt-2.5 flex items-center gap-1.5 text-[13px] font-medium text-destructive">
        <x-lucide-circle-alert class="size-3.5 shrink-0" />
        <span>
            El peso bruto supera el máximo permitido para <span x-text="vehiculo?.tipo"></span>
            (<span x-text="fmtKg(vehiculo?.peso_tope)"></span>). Revisá el valor ingresado.
        </span>
    </div>

    {{-- Hint rango --}}
    <div x-show="vehiculo?.peso_min && !belowTara && !aboveHardLimit" x-cloak class="mt-2.5 text-[13px]"
        x-bind:class="outOfRange ? 'text-warning-subtle-foreground' : 'text-muted-foreground'"
    >
        <template x-if="outOfRange">
            <span>
                <b>Fuera del rango habitual para <span x-text="vehiculo?.tipo"></span></b>
                (<span x-text="fmtN(vehiculo?.peso_min)"></span> – <span x-text="fmtN(vehiculo?.peso_max)"></span> kg).
                La validación no bloquea el guardado.
            </span>
        </template>
        <template x-if="!outOfRange">
            <span>
                Rango habitual <span x-text="vehiculo?.tipo"></span>:
                <span x-text="fmtN(vehiculo?.peso_min)"></span> – <span x-text="fmtN(vehiculo?.peso_max)"></span> kg.
            </span>
        </template>
    </div>

</x-domain.balanza.paso>
