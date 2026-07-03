@props(['zonas', 'tiposServicio', 'tiposVehiculo', 'filters' => []])

@php
    $route    = route('admin.reportes.index');
    $resetUrl = route('admin.reportes.index', ['tab' => 'generar']);

    // Quita un filtro preservando el resto del query string (incluido tab=generar).
    $removeUrl = fn (array $overrides) => request()->fullUrlWithQuery($overrides);

    $hayFiltros = ($filters['desde'] ?? null)
        || ($filters['hasta'] ?? null)
        || ($filters['zona_id'] ?? null)
        || ($filters['tipo_servicio_id'] ?? null)
        || ($filters['tipo_vehiculo_id'] ?? null);

    $chips = [];

    if (!empty($filters['desde']) || !empty($filters['hasta'])) {
        $desde = $filters['desde'] ?? null;
        $hasta = $filters['hasta'] ?? null;
        $label = match(true) {
            $desde && $hasta && $desde === $hasta => \Carbon\Carbon::parse($desde)->format('d/m/Y'),
            $desde && $hasta                      => \Carbon\Carbon::parse($desde)->format('d/m') . ' – ' . \Carbon\Carbon::parse($hasta)->format('d/m'),
            (bool) $desde                         => 'Desde ' . \Carbon\Carbon::parse($desde)->format('d/m'),
            default                               => 'Hasta ' . \Carbon\Carbon::parse($hasta)->format('d/m'),
        };
        $chips[] = ['label' => $label, 'url' => $removeUrl(['desde' => null, 'hasta' => null])];
    }

    if (!empty($filters['zona_id'])) {
        $zona = $zonas->firstWhere('id', $filters['zona_id']);
        $chips[] = ['label' => $zona?->nombre ?? 'Zona', 'url' => $removeUrl(['zona_id' => null])];
    }

    if (!empty($filters['tipo_servicio_id'])) {
        $ts = $tiposServicio->firstWhere('id', $filters['tipo_servicio_id']);
        $chips[] = ['label' => $ts?->nombre ?? 'Servicio', 'url' => $removeUrl(['tipo_servicio_id' => null])];
    }

    if (!empty($filters['tipo_vehiculo_id'])) {
        $tv = $tiposVehiculo->firstWhere('id', $filters['tipo_vehiculo_id']);
        $chips[] = ['label' => $tv?->nombre ?? 'Vehículo', 'url' => $removeUrl(['tipo_vehiculo_id' => null])];
    }
@endphp

{{-- Sheet mobile (<md): controlado por filterOpen del padre --}}
<x-ui.filter-sheet controlledBy="filterOpen" :action="$route" :resetUrl="$resetUrl">
    <x-domain.reportes.filtros.campos
        :zonas="$zonas"
        :tiposServicio="$tiposServicio"
        :tiposVehiculo="$tiposVehiculo"
        :filters="$filters"
    />
</x-ui.filter-sheet>

{{-- Panel inline (md+) --}}
<x-ui.filter-panel
    :action="$route"
    :resetUrl="$resetUrl"
    storageKey="filtros:reportes-generar"
    :hasFilters="(bool) $hayFiltros"
    title="Período"
    submitLabel="Generar"
    submittingLabel="Generando…"
    emptyLabel="Sin período seleccionado"
>
    @if(count($chips))
        <x-slot:chips>
            @foreach($chips as $chip)
                <x-ui.filter-chip :href="$chip['url']" :label="$chip['label']" />
            @endforeach
        </x-slot:chips>
    @endif

    <x-domain.reportes.filtros.campos
        :zonas="$zonas"
        :tiposServicio="$tiposServicio"
        :tiposVehiculo="$tiposVehiculo"
        :filters="$filters"
    />
</x-ui.filter-panel>
