@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'                => (int) old('_editing_id', 0) ?: null,
            'nombre'            => old('nombre', ''),
            'tipo_vehiculo_ids' => array_map('intval', (array) old('tipo_vehiculo_ids', [])),
        ],
    ] : [];

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Tipos de servicio">
<div x-data="tiposServicio({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <x-domain.tipos-servicio.mobile-drawers
        :filters="$filters"
        :tiposVehiculo="$tiposVehiculo"
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.tipos-servicio.tabla :tipos="$tipos" :activeFilters="$activeFilters" />

    <x-domain.tipos-servicio.drawer-filtros :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
    <x-domain.tipos-servicio.modal :tiposVehiculo="$tiposVehiculo" />
    <x-domain.tipos-servicio.modal-confirm />
    <x-domain.tipos-servicio.modal-delete />

</div>
</x-layouts.app>
