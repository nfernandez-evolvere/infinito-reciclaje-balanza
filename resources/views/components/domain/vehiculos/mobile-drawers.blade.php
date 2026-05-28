@props(['filters', 'tiposVehiculo', 'hayFiltros', 'activeFilters'])

<div class="hidden sm:flex items-center justify-end gap-2 shrink-0">
    <x-ui.button variant="ghost" @click="filterOpen = true" class="relative">
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
        Agregar vehículo
    </x-ui.button>
</div>

<div class="flex justify-end sm:hidden gap-2">
    <x-ui.sheet side="bottom">
        <x-slot:trigger>
            <x-ui.button size="icon" variant="ghost">
                <x-lucide-sliders-horizontal class="size-3.5" />
                @if($hayFiltros)
                    <x-ui.badge variant="secondary" class="ml-0.5">•</x-ui.badge>
                @endif
            </x-ui.button>
        </x-slot:trigger>
        <div class="p-6 pt-10 space-y-5 overflow-y-auto">
            <p class="text-label text-base">Filtros</p>
            <form method="GET" action="{{ route('admin.vehiculos.index') }}" class="space-y-3">
                <div class="space-y-1.5">
                    <x-ui.label>Patente</x-ui.label>
                    <x-ui.input type="search" name="patente" value="{{ $filters['patente'] ?? '' }}" placeholder="Buscar por patente…" />
                </div>
                <div class="space-y-1.5">
                    <x-ui.label>N.° interno</x-ui.label>
                    <x-ui.input type="search" name="numero_interno" value="{{ $filters['numero_interno'] ?? '' }}" placeholder="Buscar por número interno…" />
                </div>
                <div class="space-y-1.5">
                    <x-ui.label>Tipo de vehículo</x-ui.label>
                    <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Todos" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="">Todos</x-ui.select.item>
                            @foreach($tiposVehiculo as $tipo)
                                <x-ui.select.item value="{{ $tipo->id }}">{{ $tipo->nombre }}</x-ui.select.item>
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
                        <x-ui.button variant="secondary" href="{{ route('admin.vehiculos.index') }}" class="flex-1">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>
    </x-ui.sheet>

    <x-ui.button size="icon" @click="openCreate()">
        <x-lucide-plus class="size-3.5" />
    </x-ui.button>
</div>
