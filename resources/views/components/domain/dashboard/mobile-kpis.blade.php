<div class="sm:hidden">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="ghost" size="icon">
                <x-lucide-chart-bar class="size-4" />
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-4 overflow-y-auto max-h-[85vh]">

            {{-- Hoy --}}
            <div x-show="active === 'hoy'" class="space-y-4">
                <p class="text-label text-base">KPIs del día</p>
                <div class="grid grid-cols-1 gap-3">
                    <x-ui.kpi title="Pesajes hoy" icon="scale" variant="primary">
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
                    <x-ui.kpi title="Toneladas netas" icon="weight" variant="success">
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
                    <x-ui.kpi title="Promedio / viaje" icon="trending-up" variant="success">
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
                    <x-ui.kpi title="Último pesaje" icon="timer" variantExpr="ultimoVariant(kpisDia.ultimo_hace_min)">
                        <span :class="ultimoClass(kpisDia.ultimo_hace_min)" x-text="ultimoLabel(kpisDia.ultimo_hace_min)"></span>
                        <p class="text-xs font-normal mt-0.5 text-muted-foreground"
                           x-text="kpisDia.ultimo_hace_min === null ? 'Sin actividad hoy' :
                                   kpisDia.ultimo_hace_min < 180 ? 'Operación activa' :
                                   kpisDia.ultimo_hace_min < 480 ? 'Sin pesajes recientes' :
                                   'Sin actividad reciente'"></p>
                    </x-ui.kpi>
                    <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary">
                        <span x-text="kpisDia.kg_por_ha !== null ? fmt(kpisDia.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
                        <span x-show="kpisDia.kg_por_ha !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisDia.kg_por_ha / 1000, 2) + ' t/ha'"></span>
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
                    <x-ui.kpi title="kg / persona" icon="users" variant="primary">
                        <span x-text="kpisDia.kg_por_persona !== null ? fmt(kpisDia.kg_por_persona, 2) + ' kg' : '—'"></span>
                        <span x-show="kpisDia.kg_por_persona !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisDia.kg_por_persona / 1000, 3) + ' t'"></span>
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
            </div>

            {{-- Este mes --}}
            <div x-show="active === 'mes'" class="space-y-4">
                <p class="text-label text-base">KPIs del mes</p>
                <div class="grid grid-cols-1 gap-3">
                    <x-ui.kpi title="Pesajes del mes" icon="calendar-check" variant="primary">
                        <span x-text="fmt(kpisMes.total)"></span>
                        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
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
                    <x-ui.kpi title="Toneladas del mes" icon="package" variant="success">
                        <span x-text="fmt(kpisMes.toneladas, 1) + ' t'"></span>
                        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
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
                    <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary">
                        <span x-text="kpisMes.dias_op"></span>
                        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
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
                    <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary">
                        <span x-text="kpisMes.kg_por_ha !== null ? fmt(kpisMes.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
                        <span x-show="kpisMes.kg_por_ha !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisMes.kg_por_ha / 1000, 2) + ' t/ha'"></span>
                        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
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
                    <x-ui.kpi title="kg / persona" icon="users" variant="primary">
                        <span x-text="kpisMes.kg_por_persona !== null ? fmt(kpisMes.kg_por_persona, 2) + ' kg' : '—'"></span>
                        <span x-show="kpisMes.kg_por_persona !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisMes.kg_por_persona / 1000, 3) + ' t'"></span>
                        <x-ui.popover side="top" align="start" width="w-52" class="mt-1.5 block">
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

            {{-- Período personalizado --}}
            <template x-if="active === 'personalizado' && kpisRango">
                <div class="space-y-4">
                    <p class="text-label text-base" x-text="'KPIs · ' + rangoLabel()"></p>
                    <div class="grid grid-cols-1 gap-3">
                        <x-ui.kpi title="Pesajes" icon="scale" variant="primary">
                            <span x-text="fmt(kpisRango.total)"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground">en el período</p>
                        </x-ui.kpi>
                        <x-ui.kpi title="Toneladas netas" icon="weight" variant="success">
                            <span x-text="fmt(kpisRango.toneladas, 1) + ' t'"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground">en el período</p>
                        </x-ui.kpi>
                        <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary">
                            <span x-text="kpisRango.dias_op"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground" x-text="'de ' + kpisRango.dias_rango + ' días'"></p>
                        </x-ui.kpi>
                        <x-ui.kpi title="Promedio / día" icon="trending-up" variant="success">
                            <span x-text="fmt(kpisRango.promedio_dia, 2) + ' t'"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground">por día operativo</p>
                        </x-ui.kpi>
                        <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary">
                            <span x-text="kpisRango.kg_por_ha !== null ? fmt(kpisRango.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
                            <span x-show="kpisRango.kg_por_ha !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisRango.kg_por_ha / 1000, 2) + ' t/ha'"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground">por hectárea</p>
                        </x-ui.kpi>
                        <x-ui.kpi title="kg / persona" icon="users" variant="primary">
                            <span x-text="kpisRango.kg_por_persona !== null ? fmt(kpisRango.kg_por_persona, 2) + ' kg' : '—'"></span>
                            <span x-show="kpisRango.kg_por_persona !== null" class="text-base font-normal text-muted-foreground ml-1.5" x-text="fmt(kpisRango.kg_por_persona / 1000, 3) + ' t'"></span>
                            <p class="text-xs font-normal mt-0.5 text-muted-foreground">por habitante</p>
                        </x-ui.kpi>
                    </div>
                </div>
            </template>

        </div>
    </x-ui.sheet>
</div>
