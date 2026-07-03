@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'          => (int) old('_editing_id', 0) ?: null,
            'nombre'      => old('nombre', ''),
            'peso_min_kg' => old('peso_min_kg', ''),
            'peso_max_kg' => old('peso_max_kg', ''),
        ],
    ] : [];

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Tipos de vehículo">
<div x-data="tiposVehiculo({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Tipos de vehículo</x-ui.typography>
        <x-ui.typography as="muted">Rangos de peso bruto esperados por tipo (vehículo + carga). Se usan para detectar pesajes anómalos.</x-ui.typography>
    </div>

    <x-domain.tipos-vehiculo.acciones
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.tipos-vehiculo.filtros
        :filters="$filters"
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.tipos-vehiculo.tabla :tipos="$tipos" :activeFilters="$activeFilters" />

    <x-domain.tipos-vehiculo.modal />
    <x-domain.tipos-vehiculo.modal-confirm />
    <x-domain.tipos-vehiculo.modal-delete />

</div>
</x-layouts.app>
