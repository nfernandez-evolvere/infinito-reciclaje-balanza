@props(['kpis'])

<div class="hidden sm:grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

    <x-ui.kpi title="Pesajes hoy" icon="scale" help="Total de pesajes registrados en el día.">
        {{ number_format($kpis['total']) }}
        @if($kpis['delta'] !== null)
            <p class="text-xs font-normal mt-0.5 {{ $kpis['delta'] >= 0 ? 'text-success' : 'text-destructive' }}">
                {{ $kpis['delta'] >= 0 ? '+' : '' }}{{ $kpis['delta'] }}% vs mismo día mes anterior
            </p>
        @else
            <p class="text-xs font-normal mt-0.5 text-muted-foreground">Sin comparación disponible</p>
        @endif
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas netas" icon="weight" help="Suma de pesos netos de todos los pesajes del día.">
        {{ number_format($kpis['toneladas'], 1, ',', '.') }} t
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">toneladas acumuladas hoy</p>
    </x-ui.kpi>

    <x-ui.kpi title="Promedio / viaje" icon="trending-up" help="Toneladas netas promedio por pesaje registrado hoy.">
        {{ number_format($kpis['promedio'], 2, ',', '.') }} t
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por pesaje</p>
    </x-ui.kpi>

    <x-ui.kpi title="Horas operativas" icon="clock" help="Tiempo transcurrido desde el primer pesaje del día.">
        {{ number_format($kpis['horas_op'], 1, ',', '.') }} h
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">desde el primer pesaje</p>
    </x-ui.kpi>

</div>
