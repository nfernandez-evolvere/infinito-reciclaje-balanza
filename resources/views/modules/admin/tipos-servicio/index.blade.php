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
@endphp

<x-layouts.app title="Tipos de servicio">
<div x-data="tiposServicio({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.tipos-servicio._header')
    @include('modules.admin.tipos-servicio._tabla')
    @include('modules.admin.tipos-servicio._drawer-filtros')
    @include('modules.admin.tipos-servicio._modal')
    @include('modules.admin.tipos-servicio._modal-confirm')
    @include('modules.admin.tipos-servicio._modal-delete')

</div>
</x-layouts.app>
