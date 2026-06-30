@props([
    'source' => null,
    'zonas' => null,
    'servicios' => null, // lista completa de servicios activos [{id, nombre}] para el selector
    'title' => 'Mapa de calor',
    'description' => 'Intensidad de recolección por zona, sumando todos sus turnos. Elegí la métrica para ver dónde se concentra la actividad.',
])

{{--
    Panel de mapa de calor embebible (Dashboard y Reportes), en una card elevada
    para mantener consistencia con los desgloses (x-domain.dashboard.desglose-zona).
    Reutiliza el selector de métricas, el mapa y el ranking. El dataset llega por:
      - source: clave de window.__dashboardData (Dashboard, reactivo)
      - zonas:  colección del informe (Reportes, estático)
    El empty-state lo decide Alpine (hayMapa) para servir ambos modos con la misma
    plantilla.
--}}
<div
    x-data="mapaCalor(@js(array_merge(
        $source ? ['source' => $source] : ['zonas' => $zonas ?? []],
        ['servicios' => $servicios ?? []]
    )))"
    x-cloak
>
    <x-ui.card variant="elevated">
        <x-ui.card.header>
            <x-ui.card.title>{{ $title }}</x-ui.card.title>
            <x-ui.card.description>{{ $description }}</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="pt-0">

            {{-- Sin zonas con geometría --}}
            <div x-show="!hayMapa" class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <x-lucide-map class="size-6" />
                </div>
                <div class="space-y-1">
                    <p class="text-sm font-medium">Todavía no hay zonas en el mapa</p>
                    <p class="max-w-96 text-sm text-muted-foreground">
                        Dibujá el área de tus zonas para ver el mapa de calor. Cada zona con polígono se colorea según la métrica elegida.
                    </p>
                </div>
                <x-ui.button size="sm" :href="route('admin.tipos-servicio.index')" class="mt-1">
                    <x-lucide-map-pin class="size-4" />
                    Ir a Servicios
                </x-ui.button>
            </div>

            {{-- Mapa --}}
            <div x-show="hayMapa" class="flex flex-col gap-4">

                {{-- Métricas (izquierda) + selector de servicio (derecha). Cada zona
                     pertenece a un servicio, así que el mapa muestra uno por vez para
                     no superponer la misma área en distintos servicios. --}}
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                    <x-domain.mapa-calor.selector-metrica />

                    <div x-show="hayVarios" x-cloak class="flex flex-col gap-1.5 sm:w-56 sm:shrink-0">
                        <label class="text-xs font-medium text-muted-foreground">Servicio</label>
                        <div @select-change.stop="setServicio($event.detail.value)">
                            <x-ui.select x-modelable="value" x-model="servicioFilter" size="sm">
                                <x-ui.select.trigger>
                                    <x-ui.select.value placeholder="Servicio…" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    <template x-for="s in serviciosDisponibles" :key="s.id">
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

                {{-- Servicio elegido sin zonas: nada que mapear --}}
                <div x-show="sinZonasEnServicio" x-cloak class="flex flex-col items-center justify-center gap-3 py-10 text-center">
                    <div class="flex size-12 items-center justify-center rounded-full bg-primary/10 text-primary">
                        <x-lucide-map-pin class="size-6" />
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-medium">Este servicio todavía no tiene zonas</p>
                        <p class="max-w-96 text-sm text-muted-foreground">
                            Agregá las zonas de este servicio para verlo en el mapa de calor.
                        </p>
                    </div>
                    <x-ui.button size="sm" :href="route('admin.tipos-servicio.index')" class="mt-1">
                        <x-lucide-map-pin class="size-4" />
                        Ir a Servicios
                    </x-ui.button>
                </div>

                <div x-show="!sinZonasEnServicio" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        <x-domain.mapa-calor.mapa />
                    </div>
                    <div>
                        <x-domain.mapa-calor.lista />
                    </div>
                </div>
            </div>

        </x-ui.card.content>
    </x-ui.card>
</div>
