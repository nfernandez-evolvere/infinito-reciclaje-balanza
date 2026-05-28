{{-- Desktop: grid --}}
<div class="hidden xl:grid grid-cols-4 gap-4">

    <x-ui.kpi title="Pesajes" icon="scale" variant="primary" help="Total de pesajes registrados en el período seleccionado.">
        <span x-text="fmt(kpisRango.total)"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">en el período</p>
    </x-ui.kpi>

    <x-ui.kpi title="Toneladas netas" icon="weight" variant="success" help="Suma de pesos netos de todos los pesajes del período.">
        <span x-text="fmt(kpisRango.toneladas, 1) + ' t'"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">en el período</p>
    </x-ui.kpi>

    <x-ui.kpi title="Días operativos" icon="calendar-days" variant="primary" help="Días del período con al menos un pesaje registrado.">
        <span x-text="kpisRango.dias_op"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground" x-text="'de ' + kpisRango.dias_rango + ' días'"></p>
    </x-ui.kpi>

    <x-ui.kpi title="Promedio / día" icon="trending-up" variant="success" help="Toneladas netas promedio por día operativo.">
        <span x-text="fmt(kpisRango.promedio_dia, 2) + ' t'"></span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por día operativo</p>
    </x-ui.kpi>

</div>
