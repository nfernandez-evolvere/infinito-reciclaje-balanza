@props(['filters', 'hayFiltros'])

<div class="hidden sm:flex items-center justify-end gap-2 shrink-0">
    <x-ui.button variant="ghost" @click="filterOpen = true" class="relative">
        <x-lucide-sliders-horizontal class="size-4" />
        Filtros
        @if($hayFiltros)
            <span class="absolute -top-1 -right-1 flex size-2 rounded-full bg-primary"></span>
        @endif
    </x-ui.button>
    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        Agregar zona
    </x-ui.button>
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
            <form method="GET" action="{{ route('admin.zonas.index') }}" class="space-y-3">
                <div class="space-y-1.5">
                    <x-ui.label>Nombre</x-ui.label>
                    <x-ui.input type="search" name="nombre" value="{{ $filters['nombre'] ?? '' }}" placeholder="Buscar por nombre…" />
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
                        <x-ui.button variant="secondary" href="{{ route('admin.zonas.index') }}" class="flex-1">
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
        Agregar zona
    </x-ui.button>

</div>
