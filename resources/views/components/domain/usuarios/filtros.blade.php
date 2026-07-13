@props(['filters', 'hayFiltros', 'activeFilters', 'storageKey' => null])

@php
    $route = route('admin.usuarios.index');

    // Quita un filtro preservando el resto del query string.
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['page' => null]));

    $chips = [];

    if (!empty($filters['buscar'])) {
        $chips[] = ['label' => $filters['buscar'], 'url' => $removeUrl(['buscar' => null])];
    }

    if (($filters['role'] ?? '') !== '') {
        $chips[] = [
            'label' => $filters['role'] === 'admin' ? 'Admin' : 'Operador',
            'url'   => $removeUrl(['role' => null]),
        ];
    }

    if (($filters['activo'] ?? '') !== '') {
        $chips[] = [
            'label' => $filters['activo'] == '1' ? 'Activo' : 'Inactivo',
            'url'   => $removeUrl(['activo' => null]),
        ];
    }

    $storageKey = $storageKey ?? 'filtros:' . (request()->route()?->getName() ?? 'usuarios');
@endphp

{{-- Sheet mobile (<md): controlado por filterOpen del padre --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$route">
    <x-domain.usuarios.filtros.campos :filters="$filters" />
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

    <x-domain.usuarios.filtros.campos :filters="$filters" />
</x-ui.filter-panel>
