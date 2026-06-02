@props(['vehiculos', 'activeFilters', 'title' => 'Vehículos', 'description' => 'Padrón de vehículos habilitados para registrar pesajes. La tara se copia automáticamente al crear un pesaje.'])

@if($vehiculos->isEmpty())
    @if($activeFilters > 0)
        <x-ui.empty-state
            icon="filter-x"
            title="Sin resultados"
            description="Ningún vehículo coincide con los filtros aplicados."
        >
            <x-ui.button href="{{ route('admin.vehiculos.index') }}">
                <x-lucide-x class="size-4" />
                Limpiar filtros
            </x-ui.button>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state
            icon="truck"
            title="Todavía no hay vehículos"
            description="Creá el primero para que los operadores puedan seleccionarlo al registrar pesajes."
        >
            <x-ui.button size="sm" @click="openCreate()">
                <x-lucide-plus class="size-4" />
                Nuevo vehículo
            </x-ui.button>
        </x-ui.empty-state>
    @endif
@else

{{-- Mobile --}}
<div class="sm:hidden space-y-2">
    @foreach($vehiculos as $vehiculo)
    <x-ui.card variant="elevated" class="p-3">
        <x-ui.card.header class="items-center gap-2">
            <div class="min-w-0">
                <p class="font-semibold text-sm">{{ $vehiculo->patente }}</p>
                @if($vehiculo->numero_interno)
                    <p class="text-xs text-muted-foreground">#{{ $vehiculo->numero_interno }}</p>
                @endif
            </div>
            <x-slot:actions>
                @if($vehiculo->activo)
                    <x-ui.badge variant="success">Activo</x-ui.badge>
                @else
                    <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                @endif
                <x-ui.dropdown-menu>
                    <x-ui.dropdown-menu.trigger>
                        <x-ui.button variant="ghost" size="icon" class="size-7 -mr-1">
                            <x-lucide-ellipsis class="size-4" />
                        </x-ui.button>
                    </x-ui.dropdown-menu.trigger>
                    <x-ui.dropdown-menu.content align="end">
                        <x-ui.dropdown-menu.item
                            @click="openEdit(
                                {{ $vehiculo->id }},
                                {{ Js::from($vehiculo->patente) }},
                                {{ Js::from($vehiculo->numero_interno) }},
                                {{ $vehiculo->tara_kg }},
                                {{ $vehiculo->tipo_vehiculo_id }},
                                {{ Js::from($vehiculo->titular) }},
                                {{ $vehiculo->capacidad_kg ?? 'null' }},
                                {{ Js::from($vehiculo->observaciones ?? '') }},
                                {{ $vehiculo->pesajes_count ?? 0 }}
                            )"
                        >
                            <x-lucide-pencil class="size-4" />
                            Editar
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item
                            variant="{{ $vehiculo->activo ? 'destructive' : 'default' }}"
                            :closeOnClick="false"
                            @click="confirmToggle({{ $vehiculo->id }}, {{ Js::from($vehiculo->patente) }}, {{ $vehiculo->activo ? 'true' : 'false' }}); open = false"
                        >
                            @if($vehiculo->activo)
                                <x-lucide-ban class="size-4" />
                                Desactivar
                            @else
                                <x-lucide-check-circle class="size-4" />
                                Activar
                            @endif
                        </x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.separator />
                        <x-ui.dropdown-menu.item
                            variant="destructive"
                            :closeOnClick="false"
                            @click="confirmDelete({{ $vehiculo->id }}, {{ Js::from($vehiculo->patente) }}); open = false"
                        >
                            <x-lucide-trash-2 class="size-4" />
                            Eliminar
                        </x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.content>
                </x-ui.dropdown-menu>
            </x-slot:actions>
        </x-ui.card.header>
        <x-ui.card.content class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm pt-2">
            <div>
                <p class="text-xs text-muted-foreground">Tipo</p>
                <p>{{ $vehiculo->tipoVehiculo?->nombre ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-muted-foreground">Tara</p>
                <p class="tabular-nums">{{ number_format($vehiculo->tara_kg) }} kg</p>
            </div>
            <div class="col-span-2">
                <p class="text-xs text-muted-foreground">Titular</p>
                <p class="truncate">{{ $vehiculo->titular }}</p>
            </div>
        </x-ui.card.content>
    </x-ui.card>
    @endforeach
</div>

{{-- Desktop --}}
<div class="hidden sm:block">
    <x-ui.card variant="elevated">
        <x-ui.card.header>
            <x-ui.card.title>{{ $title }}</x-ui.card.title>
            <x-ui.card.description>{{ $description }}</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <x-ui.table variant="flat">
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head>Patente</x-ui.table.head>
                        <x-ui.table.head>N.° interno</x-ui.table.head>
                        <x-ui.table.head>Tipo</x-ui.table.head>
                        <x-ui.table.head>Tara</x-ui.table.head>
                        <x-ui.table.head>Titular</x-ui.table.head>
                        <x-ui.table.head>Estado</x-ui.table.head>
                        <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach($vehiculos as $vehiculo)
                    <x-ui.table.row>
                        <x-ui.table.cell data-label="Patente" class="font-medium">
                            {{ $vehiculo->patente }}
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="N.° interno" class="text-muted-foreground">
                            {{ $vehiculo->numero_interno }}
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Tipo">
                            {{ $vehiculo->tipoVehiculo?->nombre ?? '—' }}
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Tara">
                            {{ number_format($vehiculo->tara_kg) }} kg
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Titular" class="text-muted-foreground">
                            {{ $vehiculo->titular }}
                        </x-ui.table.cell>
                        <x-ui.table.cell data-label="Estado">
                            @if($vehiculo->activo)
                                <x-ui.badge variant="success">Activo</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell actions>
                            <form id="toggle-{{ $vehiculo->id }}" method="POST"
                                action="{{ route('admin.vehiculos.toggle', $vehiculo) }}" class="hidden">
                                @csrf @method('PATCH')
                            </form>
                            <form id="delete-{{ $vehiculo->id }}" method="POST"
                                action="{{ route('admin.vehiculos.destroy', $vehiculo) }}" class="hidden">
                                @csrf @method('DELETE')
                            </form>

                            <x-ui.dropdown-menu align="end">
                                <x-ui.dropdown-menu.trigger>
                                    <x-ui.button variant="ghost" size="icon" class="size-8">
                                        <x-lucide-ellipsis class="size-4" />
                                    </x-ui.button>
                                </x-ui.dropdown-menu.trigger>
                                <x-ui.dropdown-menu.content>
                                    <x-ui.dropdown-menu.item
                                        @click="openEdit(
                                            {{ $vehiculo->id }},
                                            {{ Js::from($vehiculo->patente) }},
                                            {{ Js::from($vehiculo->numero_interno) }},
                                            {{ $vehiculo->tara_kg }},
                                            {{ $vehiculo->tipo_vehiculo_id }},
                                            {{ Js::from($vehiculo->titular) }},
                                            {{ $vehiculo->capacidad_kg ?? 'null' }},
                                            {{ Js::from($vehiculo->observaciones ?? '') }},
                                            {{ $vehiculo->pesajes_count ?? 0 }}
                                        )"
                                    >
                                        <x-lucide-pencil class="size-4" />
                                        Editar
                                    </x-ui.dropdown-menu.item>
                                    <x-ui.dropdown-menu.item
                                        variant="{{ $vehiculo->activo ? 'destructive' : 'default' }}"
                                        :closeOnClick="false"
                                        @click="confirmToggle({{ $vehiculo->id }}, {{ Js::from($vehiculo->patente) }}, {{ $vehiculo->activo ? 'true' : 'false' }}); open = false"
                                    >
                                        @if($vehiculo->activo)
                                            <x-lucide-ban class="size-4" />
                                            Desactivar
                                        @else
                                            <x-lucide-check-circle class="size-4" />
                                            Activar
                                        @endif
                                    </x-ui.dropdown-menu.item>
                                    <x-ui.dropdown-menu.separator />
                                    <x-ui.dropdown-menu.item
                                        variant="destructive"
                                        :closeOnClick="false"
                                        @click="confirmDelete({{ $vehiculo->id }}, {{ Js::from($vehiculo->patente) }}); open = false"
                                    >
                                        <x-lucide-trash-2 class="size-4" />
                                        Eliminar
                                    </x-ui.dropdown-menu.item>
                                </x-ui.dropdown-menu.content>
                            </x-ui.dropdown-menu>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </x-ui.card.content>
    </x-ui.card>
</div>

@endif

@if($vehiculos->hasPages())
    <x-ui.pagination>
        <x-ui.pagination.content>

            <x-ui.pagination.item>
                <x-ui.pagination.previous
                    :href="$vehiculos->previousPageUrl()"
                    :disabled="$vehiculos->onFirstPage()"
                />
            </x-ui.pagination.item>

            <x-ui.pagination.item class="sm:hidden">
                <span class="px-2 text-sm text-muted-foreground tabular-nums">
                    {{ $vehiculos->currentPage() }} / {{ $vehiculos->lastPage() }}
                </span>
            </x-ui.pagination.item>

            @for($page = 1; $page <= $vehiculos->lastPage(); $page++)
                @php $isActive = $page === $vehiculos->currentPage(); @endphp
                <x-ui.pagination.item class="hidden sm:list-item">
                    <x-ui.pagination.link
                        :href="$vehiculos->url($page)"
                        :active="$isActive"
                    >
                        {{ $page }}
                    </x-ui.pagination.link>
                </x-ui.pagination.item>
            @endfor

            <x-ui.pagination.item>
                <x-ui.pagination.next
                    :href="$vehiculos->nextPageUrl()"
                    :disabled="!$vehiculos->hasMorePages()"
                />
            </x-ui.pagination.item>

        </x-ui.pagination.content>
    </x-ui.pagination>
@endif
