@props(['kpis', 'gridClass' => 'grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-4'])

<div class="{{ $gridClass }}">

    <x-ui.kpi title="Pesajes" icon="scale" variant="primary"
              help="Total de viajes registrados en el período seleccionado.">
        {{ number_format($kpis['total']) }}
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas netas" icon="weight" variant="success"
              help="Suma de pesos netos de todos los pesajes del período.">
        {{ number_format($kpis['toneladas'], 1) }} t
    </x-ui.kpi>

    <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary"
              help="Días del período con al menos un pesaje registrado.">
        {{ $kpis['dias_op'] }}
        <span class="text-base font-normal text-muted-foreground ml-1">de {{ $kpis['dias_rango'] }}d</span>
    </x-ui.kpi>

    <x-ui.kpi title="Promedio / día" icon="trending-up" variant="success"
              help="Toneladas netas promedio por día operativo.">
        {{ number_format($kpis['promedio_ton_dia'], 2) }} t
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por día op.</p>
    </x-ui.kpi>

    <x-ui.kpi title="kg / viaje" icon="gauge" variant="primary"
              help="Kilogramos netos promedio por viaje.">
        {{ number_format($kpis['promedio_kg_viaje']) }} kg
    </x-ui.kpi>

</div>
