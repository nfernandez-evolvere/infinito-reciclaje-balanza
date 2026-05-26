<x-layouts.app title="Editar pesaje">

<div
    x-data="balanza(@js($initial))"
    x-init="init()"
    @keydown.window="onKey($event)"
    x-effect="setBeforeUnload(sucio)"
>

<div class="w-full pb-6">
    <div class="flex items-center flex-wrap gap-x-3 gap-y-1 mb-1">
        <x-ui.typography as="h1">Editar pesaje</x-ui.typography>
    </div>
    <x-ui.typography as="muted" class="mb-6">
        {{ $pesaje->vehiculo->patente }} · {{ $pesaje->tipoServicio->nombre }} · #{{ strtoupper(substr($pesaje->uuid, 0, 8)) }}
    </x-ui.typography>

    <div class="flex flex-col lg:flex-row lg:items-start gap-4 lg:gap-6">

        {{-- Columna izquierda: Pasos --}}
        <div class="flex-1 flex flex-col gap-4 min-w-0">

            {{-- Vehículo: solo lectura --}}
            <x-ui.card variant="elevated">
                <div class="flex items-center gap-3">
                    <div class="size-7 rounded-full grid place-items-center text-sm font-bold shrink-0 bg-success text-success-foreground">
                        <x-lucide-check class="size-4" />
                    </div>
                    <h3 class="text-[18px] font-semibold flex-1 leading-snug">Vehículo</h3>
                    <span class="text-xs text-muted-foreground px-2 py-0.5 rounded-md bg-muted">Solo lectura</span>
                </div>
                <div class="pt-3 flex flex-wrap gap-2">
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
            </x-ui.card>

            <x-domain.balanza.paso-servicio :servicios="$servicios" />
            <x-domain.balanza.paso-peso />

            {{-- Paso 4: Motivo y observaciones --}}
            <div id="paso-4" class="rounded-xl">
                <x-ui.card variant="elevated">
                    <div class="flex items-center gap-3">
                        <div
                            class="size-7 rounded-full grid place-items-center text-sm font-bold shrink-0 transition-all duration-300"
                            x-bind:class="motivo.trim() ? 'bg-success text-success-foreground' : 'bg-card border-2 border-primary text-primary'"
                        >
                            <x-lucide-check x-show="motivo.trim()" class="size-4" />
                            <span x-show="!motivo.trim()">4</span>
                        </div>
                        <h3 class="text-[18px] font-semibold flex-1 leading-snug">Motivo de la edición</h3>
                    </div>
                    <div class="pt-4 sm:pt-5 pb-4 sm:pb-6 flex flex-col gap-5">
                        <div class="space-y-2">
                            <x-ui.label>Motivo <span class="text-destructive">*</span></x-ui.label>
                            <x-ui.input
                                x-model="motivo"
                                placeholder="Ej.: corrección de datos, error en el peso bruto…"
                            />
                            <p x-show="!motivo.trim()" x-cloak class="text-xs text-destructive">Describí el motivo antes de guardar.</p>
                        </div>
                        <div class="space-y-2">
                            <x-ui.label>Observaciones <span class="font-normal text-muted-foreground">— opcional</span></x-ui.label>
                            <textarea
                                x-model="observaciones"
                                rows="2"
                                placeholder="Observaciones adicionales sobre el pesaje…"
                                class="flex min-h-15 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            ></textarea>
                        </div>
                    </div>
                </x-ui.card>
            </div>

        </div>

        {{-- Columna derecha: Resumen (solo desktop) --}}
        <div class="hidden lg:block lg:w-72 xl:w-80 shrink-0 lg:sticky lg:top-4">
            <x-domain.balanza.resumen-card />
        </div>

    </div>
</div>

<x-domain.balanza.mobile-drawer />

{{-- Form oculto — PUT --}}
<form method="POST" action="{{ route('pesajes.update', $pesaje) }}" x-ref="form" class="hidden">
    @csrf
    @method('PUT')
    <input type="hidden" name="tipo_servicio_id" x-bind:value="servicioId">
    <input type="hidden" name="zona_id"          x-bind:value="zonaId">
    <input type="hidden" name="turno"            x-bind:value="turno">
    <input type="hidden" name="peso_bruto_kg"    x-bind:value="brutoN">
    <input type="hidden" name="motivo"           x-bind:value="motivo">
    <input type="hidden" name="observaciones"    x-bind:value="observaciones">
</form>

<x-domain.balanza.action-bar />

</div>{{-- /x-data balanza --}}

</x-layouts.app>
