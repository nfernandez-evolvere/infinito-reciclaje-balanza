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
<div x-data="organizaciones({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Organizaciones</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">Administrá las organizaciones que operan en el sistema.</x-ui.typography>
        </div>
        <div class="hidden sm:flex items-center gap-2 shrink-0">
            <x-ui.button @click="openCreate()">
                <x-lucide-plus class="size-4" />
                Agregar organización
            </x-ui.button>
        </div>
    </div>

    <div class="sm:hidden">
        <x-ui.button size="sm" class="w-full" @click="openCreate()">
            <x-lucide-plus class="size-3.5" />
            Agregar organización
        </x-ui.button>
    </div>

    @include('modules.super_admin.organizaciones._tabla')
    @include('modules.super_admin.organizaciones._modal')
    @include('modules.super_admin.organizaciones._modal-confirm')
    @include('modules.super_admin.organizaciones._modal-delete')

</div>
</x-layouts.app>
