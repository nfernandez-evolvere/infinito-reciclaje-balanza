@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = array_filter([
        'userSearchUrl' => route('super.usuarios.search'),
        'orgBaseUrl'    => url('/organizaciones'),
        'modalOpen'     => $hasErrors ?: null,
        'modalMode'     => $hasErrors ? ($isEditing ? 'edit' : 'create') : null,
        'userQuery'     => $hasErrors ? old('admin_email', '') : null,
        'form'          => $hasErrors ? [
            'id'          => (int) old('_editing_id', 0) ?: null,
            'nombre'      => old('nombre', ''),
            'admin_email' => old('admin_email', ''),
        ] : null,
    ], fn ($v) => $v !== null);
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
