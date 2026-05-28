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
                        <p :class="deltaClass(kpisDia.delta)" x-text="deltaText(kpisDia.delta, 'vs mes ant.')"></p>
                    </x-ui.kpi>
                    <x-ui.kpi title="Toneladas netas" icon="weight" variant="success">
                        <span x-text="fmt(kpisDia.toneladas, 1) + ' t'"></span>
                        <p class="text-xs font-normal mt-0.5 text-muted-foreground">acumuladas hoy</p>
                    </x-ui.kpi>
                    <x-ui.kpi title="Promedio / viaje" icon="trending-up" variant="success">
                        <span x-text="fmt(kpisDia.promedio, 2) + ' t'"></span>
                        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por pesaje</p>
                    </x-ui.kpi>
                    <x-ui.kpi title="Último pesaje" icon="timer" variantExpr="ultimoVariant(kpisDia.ultimo_hace_min)">
                        <span :class="ultimoClass(kpisDia.ultimo_hace_min)" x-text="ultimoLabel(kpisDia.ultimo_hace_min)"></span>
                        <p class="text-xs font-normal mt-0.5 text-muted-foreground"
                           x-text="kpisDia.ultimo_hace_min === null ? 'Sin actividad hoy' :
                                   kpisDia.ultimo_hace_min < 180 ? 'Operación activa' :
                                   kpisDia.ultimo_hace_min < 480 ? 'Sin pesajes recientes' :
                                   'Sin actividad reciente'"></p>
                    </x-ui.kpi>
                </div>
            </div>

            {{-- Este mes --}}
            <div x-show="active === 'mes'" class="space-y-4">
                <p class="text-label text-base">KPIs del mes</p>
                <div class="grid grid-cols-1 gap-3">
                    <x-ui.kpi title="Pesajes del mes" icon="calendar-check" variant="primary">
                        <span x-text="fmt(kpisMes.total)"></span>
                        <p :class="deltaClass(kpisMes.delta)" x-text="deltaText(kpisMes.delta, 'vs mes ant.')"></p>
                    </x-ui.kpi>
                    <x-ui.kpi title="Toneladas del mes" icon="package" variant="success">
                        <span x-text="fmt(kpisMes.toneladas, 1) + ' t'"></span>
                        <p :class="deltaClass(kpisMes.delta_toneladas)" x-text="deltaText(kpisMes.delta_toneladas, 'vs mes ant.')"></p>
                    </x-ui.kpi>
                    <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary">
                        <span x-text="kpisMes.dias_op"></span>
                        <p class="text-xs font-normal mt-0.5 text-muted-foreground" x-text="'de ' + kpisMes.dias_transcurridos + ' días'"></p>
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
                    </div>
                </div>
            </template>

        </div>
    </x-ui.sheet>
</div>
