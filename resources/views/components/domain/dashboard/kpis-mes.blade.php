{{-- Desktop: 3 + 2 --}}
<div class="hidden xl:flex flex-col gap-4">

    <div class="grid grid-cols-3 gap-4">
        <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary" help="Cantidad de días con al menos un pesaje registrado.">
            <span x-text="kpisMes.dias_op"></span>
            <x-ui.popover side="top" align="start" width="w-56" class="mt-1.5 block">
                <x-slot:trigger>
                    <span x-bind:class="deltaBadgeClass(kpisMes.delta_dias_op)">
                        <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisMes.delta_dias_op !== null && kpisMes.delta_dias_op > 0" x-cloak />
                        <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisMes.delta_dias_op !== null && kpisMes.delta_dias_op < 0" x-cloak />
                        <span x-text="deltaBadgeText(kpisMes.delta_dias_op)"></span>
                    </span>
                </x-slot:trigger>
                <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisMes.delta_dias_op)">
                    <p class="text-xs text-muted-foreground">Mismo período mes anterior</p>
                    <p class="text-sm font-semibold" x-text="kpisMes.delta_dias_op_base > 0 ? kpisMes.delta_dias_op_base + ' días op.' : 'Sin actividad'"></p>
                </div>
            </x-ui.popover>
        </x-ui.kpi>

        <x-ui.kpi title="Pesajes del mes" icon="calendar-check" variant="primary" help="Total de pesajes registrados desde el 1° del mes.">
            <span x-text="fmt(kpisMes.total)"></span>
            <x-ui.popover side="top" align="start" width="w-56" class="mt-1.5 block">
                <x-slot:trigger>
                    <span x-bind:class="deltaBadgeClass(kpisMes.delta)">
                        <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisMes.delta !== null && kpisMes.delta > 0" x-cloak />
                        <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisMes.delta !== null && kpisMes.delta < 0" x-cloak />
                        <span x-text="deltaBadgeText(kpisMes.delta)"></span>
                    </span>
                </x-slot:trigger>
                <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisMes.delta)">
                    <p class="text-xs text-muted-foreground">Mismo período mes anterior</p>
                    <p class="text-sm font-semibold" x-text="kpisMes.delta_base > 0 ? fmt(kpisMes.delta_base) + ' pesajes' : 'Sin actividad'"></p>
                </div>
            </x-ui.popover>
        </x-ui.kpi>

        <x-ui.kpi title="Toneladas del mes" icon="package" variant="success" help="Suma de toneladas netas acumuladas en el mes.">
            <span x-text="fmt(kpisMes.toneladas, 1) + ' t'"></span>
            <x-ui.popover side="top" align="start" width="w-56" class="mt-1.5 block">
                <x-slot:trigger>
                    <span x-bind:class="deltaBadgeClass(kpisMes.delta_toneladas)">
                        <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisMes.delta_toneladas !== null && kpisMes.delta_toneladas > 0" x-cloak />
                        <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisMes.delta_toneladas !== null && kpisMes.delta_toneladas < 0" x-cloak />
                        <span x-text="deltaBadgeText(kpisMes.delta_toneladas)"></span>
                    </span>
                </x-slot:trigger>
                <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisMes.delta_toneladas)">
                    <p class="text-xs text-muted-foreground">Mismo período mes anterior</p>
                    <p class="text-sm font-semibold" x-text="kpisMes.delta_toneladas_base > 0 ? fmt(kpisMes.delta_toneladas_base, 1) + ' t' : 'Sin actividad'"></p>
                </div>
            </x-ui.popover>
        </x-ui.kpi>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary" help="Kilogramos netos acumulados en el mes por hectárea de zona de servicio.">
            <span x-text="kpisMes.kg_por_ha !== null ? fmt(kpisMes.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
            <span x-show="kpisMes.kg_por_ha !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisMes.kg_por_ha / 1000, 2) + ' t/ha'"></span>
            <x-ui.popover side="top" align="start" width="w-56" class="mt-1.5 block">
                <x-slot:trigger>
                    <span x-bind:class="deltaBadgeClass(kpisMes.delta_kg_por_ha)">
                        <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisMes.delta_kg_por_ha !== null && kpisMes.delta_kg_por_ha > 0" x-cloak />
                        <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisMes.delta_kg_por_ha !== null && kpisMes.delta_kg_por_ha < 0" x-cloak />
                        <span x-text="deltaBadgeText(kpisMes.delta_kg_por_ha)"></span>
                    </span>
                </x-slot:trigger>
                <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisMes.delta_kg_por_ha)">
                    <p class="text-xs text-muted-foreground">Mismo período mes anterior</p>
                    <p class="text-sm font-semibold" x-text="kpisMes.delta_kg_por_ha_base !== null ? fmt(kpisMes.delta_kg_por_ha_base, 1) + ' kg/ha' : 'Sin datos'"></p>
                </div>
            </x-ui.popover>
        </x-ui.kpi>

        <x-ui.kpi title="kg / persona" icon="users" variant="primary" help="Kilogramos netos acumulados en el mes por habitante de la zona de servicio.">
            <span x-text="kpisMes.kg_por_persona !== null ? fmt(kpisMes.kg_por_persona, 2) + ' kg' : '—'"></span>
            <span x-show="kpisMes.kg_por_persona !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisMes.kg_por_persona / 1000, 3) + ' t'"></span>
            <x-ui.popover side="top" align="start" width="w-56" class="mt-1.5 block">
                <x-slot:trigger>
                    <span x-bind:class="deltaBadgeClass(kpisMes.delta_kg_por_persona)">
                        <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisMes.delta_kg_por_persona !== null && kpisMes.delta_kg_por_persona > 0" x-cloak />
                        <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisMes.delta_kg_por_persona !== null && kpisMes.delta_kg_por_persona < 0" x-cloak />
                        <span x-text="deltaBadgeText(kpisMes.delta_kg_por_persona)"></span>
                    </span>
                </x-slot:trigger>
                <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisMes.delta_kg_por_persona)">
                    <p class="text-xs text-muted-foreground">Mismo período mes anterior</p>
                    <p class="text-sm font-semibold" x-text="kpisMes.delta_kg_por_persona_base !== null ? fmt(kpisMes.delta_kg_por_persona_base, 2) + ' kg' : 'Sin datos'"></p>
                </div>
            </x-ui.popover>
        </x-ui.kpi>
    </div>

</div>
