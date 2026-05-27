@props(['kpis'])

<div class="hidden sm:grid sm:grid-cols-3 gap-4">

    <x-ui.kpi title="Pesajes del mes" icon="calendar-check" help="Total de pesajes registrados desde el 1° del mes.">
        {{ number_format($kpis['total']) }}
        @if($kpis['delta'] !== null)
            <p class="text-xs font-normal mt-0.5 {{ $kpis['delta'] >= 0 ? 'text-success' : 'text-destructive' }}">
                {{ $kpis['delta'] >= 0 ? '+' : '' }}{{ $kpis['delta'] }}% vs mismo período mes anterior
            </p>
        @else
            <p class="text-xs font-normal mt-0.5 text-muted-foreground">Sin comparación disponible</p>
        @endif
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas del mes" icon="package" help="Suma de toneladas netas acumuladas en el mes.">
        {{ number_format($kpis['toneladas'], 1, ',', '.') }} t
        @if($kpis['delta_toneladas'] !== null)
            <p class="text-xs font-normal mt-0.5 {{ $kpis['delta_toneladas'] >= 0 ? 'text-success' : 'text-destructive' }}">
                {{ $kpis['delta_toneladas'] >= 0 ? '+' : '' }}{{ $kpis['delta_toneladas'] }}% vs mismo período mes anterior
            </p>
        @else
            <p class="text-xs font-normal mt-0.5 text-muted-foreground">Sin comparación disponible</p>
        @endif
    </x-ui.kpi>

    <x-ui.kpi title="Días operativos" icon="calendar-days" help="Cantidad de días con al menos un pesaje registrado.">
        {{ $kpis['dias_op'] }}
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">de {{ now()->day }} días transcurridos</p>
    </x-ui.kpi>

</div>
