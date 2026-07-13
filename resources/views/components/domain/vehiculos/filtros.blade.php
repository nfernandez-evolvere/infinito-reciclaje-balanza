@props(['filters', 'tiposVehiculo', 'hayFiltros', 'activeFilters', 'storageKey' => null])

@php
    $route    = route('admin.vehiculos.index');
    $resetUrl = route('admin.vehiculos.index', ['tab' => 'vehiculos']);

    // Quita un filtro preservando el resto del query string (incluido el tab activo).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['page' => null]));

    $chips = [];

    if (!empty($filters['patente'])) {
        $chips[] = ['label' => strtoupper($filters['patente']), 'url' => $removeUrl(['patente' => null])];
    }

    if (!empty($filters['numero_interno'])) {
        $chips[] = ['label' => 'Int. ' . $filters['numero_interno'], 'url' => $removeUrl(['numero_interno' => null])];
    }

    if (!empty($filters['tipo_vehiculo_id'])) {
        $tv = $tiposVehiculo->firstWhere('id', $filters['tipo_vehiculo_id']);
        $chips[] = ['label' => $tv?->nombre ?? 'Tipo', 'url' => $removeUrl(['tipo_vehiculo_id' => null])];
    }

    if (($filters['activo'] ?? '') !== '') {
        $chips[] = [
            'label' => $filters['activo'] == '1' ? 'Activo' : 'Inactivo',
            'url'   => $removeUrl(['activo' => null]),
        ];
    }

    $storageKey = $storageKey ?? 'filtros:vehiculos';
@endphp

{{-- Sheet mobile (<md) --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$resetUrl">
    <x-domain.vehiculos.filtros.campos :filters="$filters" :tiposVehiculo="$tiposVehiculo" tab="vehiculos" />
</x-ui.filter-sheet>

{{-- Panel inline (md+) --}}
<x-ui.filter-panel :action="$route" :resetUrl="$resetUrl" :storageKey="$storageKey" :hasFilters="(bool) $hayFiltros">
    @if(count($chips))
        <x-slot:chips>
            @foreach($chips as $chip)
                <x-ui.filter-chip :href="$chip['url']" :label="$chip['label']" />
            @endforeach
        </x-slot:chips>
    @endif

    <x-domain.vehiculos.filtros.campos :filters="$filters" :tiposVehiculo="$tiposVehiculo" tab="vehiculos" />
</x-ui.filter-panel>
