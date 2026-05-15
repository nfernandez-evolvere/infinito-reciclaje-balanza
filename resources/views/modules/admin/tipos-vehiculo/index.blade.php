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
@endphp

<x-layouts.app title="Tipos de vehículo">
<div x-data="tiposVehiculo({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.tipos-vehiculo._header')
    @include('modules.admin.tipos-vehiculo._tabla')
    @include('modules.admin.tipos-vehiculo._drawer-filtros')
    @include('modules.admin.tipos-vehiculo._modal')
    @include('modules.admin.tipos-vehiculo._modal-confirm')

</div>
</x-layouts.app>
