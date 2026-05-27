{{-- Mobile: drawer --}}
<div class="xl:hidden">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button variant="outline" size="sm" class="w-full">
                <x-lucide-bar-chart-2 class="size-3.5" />
                KPIs del día &middot;
                <span x-text="fmt(kpisDia.total)"></span> pesajes &middot;
                <span x-text="fmt(kpisDia.toneladas, 1)"></span> t
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-4">
            <p class="text-label text-base">KPIs del día</p>
            <div class="grid grid-cols-1 gap-3">
                <x-ui.kpi title="Pesajes hoy" icon="scale">
                    <span x-text="fmt(kpisDia.total)"></span>
                    <p :class="deltaClass(kpisDia.delta)" x-text="deltaText(kpisDia.delta, 'vs mes ant.')"></p>
                </x-ui.kpi>
                <x-ui.kpi title="Toneladas netas" icon="weight">
                    <span x-text="fmt(kpisDia.toneladas, 1) + ' t'"></span>
                    <p class="text-xs font-normal mt-0.5 text-muted-foreground">acumuladas hoy</p>
                </x-ui.kpi>
                <x-ui.kpi title="Promedio / viaje" icon="trending-up">
                    <span x-text="fmt(kpisDia.promedio, 2) + ' t'"></span>
                    <p class="text-xs font-normal mt-0.5 text-muted-foreground">por pesaje</p>
                </x-ui.kpi>
                <x-ui.kpi title="Último pesaje" icon="timer">
                    <span :class="ultimoClass(kpisDia.ultimo_hace_min)" x-text="ultimoLabel(kpisDia.ultimo_hace_min)"></span>
                    <p class="text-xs font-normal mt-0.5 text-muted-foreground"
                       x-text="kpisDia.ultimo_hace_min === null ? 'Sin actividad hoy' :
                               kpisDia.ultimo_hace_min < 15  ? 'Operación activa' :
                               kpisDia.ultimo_hace_min < 60  ? 'Sin pesajes recientes' :
                               'Sin actividad reciente'"></p>
                </x-ui.kpi>
            </div>
        </div>
    </x-ui.sheet>
</div>

{{-- Desktop: grid --}}
<div class="hidden xl:grid grid-cols-4 gap-4">

    <x-ui.kpi title="Pesajes hoy" icon="scale" help="Total de pesajes registrados en el día.">
        <span x-text="fmt(kpisDia.total)"></span>
        <p :class="deltaClass(kpisDia.delta)" x-text="deltaText(kpisDia.delta, 'vs mismo día mes anterior')"></p>
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas netas" icon="weight" help="Suma de pesos netos de todos los pesajes del día.">
        <span x-text="fmt(kpisDia.toneladas, 1) + ' t'"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">toneladas acumuladas hoy</p>
    </x-ui.kpi>

    <x-ui.kpi title="Promedio / viaje" icon="trending-up" help="Toneladas netas promedio por pesaje registrado hoy.">
        <span x-text="fmt(kpisDia.promedio, 2) + ' t'"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por pesaje</p>
    </x-ui.kpi>

    <x-ui.kpi title="Último pesaje" icon="timer" help="Minutos transcurridos desde el último pesaje registrado hoy.">
        <span :class="ultimoClass(kpisDia.ultimo_hace_min)" x-text="ultimoLabel(kpisDia.ultimo_hace_min)"></span>
        <p class="text-xs font-normal mt-0.5"
           :class="ultimoClass(kpisDia.ultimo_hace_min) === 'text-success' ? 'text-success/80' :
                   ultimoClass(kpisDia.ultimo_hace_min) === 'text-warning' ? 'text-muted-foreground' :
                   ultimoClass(kpisDia.ultimo_hace_min) === 'text-destructive' ? 'text-destructive/80' :
                   'text-muted-foreground'"
           x-text="kpisDia.ultimo_hace_min === null ? 'Sin actividad hoy' :
                   kpisDia.ultimo_hace_min < 15 ? 'Operación activa' :
                   kpisDia.ultimo_hace_min < 60 ? 'Sin pesajes recientes' :
                   'Sin actividad reciente'">
        </p>
    </x-ui.kpi>

</div>
