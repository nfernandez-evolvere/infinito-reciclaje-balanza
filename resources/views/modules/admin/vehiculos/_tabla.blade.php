@php $activeFilters = count(array_filter($filters ?? [], fn($v) => $v !== null && $v !== '')); @endphp

<div class="flex justify-end gap-2">
    <x-ui.button variant="secondary" @click="filterOpen = true" class="relative">
        <x-lucide-filter class="size-4" />
        <span class="hidden sm:inline">Filtros</span>
        @if($activeFilters > 0)
            <span class="absolute -top-1 -right-1 flex size-4 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-primary-foreground leading-none">
                {{ $activeFilters }}
            </span>
        @endif
    </x-ui.button>
    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        <span class="hidden sm:inline">Nuevo vehículo</span>
    </x-ui.button>
</div>

<x-ui.card variant="elevated">
    <x-ui.table>
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
            @forelse($vehiculos as $vehiculo)
            <x-ui.table.row>
                <x-ui.table.cell data-label="Patente" class="font-medium font-mono">
                    {{ $vehiculo->patente }}
                </x-ui.table.cell>
                <x-ui.table.cell data-label="N.° interno" class="font-mono text-muted-foreground">
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
                <x-ui.table.cell data-label="Acciones" class="text-right">
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
                                    {{ Js::from($vehiculo->observaciones ?? '') }}
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

            @empty
            <tr>
                <td colspan="7">
                    @if($activeFilters > 0)
                        <x-ui.empty-state
                            icon="filter-x"
                            title="Sin resultados"
                            description="Ningún vehículo coincide con los filtros aplicados."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <a href="{{ route('admin.vehiculos.index') }}">
                                <x-ui.button>
                                    <x-lucide-x class="size-4" />
                                    Limpiar filtros
                                </x-ui.button>
                            </a>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state
                            icon="truck"
                            title="Todavía no hay vehículos"
                            description="Creá el primero para que los operadores puedan seleccionarlo al registrar pesajes."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <x-ui.button size="sm" @click="openCreate()">
                                <x-lucide-plus class="size-4" />
                                Nuevo vehículo
                            </x-ui.button>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>
</x-ui.card>

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
