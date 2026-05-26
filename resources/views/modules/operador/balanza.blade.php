<x-layouts.app title="Pesaje">

<div
    x-data="balanza()"
    x-init="init()"
    @keydown.window="onKey($event)"
    x-effect="setBeforeUnload(sucio)"
>

<div class="mx-auto max-w-220 w-full pb-6">

    {{-- ── Encabezado ── --}}
    <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1 mb-1">
        <x-ui.typography as="h1" class="text-2xl sm:text-4xl">Registro de pesaje</x-ui.typography>
        <span class="inline-flex items-center gap-1.5 h-6 px-2.5 rounded-full text-[11px] font-bold tracking-widest uppercase bg-success-subtle text-success-subtle-foreground">
            <span class="size-2 rounded-full bg-success inline-block"></span>
            Balanza en línea
        </span>
    </div>
    <x-ui.typography as="muted" class="mb-4">Seguí los tres pasos. Los datos del padrón se completan solos.</x-ui.typography>

<div class="flex flex-col gap-4">

        {{-- ── Paso 1 — Vehículo ── --}}
        <x-domain.balanza.paso numero="1" titulo="Vehículo" completo="vehiculo">

            {{-- Input con popper --}}
            <div class="relative">
                <x-ui.input
                    size="lg"
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
                    class="absolute left-0 right-0 top-full mt-1 bg-popover border border-border rounded-lg shadow-md overflow-hidden z-30 max-h-[280px] overflow-y-auto"
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

        {{-- ── Paso 2 — Servicio + Origen + Turno ── --}}
        <x-domain.balanza.paso numero="2" titulo="Tipo de servicio y origen" completo="servicioCompleto" inactivo="!vehiculo">

            <div class="flex flex-col gap-5">
                {{-- Select servicio --}}
                <div class="flex flex-col gap-2.5">
                    <x-ui.label>Tipo de servicio</x-ui.label>
                    {{-- wrapper con x-ref en scope de balanza (sin x-data propio) --}}
                    <div x-ref="wrapServicio" @select-change.stop="onSelectServicio($event.detail)">
                        <x-ui.select size="lg">
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
                    {{-- wrapper con x-ref en scope de balanza (sin x-data propio) --}}
                    <div x-ref="wrapOrigen" @select-change.stop="onZonaChange($event.detail)">
                        <x-ui.select size="lg">
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
                    {{-- wrapper con x-ref en scope de balanza (sin x-data propio) --}}
                    <div x-ref="wrapTurno" @select-change.stop="turno = $event.detail.value">
                        <x-ui.select size="lg">
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

            {{-- Badge tipo habitual --}}
            <div x-show="tipoSugerido" x-cloak class="flex flex-wrap items-center gap-2 mt-3">
                <span class="inline-flex items-baseline gap-1.5 bg-info-subtle border border-info-border rounded-md px-2.5 py-1.5 text-xs text-info-subtle-foreground">
                    Tipo habitual: <b class="font-semibold" x-text="tipoSugerido"></b>
                </span>
            </div>

            {{-- Cascade warning --}}
            <div x-show="tipoMismatch" x-cloak
                class="mt-3 bg-warning-subtle border-l-[3px] border-warning rounded-md px-3 py-2.5 text-sm text-foreground flex gap-2 items-start"
            >
                <x-lucide-triangle-alert class="size-4 shrink-0 mt-0.5 text-warning" />
                <div>
                    <b>No es el tipo habitual para este servicio.</b><br>
                    Para <b x-text="servicioNombre"></b> se espera un <b x-text="tipoSugerido"></b>; este vehículo es <b x-text="vehiculo?.tipo"></b>. El pesaje se guarda igual.
                </div>
            </div>

        </x-domain.balanza.paso>

        {{-- ── Paso 3 — Peso bruto ── --}}
        <x-domain.balanza.paso numero="3" titulo="Peso bruto" completo="brutoN > 0" inactivo="!servicioId">

            {{-- Readout shell --}}
            <div
                class="border rounded-lg p-4 sm:p-5 flex flex-col gap-4 sm:flex-row sm:items-end transition-all duration-150"
                x-bind:class="outOfRange
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
                            x-bind:class="outOfRange ? 'text-warning-subtle-foreground' : inRange ? 'text-success-subtle-foreground' : 'text-foreground'"
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

            {{-- Hint rango --}}
            <div x-show="vehiculo?.peso_min" x-cloak class="mt-2.5 text-[13px]"
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

        {{-- ── Resumen ── --}}
        <div
            class="rounded-lg p-6 shadow-sm border transition-all duration-200"
            x-bind:class="canSave
                ? 'bg-success-subtle border-success-border'
                : 'bg-card border-border'"
        >
            <div class="flex flex-col justify-between mb-4">
                <div class="text-sm font-semibold text-foreground">Resumen del pesaje</div>
                <span class="text-xs text-muted-foreground" x-text="fechaHoraActual"></span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                @foreach([
                    ['label' => 'Vehículo',    'key' => "vehiculo?.patente ?? '—'",               'num' => false],
                    ['label' => 'Servicio',     'key' => "servicioNombre || '—'",                  'num' => false],
                    ['label' => 'Origen',       'key' => "zonaNombre || '—'",                       'num' => false],
                    ['label' => 'Turno',        'key' => "turno || '—'",                            'num' => false],
                    ['label' => 'Tipo',         'key' => "vehiculo?.tipo ?? '—'",                   'num' => false],
                    ['label' => 'Peso bruto',   'key' => "brutoN > 0 ? fmtKg(brutoN) : '—'",       'num' => true],
                    ['label' => 'Tara',         'key' => "vehiculo ? fmtKg(vehiculo.tara) : '—'",  'num' => true],
                ] as $cell)
                    <div>
                        <div class="text-overline mb-1">{{ $cell['label'] }}</div>
                        <div class="{{ $cell['num'] ? 'tabular-nums font-mono' : '' }} text-[15px] font-semibold text-foreground"
                            x-text="{{ $cell['key'] }}"></div>
                    </div>
                @endforeach

                {{-- Neto — destacado --}}
                <div>
                    <div class="text-overline mb-1">Neto</div>
                    <div
                        class="tabular-nums font-mono font-bold"
                        style="font-size:22px;line-height:1.1"
                        x-bind:class="canSave ? 'text-success' : 'text-foreground'"
                        x-text="vehiculo && brutoN > 0 ? fmtKg(neto) : '—'"
                    ></div>
                </div>

                {{-- Operador --}}
                <div class="col-span-2 sm:col-span-4">
                    <div class="text-overline mb-1">Operador</div>
                    <div class="text-[15px] font-semibold text-foreground">{{ auth()->user()->name }}</div>
                </div>
            </div>
        </div>

    </div>{{-- /flex col --}}

</div>{{-- /max-w-220 --}}

{{-- Form oculto — submit SSR --}}
<form method="POST" action="{{ route('pesajes.store') }}" x-ref="form" class="hidden">
    @csrf
    <input type="hidden" name="vehiculo_id"      x-bind:value="vehiculo?.id">
    <input type="hidden" name="tipo_servicio_id" x-bind:value="servicioId">
    <input type="hidden" name="zona_id"          x-bind:value="zonaId">
    <input type="hidden" name="turno"            x-bind:value="turno">
    <input type="hidden" name="peso_bruto_kg"    x-bind:value="brutoN">
</form>

{{-- ── Barra de acción (teleportada fuera del scroll container) ── --}}
<template x-teleport="#layout-action-bar">
    <div class="bg-card border-t border-border px-4 sm:px-6 py-2 flex items-center gap-3">
        <x-ui.button variant="outline" size="lg" @click="limpiar()">
            <x-lucide-rotate-ccw class="size-4" />
            Limpiar
        </x-ui.button>

        <div class="flex-1"></div>

        <span class="text-sm font-medium text-muted-foreground" x-text="hintContextual"></span>

        <x-ui.button size="lg" class="uppercase tracking-widest font-bold" @click="guardar()" x-bind:disabled="!canSave">
            <x-lucide-save class="size-4" />
            Guardar pesaje
        </x-ui.button>
    </div>
</template>


</div>{{-- /x-data balanza --}}

@if(!auth()->user()->onboarding_visto)
    <x-onboarding.bienvenida-operador :forzar="true" />
@else
    <x-onboarding.bienvenida-operador :forzar="false" />
@endif

</x-layouts.app>

