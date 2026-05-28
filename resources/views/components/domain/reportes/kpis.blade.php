@props(['kpis'])

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">

    <x-ui.card variant="elevated" class="col-span-2 lg:col-span-1 xl:col-span-1">
        <x-ui.card.content class="pt-6">
            <p class="text-overline">Viajes</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($kpis['total']) }}</p>
            <p class="text-caption mt-1">pesajes registrados</p>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card variant="elevated" class="col-span-2 lg:col-span-1 xl:col-span-1">
        <x-ui.card.content class="pt-6">
            <p class="text-overline">Toneladas</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($kpis['toneladas'], 1) }}</p>
            <p class="text-caption mt-1">toneladas netas</p>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card variant="elevated" class="col-span-1">
        <x-ui.card.content class="pt-6">
            <p class="text-overline">Días op.</p>
            <p class="text-3xl font-bold mt-1">{{ $kpis['dias_op'] }}</p>
            <p class="text-caption mt-1">de {{ $kpis['dias_rango'] }} días</p>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card variant="elevated" class="col-span-1">
        <x-ui.card.content class="pt-6">
            <p class="text-overline">Prom. ton/día</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($kpis['promedio_ton_dia'], 1) }}</p>
            <p class="text-caption mt-1">en días operativos</p>
        </x-ui.card.content>
    </x-ui.card>

    <x-ui.card variant="elevated" class="col-span-1">
        <x-ui.card.content class="pt-6">
            <p class="text-overline">kg/viaje</p>
            <p class="text-3xl font-bold mt-1">{{ number_format($kpis['promedio_kg_viaje']) }}</p>
            <p class="text-caption mt-1">promedio por viaje</p>
        </x-ui.card.content>
    </x-ui.card>

</div>
