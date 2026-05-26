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
        <div>
            <x-ui.typography as="h2">Usuarios</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">Gestioná los usuarios del sistema. Solo los usuarios activos pueden iniciar sesión.</x-ui.typography>
        </div>
        <div class="hidden sm:flex items-center gap-2 shrink-0">
            <x-ui.button variant="secondary" @click="filterOpen = true" class="relative">
                <x-lucide-sliders-horizontal class="size-4" />
                Filtros
                @if($activeFilters > 0)
                    <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                        {{ $activeFilters }}
                    </span>
                @endif
            </x-ui.button>
            <x-ui.button @click="openCreate()">
                <x-lucide-plus class="size-4" />
                Agregar usuario
            </x-ui.button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2 sm:hidden">
        <x-ui.sheet side="bottom">
            <x-slot:trigger>
                <x-ui.button variant="{{ $hayFiltros ? 'default' : 'outline' }}" size="sm" class="w-full">
                    <x-lucide-sliders-horizontal class="size-3.5" />
                    Filtros
                    @if($hayFiltros)
                        <x-ui.badge variant="secondary" class="ml-0.5">•</x-ui.badge>
                    @endif
                </x-ui.button>
            </x-slot:trigger>
            <div class="p-6 pt-10 space-y-5 overflow-y-auto">
                <p class="text-label text-base">Filtros</p>
                <form method="GET" action="{{ route('admin.usuarios.index') }}" class="space-y-3">
                    <div class="space-y-1.5">
                        <x-ui.label>Nombre o correo</x-ui.label>
                        <x-ui.input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar usuario…" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label>Rol</x-ui.label>
                        <x-ui.select name="role" :value="$filters['role'] ?? ''">
                            <x-ui.select.trigger>
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                <x-ui.select.item value="operador">Operador</x-ui.select.item>
                                <x-ui.select.item value="admin">Admin</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label>Estado</x-ui.label>
                        <x-ui.select name="activo" :value="$filters['activo'] ?? ''">
                            <x-ui.select.trigger>
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                <x-ui.select.item value="1">Activo</x-ui.select.item>
                                <x-ui.select.item value="0">Inactivo</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </div>
                    <div class="flex gap-2 pt-1">
                        <x-ui.button type="submit" class="flex-1">
                            <x-lucide-search class="size-4" />
                            Aplicar
                        </x-ui.button>
                        @if($hayFiltros)
                            <x-ui.button variant="secondary" href="{{ route('admin.usuarios.index') }}" class="flex-1">
                                <x-lucide-x class="size-4" />
                                Limpiar
                            </x-ui.button>
                        @endif
                    </div>
                </form>
            </div>
        </x-ui.sheet>

        <x-ui.button size="sm" class="w-full" @click="openCreate()">
            <x-lucide-plus class="size-3.5" />
            Agregar usuario
        </x-ui.button>
    </div>

    @include('modules.admin.usuarios._tabla')
    @include('modules.admin.usuarios._drawer-filtros')
    @include('modules.admin.usuarios._modal')
    @include('modules.admin.usuarios._modal-confirm')
    @include('modules.admin.usuarios._modal-reset-password')

</div>
</x-layouts.app>
