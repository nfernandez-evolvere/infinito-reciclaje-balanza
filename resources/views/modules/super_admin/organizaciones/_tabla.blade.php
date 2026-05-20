<div class="flex justify-end">
    <x-ui.button @click="openCreate()">
        <x-lucide-plus class="size-4" />
        <span class="hidden sm:inline">Nueva organización</span>
    </x-ui.button>
</div>

<x-ui.card>
    <x-ui.table>
        <x-ui.table.header>
            <x-ui.table.row>
                <x-ui.table.head>Organización</x-ui.table.head>
                <x-ui.table.head>Estado</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
            </x-ui.table.row>
        </x-ui.table.header>
        <x-ui.table.body>
            @forelse($organizaciones as $org)
            <x-ui.table.row>
                <x-ui.table.cell data-label="Organización">
                    <div class="text-left">
                        <p class="font-medium leading-tight">{{ $org->nombre }}</p>
                        @if($org->slug)
                            <p class="text-xs text-muted-foreground font-mono">{{ $org->slug }}</p>
                        @endif
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Estado">
                    @if($org->activo)
                        <x-ui.badge variant="success">Activa</x-ui.badge>
                    @else
                        <x-ui.badge variant="secondary">Inactiva</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Acciones" class="text-right">
                    <form id="toggle-{{ $org->id }}" method="POST"
                        action="{{ route('super.organizaciones.toggle', $org) }}" class="hidden">
                        @csrf @method('PATCH')
                    </form>
                    <form id="delete-{{ $org->id }}" method="POST"
                        action="{{ route('super.organizaciones.destroy', $org) }}" class="hidden">
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
                                    {{ $org->id }},
                                    {{ Js::from($org->nombre) }},
                                    {{ Js::from($org->slug) }}
                                )"
                            >
                                <x-lucide-pencil class="size-4" />
                                Editar
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.separator />
                            <x-ui.dropdown-menu.item
                                variant="{{ $org->activo ? 'destructive' : 'default' }}"
                                :closeOnClick="false"
                                @click="confirmToggle({{ $org->id }}, {{ Js::from($org->nombre) }}, {{ $org->activo ? 'true' : 'false' }}); open = false"
                            >
                                @if($org->activo)
                                    <x-lucide-ban class="size-4" />
                                    Desactivar
                                @else
                                    <x-lucide-circle-check class="size-4" />
                                    Activar
                                @endif
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item
                                variant="destructive"
                                :closeOnClick="false"
                                @click="confirmDelete({{ $org->id }}, {{ Js::from($org->nombre) }}); open = false"
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
                <td colspan="3">
                    <x-ui.empty-state
                        icon="building-2"
                        title="Todavía no hay organizaciones"
                        description="Creá la primera para que pueda operar en el sistema."
                        class="rounded-none border-0 bg-transparent"
                    >
                        <x-ui.button size="sm" @click="openCreate()">
                            <x-lucide-plus class="size-4" />
                            Nueva organización
                        </x-ui.button>
                    </x-ui.empty-state>
                </td>
            </tr>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>
</x-ui.card>

@if($organizaciones->hasPages())
    <x-ui.pagination>
        <x-ui.pagination.content>

            <x-ui.pagination.item>
                <x-ui.pagination.previous
                    :href="$organizaciones->previousPageUrl()"
                    :disabled="$organizaciones->onFirstPage()"
                />
            </x-ui.pagination.item>

            <x-ui.pagination.item class="sm:hidden">
                <span class="px-2 text-sm text-muted-foreground tabular-nums">
                    {{ $organizaciones->currentPage() }} / {{ $organizaciones->lastPage() }}
                </span>
            </x-ui.pagination.item>

            @for($page = 1; $page <= $organizaciones->lastPage(); $page++)
                @php $isActive = $page === $organizaciones->currentPage(); @endphp
                <x-ui.pagination.item class="hidden sm:list-item">
                    <x-ui.pagination.link
                        :href="$organizaciones->url($page)"
                        :active="$isActive"
                    >
                        {{ $page }}
                    </x-ui.pagination.link>
                </x-ui.pagination.item>
            @endfor

            <x-ui.pagination.item>
                <x-ui.pagination.next
                    :href="$organizaciones->nextPageUrl()"
                    :disabled="!$organizaciones->hasMorePages()"
                />
            </x-ui.pagination.item>

        </x-ui.pagination.content>
    </x-ui.pagination>
@endif
