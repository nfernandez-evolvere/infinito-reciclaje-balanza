@props(['usuarios', 'activeFilters', 'title' => 'Usuarios', 'description' => 'Solo los usuarios activos pueden iniciar sesión.'])

<x-ui.card variant="elevated">
    <x-ui.card.header>
        <x-ui.card.title>{{ $title }}</x-ui.card.title>
        <x-ui.card.description>{{ $description }}</x-ui.card.description>
    </x-ui.card.header>
    <x-ui.card.content>
    <x-ui.table variant="flat">
        <x-ui.table.header>
            <x-ui.table.row>
                <x-ui.table.head>Usuario</x-ui.table.head>
                <x-ui.table.head>Rol</x-ui.table.head>
                <x-ui.table.head>Estado</x-ui.table.head>
                <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
            </x-ui.table.row>
        </x-ui.table.header>
        <x-ui.table.body>
            @forelse($usuarios as $usuario)
            <x-ui.table.row>
                <x-ui.table.cell data-label="Usuario" stack="true">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :alt="$usuario->name" size="sm" />
                        <div class="text-left">
                            <p class="font-medium leading-tight">{{ $usuario->name }}</p>
                            <p class="text-xs text-muted-foreground">{{ $usuario->email }}</p>
                        </div>
                    </div>
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Rol">
                    @if($usuario->isAdmin())
                        <x-ui.badge variant="default">Admin</x-ui.badge>
                    @else
                        <x-ui.badge variant="secondary">Operador</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell data-label="Estado">
                    @if($usuario->activo)
                        <x-ui.badge variant="success">Activo</x-ui.badge>
                    @else
                        <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                    @endif
                </x-ui.table.cell>
                <x-ui.table.cell actions>
                    <form id="toggle-{{ $usuario->id }}" method="POST"
                        action="{{ route('admin.usuarios.toggle', $usuario) }}" class="hidden">
                        @csrf @method('PATCH')
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
                                    {{ $usuario->id }},
                                    {{ Js::from($usuario->name) }},
                                    {{ Js::from($usuario->email) }},
                                    {{ Js::from($usuario->role) }}
                                )"
                            >
                                <x-lucide-pencil class="size-4" />
                                Editar
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item
                                @click="openResetPassword({{ $usuario->id }}, {{ Js::from($usuario->name) }}); open = false"
                                :closeOnClick="false"
                            >
                                <x-lucide-key-round class="size-4" />
                                Restablecer contraseña
                            </x-ui.dropdown-menu.item>
                            @if($usuario->id !== auth()->id())
                                <x-ui.dropdown-menu.separator />
                                <x-ui.dropdown-menu.item
                                    variant="{{ $usuario->activo ? 'destructive' : 'default' }}"
                                    :closeOnClick="false"
                                    @click="confirmToggle({{ $usuario->id }}, {{ Js::from($usuario->name) }}, {{ $usuario->activo ? 'true' : 'false' }}); open = false"
                                >
                                    @if($usuario->activo)
                                        <x-lucide-ban class="size-4" />
                                        Desactivar
                                    @else
                                        <x-lucide-circle-check class="size-4" />
                                        Activar
                                    @endif
                                </x-ui.dropdown-menu.item>
                            @endif
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </x-ui.table.cell>
            </x-ui.table.row>

            @empty
            <tr>
                <td colspan="4">
                    @if($activeFilters > 0)
                        <x-ui.empty-state
                            icon="filter-x"
                            title="Sin resultados"
                            description="Ningún usuario coincide con los filtros aplicados."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <x-ui.button href="{{ route('admin.usuarios.index') }}">
                                <x-lucide-x class="size-4" />
                                Limpiar filtros
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.empty-state
                            icon="users"
                            title="Todavía no hay usuarios"
                            description="Creá el primero para que pueda iniciar sesión en el sistema."
                            class="rounded-none border-0 bg-transparent"
                        >
                            <x-ui.button size="sm" @click="openCreate()">
                                <x-lucide-plus class="size-4" />
                                Nuevo usuario
                            </x-ui.button>
                        </x-ui.empty-state>
                    @endif
                </td>
            </tr>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>
    </x-ui.card.content>
</x-ui.card>

@if($usuarios->hasPages())
    <x-ui.pagination>
        <x-ui.pagination.content>

            <x-ui.pagination.item>
                <x-ui.pagination.previous
                    :href="$usuarios->previousPageUrl()"
                    :disabled="$usuarios->onFirstPage()"
                />
            </x-ui.pagination.item>

            <x-ui.pagination.item class="sm:hidden">
                <span class="px-2 text-sm text-muted-foreground tabular-nums">
                    {{ $usuarios->currentPage() }} / {{ $usuarios->lastPage() }}
                </span>
            </x-ui.pagination.item>

            @for($page = 1; $page <= $usuarios->lastPage(); $page++)
                @php $isActive = $page === $usuarios->currentPage(); @endphp
                <x-ui.pagination.item class="hidden sm:list-item">
                    <x-ui.pagination.link
                        :href="$usuarios->url($page)"
                        :active="$isActive"
                    >
                        {{ $page }}
                    </x-ui.pagination.link>
                </x-ui.pagination.item>
            @endfor

            <x-ui.pagination.item>
                <x-ui.pagination.next
                    :href="$usuarios->nextPageUrl()"
                    :disabled="!$usuarios->hasMorePages()"
                />
            </x-ui.pagination.item>

        </x-ui.pagination.content>
    </x-ui.pagination>
@endif
