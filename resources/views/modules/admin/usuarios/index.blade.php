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

    <div class="flex items-start justify-between gap-4">
        <div class="flex flex-col items-start gap-2">
            <x-ui.typography as="h2">Usuarios</x-ui.typography>
            <x-ui.typography as="muted">Operadores y administradores con acceso al sistema.</x-ui.typography>
        </div>

        <x-ui.button @click="openCreate()" class="shrink-0">
            <x-lucide-plus class="size-4" />
            <span class="hidden sm:inline">Agregar usuario</span>
            <span class="sm:hidden">Agregar</span>
        </x-ui.button>
    </div>

    <x-domain.usuarios.acciones
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.usuarios.filtros
        :filters="$filters"
        :hayFiltros="$hayFiltros"
        :activeFilters="$activeFilters"
    />

    <x-domain.usuarios.tabla :usuarios="$usuarios" :activeFilters="$activeFilters" />

    <x-domain.usuarios.modal />
    <x-domain.usuarios.modal-confirm />
    <x-domain.usuarios.modal-reset-password />

</div>
</x-layouts.app>
