@props(['filtros', 'operarios', 'hayFiltros', 'routeHistorial', 'zonas' => collect(), 'tiposServicio' => collect(), 'tiposVehiculo' => collect(), 'sortDirection' => 'desc'])

@php
    $merge = fn(array $overrides) => $routeHistorial . '?' . http_build_query(
        array_filter(array_merge($filtros, $overrides), fn($v) => $v !== null && $v !== '')
    );

    $chips = [];

    if ($filtros['desde'] || $filtros['hasta']) {
        $label = match(true) {
            (bool) $filtros['desde'] && (bool) $filtros['hasta'] && $filtros['desde'] === $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m/Y'),
            (bool) $filtros['desde'] && (bool) $filtros['hasta']
                => \Carbon\Carbon::parse($filtros['desde'])->format('d/m') . ' – ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
            (bool) $filtros['desde']
                => 'Desde ' . \Carbon\Carbon::parse($filtros['desde'])->format('d/m'),
            default
                => 'Hasta ' . \Carbon\Carbon::parse($filtros['hasta'])->format('d/m'),
        };
        $chips[] = [
            'label' => $label,
            'url'   => $merge(['desde' => null, 'hasta' => null]),
        ];
    }

    if (!empty($filtros['patente'])) {
        $chips[] = ['label' => strtoupper($filtros['patente']), 'url' => $merge(['patente' => null])];
    }

    if (!empty($filtros['estado'])) {
        $chips[] = ['label' => $filtros['estado'], 'url' => $merge(['estado' => null])];
    }

    if (!empty($filtros['operario_id'])) {
        $op = $operarios->firstWhere('id', $filtros['operario_id']);
        $chips[] = ['label' => $op?->name ?? 'Operario', 'url' => $merge(['operario_id' => null])];
    }

    if (!empty($filtros['zona_id'])) {
        $zona = $zonas->firstWhere('id', $filtros['zona_id']);
        $chips[] = ['label' => $zona?->nombre ?? 'Zona', 'url' => $merge(['zona_id' => null])];
    }

    if (!empty($filtros['tipo_servicio_id'])) {
        $ts = $tiposServicio->firstWhere('id', $filtros['tipo_servicio_id']);
        $chips[] = ['label' => $ts?->nombre ?? 'Servicio', 'url' => $merge(['tipo_servicio_id' => null])];
    }

    if (!empty($filtros['tipo_vehiculo_id'])) {
        $tv = $tiposVehiculo->firstWhere('id', $filtros['tipo_vehiculo_id']);
        $chips[] = ['label' => $tv?->nombre ?? 'Tipo de vehículo', 'url' => $merge(['tipo_vehiculo_id' => null])];
    }

    if (!empty($filtros['solo_alerta'])) {
        $chips[] = ['label' => 'Con alerta', 'url' => $merge(['solo_alerta' => null])];
    }

    if (!empty($filtros['solo_editados'])) {
        $chips[] = ['label' => 'Solo editados', 'url' => $merge(['solo_editados' => null])];
    }

    $storageKey = 'filtros:' . (request()->route()?->getName() ?? 'historial');
@endphp

{{-- ── Mobile (<md): botón que abre el sheet ─────────────────────── --}}
<div class="md:hidden">
    <div class="relative inline-flex">
        <x-ui.button variant="outline" class="hover:bg-accent hover:text-accent-foreground" @click="filterOpen = true">
            <x-lucide-sliders-horizontal class="size-4" />
            Filtros
        </x-ui.button>
        @if($hayFiltros)
            <span class="pointer-events-none absolute -top-1.5 -right-1.5 flex size-4 items-center justify-center rounded-full bg-primary text-primary-foreground ring-2 ring-background text-[10px] font-semibold leading-none">
                {{ count($chips) }}
            </span>
        @endif
    </div>
</div>

<x-ui.filter-sheet controlledBy="filterOpen" :action="$routeHistorial" :resetUrl="$routeHistorial">
    <x-domain.historial.filtros.campos :filtros="$filtros" :operarios="$operarios" :zonas="$zonas" :tiposServicio="$tiposServicio" :tiposVehiculo="$tiposVehiculo" :sortDirection="$sortDirection" />
</x-ui.filter-sheet>

{{-- ── Tablet / Desktop (md+): toggle + card de filtros ─────────────── --}}
<x-ui.filter-panel :action="$routeHistorial" :resetUrl="$routeHistorial" :storageKey="$storageKey" :hasFilters="(bool) $hayFiltros"
    bodyClass="grid grid-cols-2 gap-x-4 gap-y-3 p-4 lg:grid-cols-5 lg:items-end">
    @if(count($chips))
        <x-slot:chips>
            @foreach($chips as $chip)
                <x-ui.filter-chip :href="$chip['url']" :label="$chip['label']" />
            @endforeach
        </x-slot:chips>
    @endif

    <x-domain.historial.filtros.campos :filtros="$filtros" :operarios="$operarios" :zonas="$zonas" :tiposServicio="$tiposServicio" :tiposVehiculo="$tiposVehiculo" :sortDirection="$sortDirection" />
</x-ui.filter-panel>
