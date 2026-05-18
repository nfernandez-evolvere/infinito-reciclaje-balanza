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
@endphp

<x-layouts.app title="Vehículos">
<div x-data="vehiculos({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.vehiculos._header')
    @include('modules.admin.vehiculos._tabla')
    @include('modules.admin.vehiculos._drawer-filtros')
    @include('modules.admin.vehiculos._modal')
    @include('modules.admin.vehiculos._modal-confirm')
    @include('modules.admin.vehiculos._modal-delete')

</div>
</x-layouts.app>
