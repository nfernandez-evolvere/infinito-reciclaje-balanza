{{-- Desktop: grid --}}
<div class="hidden xl:grid grid-cols-3 gap-4">

    <x-ui.kpi title="Pesajes hoy" icon="scale" variant="primary" help="Total de pesajes registrados en el día.">
        <span x-text="fmt(kpisDia.total)"></span>
        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
            <x-slot:trigger>
                <span x-bind:class="deltaBadgeClass(kpisDia.delta)">
                    <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisDia.delta !== null && kpisDia.delta > 0" x-cloak />
                    <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisDia.delta !== null && kpisDia.delta < 0" x-cloak />
                    <span x-text="deltaBadgeText(kpisDia.delta)"></span>
                </span>
            </x-slot:trigger>
            <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisDia.delta)">
                <p class="text-xs text-muted-foreground">Mismo día mes anterior</p>
                <p class="text-sm font-semibold" x-text="kpisDia.delta_base > 0 ? fmt(kpisDia.delta_base) + ' pesajes' : 'Sin actividad ese día'"></p>
            </div>
        </x-ui.popover>
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas netas" icon="weight" variant="success" help="Suma de pesos netos de todos los pesajes del día.">
        <span x-text="fmt(kpisDia.toneladas, 1) + ' t'"></span>
        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
            <x-slot:trigger>
                <span x-bind:class="deltaBadgeClass(kpisDia.delta_toneladas)">
                    <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisDia.delta_toneladas !== null && kpisDia.delta_toneladas > 0" x-cloak />
                    <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisDia.delta_toneladas !== null && kpisDia.delta_toneladas < 0" x-cloak />
                    <span x-text="deltaBadgeText(kpisDia.delta_toneladas)"></span>
                </span>
            </x-slot:trigger>
            <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisDia.delta_toneladas)">
                <p class="text-xs text-muted-foreground">Mismo día mes anterior</p>
                <p class="text-sm font-semibold" x-text="kpisDia.delta_toneladas_base > 0 ? fmt(kpisDia.delta_toneladas_base, 1) + ' t' : 'Sin actividad ese día'"></p>
            </div>
        </x-ui.popover>
    </x-ui.kpi>

    <x-ui.kpi title="Promedio / viaje" icon="trending-up" variant="success" help="Toneladas netas promedio por pesaje registrado hoy.">
        <span x-text="fmt(kpisDia.promedio, 2) + ' t'"></span>
        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
            <x-slot:trigger>
                <span x-bind:class="deltaBadgeClass(kpisDia.delta_promedio)">
                    <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisDia.delta_promedio !== null && kpisDia.delta_promedio > 0" x-cloak />
                    <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisDia.delta_promedio !== null && kpisDia.delta_promedio < 0" x-cloak />
                    <span x-text="deltaBadgeText(kpisDia.delta_promedio)"></span>
                </span>
            </x-slot:trigger>
            <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisDia.delta_promedio)">
                <p class="text-xs text-muted-foreground">Mismo día mes anterior</p>
                <p class="text-sm font-semibold" x-text="kpisDia.delta_promedio_base !== null ? fmt(kpisDia.delta_promedio_base, 2) + ' t/viaje' : 'Sin actividad ese día'"></p>
            </div>
        </x-ui.popover>
    </x-ui.kpi>

    <x-ui.kpi title="Último pesaje" icon="timer" variantExpr="ultimoVariant(kpisDia.ultimo_hace_min)" help="Minutos transcurridos desde el último pesaje registrado hoy.">
        <span :class="ultimoClass(kpisDia.ultimo_hace_min)" x-text="ultimoLabel(kpisDia.ultimo_hace_min)"></span>
        <p class="text-xs font-normal mt-0.5"
           :class="ultimoClass(kpisDia.ultimo_hace_min) === 'text-success' ? 'text-success/80' :
                   ultimoClass(kpisDia.ultimo_hace_min) === 'text-warning' ? 'text-muted-foreground' :
                   ultimoClass(kpisDia.ultimo_hace_min) === 'text-destructive' ? 'text-destructive/80' :
                   'text-muted-foreground'"
           x-text="kpisDia.ultimo_hace_min === null ? 'Sin actividad hoy' :
                   kpisDia.ultimo_hace_min < 180 ? 'Operación activa' :
                   kpisDia.ultimo_hace_min < 480 ? 'Sin pesajes recientes' :
                   'Sin actividad reciente'">
        </p>
    </x-ui.kpi>

    <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary" help="Kilogramos netos recolectados hoy por hectárea de zona de servicio.">
        <span class="inline-flex items-baseline gap-1.5">
            <span x-text="kpisDia.kg_por_ha !== null ? fmt(kpisDia.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
            <span x-show="kpisDia.kg_por_ha !== null" class="text-base font-normal text-muted-foreground" x-text="fmt(kpisDia.kg_por_ha / 1000, 2) + ' t/ha'"></span>
        </span>
        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
            <x-slot:trigger>
                <span x-bind:class="deltaBadgeClass(kpisDia.delta_kg_por_ha)">
                    <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisDia.delta_kg_por_ha !== null && kpisDia.delta_kg_por_ha > 0" x-cloak />
                    <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisDia.delta_kg_por_ha !== null && kpisDia.delta_kg_por_ha < 0" x-cloak />
                    <span x-text="deltaBadgeText(kpisDia.delta_kg_por_ha)"></span>
                </span>
            </x-slot:trigger>
            <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisDia.delta_kg_por_ha)">
                <p class="text-xs text-muted-foreground">Mismo día mes anterior</p>
                <p class="text-sm font-semibold" x-text="kpisDia.delta_kg_por_ha_base !== null ? fmt(kpisDia.delta_kg_por_ha_base, 1) + ' kg/ha' : 'Sin datos'"></p>
            </div>
        </x-ui.popover>
    </x-ui.kpi>

    <x-ui.kpi title="kg / persona" icon="users" variant="primary" help="Kilogramos netos recolectados hoy por habitante de la zona de servicio.">
        <span class="inline-flex items-baseline gap-1.5">
            <span x-text="kpisDia.kg_por_persona !== null ? fmt(kpisDia.kg_por_persona, 2) + ' kg' : '—'"></span>
            <span x-show="kpisDia.kg_por_persona !== null" class="text-base font-normal text-muted-foreground" x-text="fmt(kpisDia.kg_por_persona / 1000, 3) + ' t'"></span>
        </span>
        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
            <x-slot:trigger>
                <span x-bind:class="deltaBadgeClass(kpisDia.delta_kg_por_persona)">
                    <x-lucide-trending-up class="size-3 shrink-0" x-show="kpisDia.delta_kg_por_persona !== null && kpisDia.delta_kg_por_persona > 0" x-cloak />
                    <x-lucide-trending-down class="size-3 shrink-0" x-show="kpisDia.delta_kg_por_persona !== null && kpisDia.delta_kg_por_persona < 0" x-cloak />
                    <span x-text="deltaBadgeText(kpisDia.delta_kg_por_persona)"></span>
                </span>
            </x-slot:trigger>
            <div class="pl-3 space-y-0.5" x-bind:style="deltaBorderStyle(kpisDia.delta_kg_por_persona)">
                <p class="text-xs text-muted-foreground">Mismo día mes anterior</p>
                <p class="text-sm font-semibold" x-text="kpisDia.delta_kg_por_persona_base !== null ? fmt(kpisDia.delta_kg_por_persona_base, 2) + ' kg' : 'Sin datos'"></p>
            </div>
        </x-ui.popover>
    </x-ui.kpi>

</div>
