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

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Tipos de servicio">
<div x-data="tiposServicio({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Tipos de servicio</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">Servicios de recolección habilitados. Se usan para clasificar pesajes y armar las zonas de operación.</x-ui.typography>
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
                Agregar tipo
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
                <form method="GET" action="{{ route('admin.tipos-servicio.index') }}" class="space-y-3">
                    <div class="space-y-1.5">
                        <x-ui.label>Nombre</x-ui.label>
                        <x-ui.input type="search" name="nombre" value="{{ $filters['nombre'] ?? '' }}" placeholder="Buscar por nombre…" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label>Vehículo habitual</x-ui.label>
                        <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
                            <x-ui.select.trigger>
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                @foreach($tiposVehiculo as $tv)
                                    <x-ui.select.item value="{{ $tv->id }}">{{ $tv->nombre }}</x-ui.select.item>
                                @endforeach
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
                            <x-ui.button variant="secondary" href="{{ route('admin.tipos-servicio.index') }}" class="flex-1">
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
            Agregar tipo
        </x-ui.button>
    </div>

    @include('modules.admin.tipos-servicio._tabla')
    @include('modules.admin.tipos-servicio._drawer-filtros')
    @include('modules.admin.tipos-servicio._modal')
    @include('modules.admin.tipos-servicio._modal-confirm')
    @include('modules.admin.tipos-servicio._modal-delete')

</div>
</x-layouts.app>
