@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'               => (int) old('_editing_id', 0) ?: null,
            'patente'          => old('patente', ''),
            'numero_interno'   => old('numero_interno', ''),
            'tara_kg'          => old('tara_kg', ''),
            'tipo_vehiculo_id' => old('tipo_vehiculo_id', ''),
            'titular'          => old('titular', ''),
            'capacidad_kg'     => old('capacidad_kg', ''),
            'observaciones'    => old('observaciones', ''),
        ],
    ] : [];

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Vehículos">
<div x-data="vehiculos({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Vehículos</x-ui.typography>
        <x-ui.typography as="muted">Padrón de vehículos habilitados para registrar pesajes. La tara de cada vehículo se copia automáticamente al crear un pesaje.</x-ui.typography>
    </div>

    <x-domain.vehiculos.mobile-drawers
        :filters="$filters"
        :tiposVehiculo="$tiposVehiculo"
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.vehiculos.tabla :vehiculos="$vehiculos" :activeFilters="$activeFilters" />

    <x-domain.vehiculos.drawer-filtros :filters="$filters" :tiposVehiculo="$tiposVehiculo" />
    <x-domain.vehiculos.modal :tiposVehiculo="$tiposVehiculo" />
    <x-domain.vehiculos.modal-confirm />
    <x-domain.vehiculos.modal-delete />

</div>
</x-layouts.app>
