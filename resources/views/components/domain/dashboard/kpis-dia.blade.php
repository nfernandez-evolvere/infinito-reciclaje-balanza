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

    <x-ui.kpi title="Último pesaje" icon="timer" help="Minutos transcurridos desde el último pesaje registrado hoy.">
        @if($kpis['ultimo_hace_min'] === null)
            <span class="text-muted-foreground">—</span>
            <p class="text-xs font-normal mt-0.5 text-muted-foreground">Sin actividad hoy</p>
        @elseif($kpis['ultimo_hace_min'] < 15)
            <span class="text-success">{{ $kpis['ultimo_hace_min'] }} min</span>
            <p class="text-xs font-normal mt-0.5 text-success/80">Operación activa</p>
        @elseif($kpis['ultimo_hace_min'] < 60)
            <span class="text-warning">{{ $kpis['ultimo_hace_min'] }} min</span>
            <p class="text-xs font-normal mt-0.5 text-muted-foreground">Sin pesajes recientes</p>
        @else
            @php $h = intdiv($kpis['ultimo_hace_min'], 60); $m = $kpis['ultimo_hace_min'] % 60; @endphp
            <span class="text-destructive">{{ $h }}h{{ $m > 0 ? ' ' . $m . 'min' : '' }}</span>
            <p class="text-xs font-normal mt-0.5 text-destructive/80">Sin actividad reciente</p>
        @endif
    </x-ui.kpi>

</div>
