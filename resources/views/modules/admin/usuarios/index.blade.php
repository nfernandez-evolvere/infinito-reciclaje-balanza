@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'    => (int) old('_editing_id', 0) ?: null,
            'name'  => old('name', ''),
            'email' => old('email', ''),
            'role'  => old('role', ''),
            'password' => '',
            'password_confirmation' => '',
        ],
    ] : [];
@endphp

<x-layouts.app title="Usuarios">
<div x-data="usuarios({{ Js::from($initial) }})" class="space-y-6">

    @include('modules.admin.usuarios._header')
    @include('modules.admin.usuarios._tabla')
    @include('modules.admin.usuarios._drawer-filtros')
    @include('modules.admin.usuarios._modal')
    @include('modules.admin.usuarios._modal-confirm')
    @include('modules.admin.usuarios._modal-reset-password')

</div>
</x-layouts.app>
