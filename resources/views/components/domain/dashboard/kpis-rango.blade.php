{{-- Desktop: grid --}}
<div class="hidden xl:grid grid-cols-3 gap-4">

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

    <x-ui.kpi title="kg / hectárea" icon="land-plot" variant="primary" help="Kilogramos netos del período por hectárea de zona de servicio.">
        <span class="inline-flex items-baseline gap-1.5">
            <span x-text="kpisRango.kg_por_ha !== null ? fmt(kpisRango.kg_por_ha, 1) + ' kg/ha' : '—'"></span>
            <span x-show="kpisRango.kg_por_ha !== null" class="text-base font-normal text-muted-foreground" x-text="fmt(kpisRango.kg_por_ha / 1000, 2) + ' t/ha'"></span>
        </span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por hectárea</p>
    </x-ui.kpi>

    <x-ui.kpi title="kg / persona" icon="users" variant="primary" help="Kilogramos netos del período por habitante de la zona de servicio.">
        <span class="inline-flex items-baseline gap-1.5">
            <span x-text="kpisRango.kg_por_persona !== null ? fmt(kpisRango.kg_por_persona, 2) + ' kg' : '—'"></span>
            <span x-show="kpisRango.kg_por_persona !== null" class="text-base font-normal text-muted-foreground" x-text="fmt(kpisRango.kg_por_persona / 1000, 3) + ' t'"></span>
        </span>
        <p class="text-xs font-normal mt-0.5 text-muted-foreground">por habitante</p>
    </x-ui.kpi>

</div>
