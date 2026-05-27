<div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

    <x-ui.kpi title="Pesajes del mes" icon="calendar-check" help="Total de pesajes registrados desde el 1° del mes.">
        <span x-text="fmt(kpisMes.total)"></span>
        <p :class="deltaClass(kpisMes.delta)" x-text="deltaText(kpisMes.delta, 'vs mismo período mes anterior')"></p>
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas del mes" icon="package" help="Suma de toneladas netas acumuladas en el mes.">
        <span x-text="fmt(kpisMes.toneladas, 1) + ' t'"></span>
        <p :class="deltaClass(kpisMes.delta_toneladas)" x-text="deltaText(kpisMes.delta_toneladas, 'vs mismo período mes anterior')"></p>
    </x-ui.kpi>

    <x-ui.kpi title="Días operativos" icon="calendar-days" help="Cantidad de días con al menos un pesaje registrado.">
        <span x-text="kpisMes.dias_op"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">de {{ now()->day }} días transcurridos</p>
    </x-ui.kpi>

</div>
