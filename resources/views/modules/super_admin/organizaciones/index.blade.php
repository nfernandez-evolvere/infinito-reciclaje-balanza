@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'                          => (int) old('_editing_id', 0) ?: null,
            'nombre'                      => old('nombre', ''),
            'slug'                        => old('slug', ''),
            'admin_email'                 => old('admin_email', ''),
            'admin_password'              => '',
            'admin_password_confirmation' => '',
        ],
    ] : [];
@endphp

<x-layouts.app title="Organizaciones">
<div x-data="organizaciones({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.super_admin.organizaciones._header')
    @include('modules.super_admin.organizaciones._tabla')
    @include('modules.super_admin.organizaciones._modal')
    @include('modules.super_admin.organizaciones._modal-confirm')
    @include('modules.super_admin.organizaciones._modal-delete')

</div>
</x-layouts.app>
