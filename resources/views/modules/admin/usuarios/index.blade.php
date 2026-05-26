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

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Usuarios">
<div x-data="usuarios({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex flex-col items-start gap-2">
        <x-ui.typography as="h2">Usuarios</x-ui.typography>
        <x-ui.typography as="muted">Gestioná los usuarios del sistema. Solo los usuarios activos pueden iniciar sesión.</x-ui.typography>
    </div>

    <x-domain.usuarios.mobile-drawers
        :filters="$filters"
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.usuarios.tabla :usuarios="$usuarios" :activeFilters="$activeFilters" />

    <x-domain.usuarios.drawer-filtros :filters="$filters" />
    <x-domain.usuarios.modal />
    <x-domain.usuarios.modal-confirm />
    <x-domain.usuarios.modal-reset-password />

</div>
</x-layouts.app>
