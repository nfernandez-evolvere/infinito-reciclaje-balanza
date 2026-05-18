@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'         => (int) old('_editing_id', 0) ?: null,
            'nombre'     => old('nombre', ''),
            'hectareas'  => old('hectareas', ''),
            'barrios'    => old('barrios', ''),
            'habitantes' => old('habitantes', ''),
        ],
    ] : [];
@endphp

<x-layouts.app title="Zonas operativas">
<div x-data="zonas({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.zonas._header')
    @include('modules.admin.zonas._cards')
    @include('modules.admin.zonas._drawer-filtros')
    @include('modules.admin.zonas._modal')
    @include('modules.admin.zonas._modal-confirm')
    @include('modules.admin.zonas._modal-delete')
    @include('modules.admin.zonas._modal-servicio')
    @include('modules.admin.zonas._modal-quitar-servicio')

</div>
</x-layouts.app>
