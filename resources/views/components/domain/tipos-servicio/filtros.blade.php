@props(['filters', 'tiposVehiculo', 'hayFiltros', 'activeFilters', 'storageKey' => null])

@php
    $route = route('admin.tipos-servicio.index');

    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['page' => null]));

    $chips = [];

    if (!empty($filters['nombre'])) {
        $chips[] = ['label' => $filters['nombre'], 'url' => $removeUrl(['nombre' => null])];
    }

    if (!empty($filters['tipo_vehiculo_id'])) {
        $tv = $tiposVehiculo->firstWhere('id', $filters['tipo_vehiculo_id']);
        $chips[] = ['label' => $tv?->nombre ?? 'Vehículo', 'url' => $removeUrl(['tipo_vehiculo_id' => null])];
    }

    if (($filters['activo'] ?? '') !== '') {
        $chips[] = [
            'label' => $filters['activo'] == '1' ? 'Activo' : 'Inactivo',
            'url'   => $removeUrl(['activo' => null]),
        ];
    }

    $storageKey = $storageKey ?? 'filtros:' . (request()->route()?->getName() ?? 'tipos-servicio');
@endphp

{{-- Sheet mobile (<md) --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$route">
    <x-domain.tipos-servicio.filtros.campos :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
</x-ui.filter-sheet>

{{-- Panel inline (md+) --}}
<x-ui.filter-panel :action="$route" :resetUrl="$route" :storageKey="$storageKey" :hasFilters="(bool) $hayFiltros">
    @if(count($chips))
        <x-slot:chips>
            @foreach($chips as $chip)
                <x-ui.filter-chip :href="$chip['url']" :label="$chip['label']" />
            @endforeach
        </x-slot:chips>
    @endif

    <x-domain.tipos-servicio.filtros.campos :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
</x-ui.filter-panel>
