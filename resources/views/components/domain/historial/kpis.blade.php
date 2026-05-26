@props(['kpis'])

<div class="hidden sm:grid sm:grid-cols-4 gap-4">
    <x-ui.kpi title="Pesajes" icon="scale" help="Total de pesajes registrados en el turno actual.">
        {{ $kpis['total'] }}
    </x-ui.kpi>
    <x-ui.kpi title="Toneladas netas" icon="weight" help="Suma de pesos netos de todos los pesajes cerrados en el turno.">
        {{ number_format($kpis['toneladas_netas'], 1, ',', '.') }} t
    </x-ui.kpi>
    <x-ui.kpi title="Promedio neto" icon="chart-bar" help="Peso neto promedio por pesaje en el turno actual.">
        {{ number_format($kpis['promedio_kg'], 0, ',', '.') }} kg
    </x-ui.kpi>
    <x-ui.kpi title="En predio" icon="truck" help="Vehículos con entrada registrada que aún no tienen salida.">
        {{ $kpis['en_predio'] }}
    </x-ui.kpi>
</div>
