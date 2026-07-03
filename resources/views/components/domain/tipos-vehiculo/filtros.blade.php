@props(['filters', 'hayFiltros', 'activeFilters', 'storageKey' => null])

@php
    $route    = route('admin.vehiculos.index');
    $resetUrl = route('admin.vehiculos.index', ['tab' => 'tipos']);

    // Quita un filtro preservando el resto del query string (incluido el tab activo).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['page' => null]));

    $chips = [];

    if (!empty($filters['nombre'])) {
        $chips[] = ['label' => $filters['nombre'], 'url' => $removeUrl(['nombre' => null])];
    }

    if (($filters['peso_min'] ?? '') !== '') {
        $chips[] = [
            'label' => '≥ ' . number_format((int) $filters['peso_min'], 0, ',', '.') . ' kg',
            'url'   => $removeUrl(['peso_min' => null]),
        ];
    }

    if (($filters['peso_max'] ?? '') !== '') {
        $chips[] = [
            'label' => '≤ ' . number_format((int) $filters['peso_max'], 0, ',', '.') . ' kg',
            'url'   => $removeUrl(['peso_max' => null]),
        ];
    }

    if (($filters['activo'] ?? '') !== '') {
        $chips[] = [
            'label' => $filters['activo'] == '1' ? 'Activo' : 'Inactivo',
            'url'   => $removeUrl(['activo' => null]),
        ];
    }

    $storageKey = $storageKey ?? 'filtros:tipos-vehiculo';
@endphp

{{-- Sheet mobile (<md) --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$resetUrl">
    <x-domain.tipos-vehiculo.filtros.campos :filters="$filters" tab="tipos" />
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

    <x-domain.tipos-vehiculo.filtros.campos :filters="$filters" tab="tipos" />
</x-ui.filter-panel>
