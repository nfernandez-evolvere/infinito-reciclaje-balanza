@props(['tipos', 'activeFilters'])

<x-ui.card variant="elevated">
    <x-ui.table variant="flat">
        <x-ui.table.header>
            <x-ui.table.row>
                <x-ui.table.head>Tipo</x-ui.table.head>
                <x-ui.table.head>Bruto mínimo</x-ui.table.head>
                <x-ui.table.head>Bruto máximo</x-ui.table.head>
                <x-ui.table.head>Estado</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
            </x-ui.table.row>
        </x-ui.table.header>
        <x-ui.table.body>
            @forelse($tipos as $tipo)
            <x-ui.table.row>
                <x-ui.table.cell data-label="Tipo" class="text-left font-medium">{{ $tipo->nombre }}</x-ui.table.cell>
                <x-ui.table.cell data-label="Bruto mínimo">
                    {{ number_format($tipo->peso_min_kg) }} kg
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Bruto máximo">
                    {{ number_format($tipo->peso_max_kg) }} kg
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Estado">
                    @if($tipo->activo)
                        <x-ui.badge variant="success">Activo</x-ui.badge>
                    @else
                        <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <form id="toggle-{{ $tipo->id }}" method="POST"
                        action="{{ route('admin.tipos-vehiculo.toggle', $tipo) }}" class="hidden">
                        @csrf @method('PATCH')
                    </form>
                    <form id="delete-{{ $tipo->id }}" method="POST"
                        action="{{ route('admin.tipos-vehiculo.destroy', $tipo) }}" class="hidden">
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
                                @click="openEdit({{ $tipo->id }}, {{ Js::from($tipo->nombre) }}, {{ $tipo->peso_min_kg }}, {{ $tipo->peso_max_kg }})"
                            >
                                <x-lucide-pencil class="size-4" />
                                Editar
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item
                                variant="{{ $tipo->activo ? 'destructive' : 'default' }}"
                                :closeOnClick="false"
                                @click="confirmToggle({{ $tipo->id }}, {{ Js::from($tipo->nombre) }}, {{ $tipo->activo ? 'true' : 'false' }}); open = false"
                            >
                                @if($tipo->activo)
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
                                @click="confirmDelete({{ $tipo->id }}, {{ Js::from($tipo->nombre) }}); open = false"
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
                <td colspan="5">
                    @if($activeFilters > 0)
                        <x-ui.empty-state
                            icon="filter-x"
                            title="Sin resultados"
                            description="Ningún tipo coincide con los filtros aplicados."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <x-ui.button href="{{ route('admin.vehiculos.index', ['tab' => 'tipos']) }}">
                                <x-lucide-x class="size-4" />
                                Limpiar filtros
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state
                            icon="car"
                            title="Todavía no hay tipos de vehículo"
                            description="Creá el primero para que el sistema pueda validar los rangos de peso en cada pesaje."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <x-ui.button size="sm" @click="openCreate()">
                                <x-lucide-plus class="size-4" />
                                Nuevo tipo
                            </x-ui.button>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>
</x-ui.card>

@if($tipos->hasPages())
    <x-ui.pagination>
        <x-ui.pagination.content>

            <x-ui.pagination.item>
                <x-ui.pagination.previous
                    :href="$tipos->previousPageUrl()"
                    :disabled="$tipos->onFirstPage()"
                />
            </x-ui.pagination.item>

            <x-ui.pagination.item class="sm:hidden">
                <span class="px-2 text-sm text-muted-foreground tabular-nums">
                    {{ $tipos->currentPage() }} / {{ $tipos->lastPage() }}
                </span>
            </x-ui.pagination.item>

            @for($page = 1; $page <= $tipos->lastPage(); $page++)
                @php $isActive = $page === $tipos->currentPage(); @endphp
                <x-ui.pagination.item class="hidden sm:list-item">
                    <x-ui.pagination.link
                        :href="$tipos->url($page)"
                        :active="$isActive"
                    >
                        {{ $page }}
                    </x-ui.pagination.link>
                </x-ui.pagination.item>
            @endfor

            <x-ui.pagination.item>
                <x-ui.pagination.next
                    :href="$tipos->nextPageUrl()"
                    :disabled="!$tipos->hasMorePages()"
                />
            </x-ui.pagination.item>

        </x-ui.pagination.content>
    </x-ui.pagination>
@endif
