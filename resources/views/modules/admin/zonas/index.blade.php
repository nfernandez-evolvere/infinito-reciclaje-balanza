@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $zonasGuia = $zonas
        ->filter(fn($z) => $z->geojson !== null && $z->geojson !== '')
        ->map(fn($z) => ['id' => $z->id, 'nombre' => $z->nombre, 'geojson' => $z->geojson])
        ->values();

    $initial = array_merge(
        $hasErrors ? [
            'modalOpen' => true,
            'modalMode' => $isEditing ? 'edit' : 'create',
            'form'      => [
                'id'         => (int) old('_editing_id', 0) ?: null,
                'nombre'     => old('nombre', ''),
                'hectareas'  => old('hectareas', ''),
                'barrios'    => old('barrios', ''),
                'habitantes' => old('habitantes', ''),
                'geojson'    => old('geojson', ''),
                'centro_lat' => old('centro_lat', ''),
                'centro_lng' => old('centro_lng', ''),
            ],
        ] : [],
        ['zonasGuia' => $zonasGuia],
    );

    $totalHa       = $zonas->sum('hectareas');
    $haFormateadas = $totalHa > 0 ? number_format($totalHa, 2, ',', '.') . ' ha en total' : null;
    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Zonas operativas">
<div x-data="zonas({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex flex-col items-start justify-between gap-2">
        <x-ui.typography as="h2">Zonas operativas</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1">
            Zonas geográficas de recolección{{ $haFormateadas ? ' · ' . $haFormateadas : '' }}.
        </x-ui.typography>
    </div>

    <x-domain.zonas.mobile-drawers :filters="$filters" :hayFiltros="$hayFiltros" />

    <x-domain.zonas.cards :zonas="$zonas" :tiposServicio="$tiposServicio" />

    <x-domain.zonas.drawer-filtros :filters="$filters" />
    <x-domain.zonas.modal />
    <x-domain.zonas.modal-confirm />
    <x-domain.zonas.modal-delete />
    <x-domain.zonas.modal-servicio :tiposServicio="$tiposServicio" />
    <x-domain.zonas.modal-quitar-servicio />

</div>
</x-layouts.app>
