@props(['filtros' => [], 'hayFiltros' => false, 'activeFilters' => 0, 'storageKey' => null])

@php
    $route    = route('admin.alertas.index');
    $resetUrl = route('admin.alertas.index', ['tab' => 'alertas']);

    // Quita un filtro preservando el resto del query string (incluido tab=alertas).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge($overrides, ['page' => null]));

    $tipoLabels = [
        'peso_fuera_rango'        => 'Peso fuera de rango',
        'volumen_diario_atipico'  => 'Volumen atípico',
        'gap_registro'            => 'Sin actividad',
        'frecuencia_zona_atipica' => 'Frecuencia atípica',
    ];

    $chips = [];

    if (!empty($filtros['tipo'])) {
        $chips[] = ['label' => $tipoLabels[$filtros['tipo']] ?? $filtros['tipo'], 'url' => $removeUrl(['tipo' => null])];
    }

    if (($filtros['leida'] ?? '') !== '') {
        $chips[] = [
            'label' => $filtros['leida'] == '1' ? 'Leídas' : 'Sin leer',
            'url'   => $removeUrl(['leida' => null]),
        ];
    }

    if (!empty($filtros['desde']) || !empty($filtros['hasta'])) {
        $desde = $filtros['desde'] ?? null;
        $hasta = $filtros['hasta'] ?? null;
        $label = match(true) {
            $desde && $hasta && $desde === $hasta => \Carbon\Carbon::parse($desde)->format('d/m/Y'),
            $desde && $hasta                      => \Carbon\Carbon::parse($desde)->format('d/m') . ' – ' . \Carbon\Carbon::parse($hasta)->format('d/m'),
            (bool) $desde                         => 'Desde ' . \Carbon\Carbon::parse($desde)->format('d/m'),
            default                               => 'Hasta ' . \Carbon\Carbon::parse($hasta)->format('d/m'),
        };
        $chips[] = ['label' => $label, 'url' => $removeUrl(['desde' => null, 'hasta' => null])];
    }

    $storageKey = $storageKey ?? 'filtros:alertas';
@endphp

{{-- Sheet mobile (<md) --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$resetUrl">
    <x-domain.alertas.filtros.campos :filtros="$filtros" />
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

    <x-domain.alertas.filtros.campos :filtros="$filtros" />
</x-ui.filter-panel>
