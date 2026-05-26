@php
    $hasErrors = $errors->any();
    $isEditing = old('_mode') === 'edit';

    $initial = $hasErrors ? [
        'modalOpen' => true,
        'modalMode' => $isEditing ? 'edit' : 'create',
        'form'      => [
            'id'          => (int) old('_editing_id', 0) ?: null,
            'nombre'      => old('nombre', ''),
            'peso_min_kg' => old('peso_min_kg', ''),
            'peso_max_kg' => old('peso_max_kg', ''),
        ],
    ] : [];

    $hayFiltros    = collect($filters)->filter(fn($v) => $v !== null && $v !== '')->isNotEmpty();
    $activeFilters = count(array_filter($filters, fn($v) => $v !== null && $v !== ''));
@endphp

<x-layouts.app title="Tipos de vehículo">
<div x-data="tiposVehiculo({{ Js::from($initial) }})" class="flex flex-col gap-6">

    <div class="flex items-start justify-between gap-4">
        <div>
            <x-ui.typography as="h2">Tipos de vehículo</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1">Rangos de peso bruto esperados por tipo (vehículo + carga). Se usan para detectar pesajes anómalos.</x-ui.typography>
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
                <form method="GET" action="{{ route('admin.tipos-vehiculo.index') }}" class="space-y-3">
                    <div class="space-y-1.5">
                        <x-ui.label>Tipo</x-ui.label>
                        <x-ui.input type="search" name="nombre" value="{{ $filters['nombre'] ?? '' }}" placeholder="Buscar por tipo…" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label>Bruto mínimo desde (kg)</x-ui.label>
                        <x-ui.input type="number" name="peso_min" min="0" placeholder="0" value="{{ $filters['peso_min'] ?? '' }}" />
                    </div>
                    <div class="space-y-1.5">
                        <x-ui.label>Bruto máximo hasta (kg)</x-ui.label>
                        <x-ui.input type="number" name="peso_max" min="0" placeholder="0" value="{{ $filters['peso_max'] ?? '' }}" />
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
                            <x-ui.button variant="secondary" href="{{ route('admin.tipos-vehiculo.index') }}" class="flex-1">
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

    @include('modules.admin.tipos-vehiculo._tabla')
    @include('modules.admin.tipos-vehiculo._drawer-filtros')
    @include('modules.admin.tipos-vehiculo._modal')
    @include('modules.admin.tipos-vehiculo._modal-confirm')
    @include('modules.admin.tipos-vehiculo._modal-delete')

</div>
</x-layouts.app>
