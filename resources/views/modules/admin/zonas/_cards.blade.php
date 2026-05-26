@if($zonas->isEmpty())
    <x-ui.empty-state
        icon="map-pin"
        title="Todavía no hay zonas"
        description="Creá la primera zona para que el sistema pueda agrupar pesajes por área geográfica."
    >
        <x-ui.button size="sm" @click="openCreate()">
            <x-lucide-plus class="size-4" />
            Agregar zona
        </x-ui.button>
    </x-ui.empty-state>
@else
    <div class="space-y-4">
        @foreach($zonas as $zona)
            @php $servicioCount = $zona->zonaServicios->count(); @endphp

            {{-- Forms ocultos para toggle y delete de la zona --}}
            <form id="toggle-{{ $zona->id }}" method="POST"
                action="{{ route('admin.zonas.toggle', $zona) }}" class="hidden">
                @csrf @method('PATCH')
            </form>
            <form id="delete-{{ $zona->id }}" method="POST"
                action="{{ route('admin.zonas.destroy', $zona) }}" class="hidden">
                @csrf @method('DELETE')
            </form>

            {{-- Forms ocultos para quitar servicios --}}
            @foreach($zona->zonaServicios as $zs)
                <form id="quitar-{{ $zona->id }}-{{ $zs->tipo_servicio_id }}" method="POST"
                    action="{{ route('admin.zonas.servicios.destroy', [$zona, $zs->tipoServicio]) }}" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach

            <x-ui.card variant="elevated" :collapsible="true" :startOpen="false">

                <x-ui.card.header class="pb-3">
                    <div class="flex items-start justify-between gap-2 sm:gap-4">

                        {{-- Área clickeable para colapsar --}}
                        <button
                            type="button"
                            class="flex-1 text-left min-w-0 space-y-1"
                            @click="open = !open"
                        >
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-base font-semibold">{{ $zona->nombre }}</span>
                                @if($zona->activo)
                                    <x-ui.badge variant="success">Activo</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                                @endif
                            </div>
                            <p class="text-sm text-muted-foreground">
                                @if($zona->hectareas) {{ number_format($zona->hectareas, 2, ',', '.') }} ha @endif
                                @if($zona->hectareas && $zona->barrios) &middot; @endif
                                @if($zona->barrios) {{ $zona->barrios }} {{ $zona->barrios === 1 ? 'barrio' : 'barrios' }} @endif
                                @if($zona->hectareas || $zona->barrios) &middot; @endif
                                {{ $servicioCount }} {{ $servicioCount === 1 ? 'servicio asignado' : 'servicios asignados' }}
                            </p>
                        </button>

                        <div class="flex items-center gap-1 shrink-0">

                            {{-- Ver / Ocultar servicios --}}
                            <x-ui.button type="button" size="sm" @click="open = !open">
                                <x-lucide-chevron-down
                                    class="size-4 transition-transform duration-200"
                                    x-bind:class="open ? 'rotate-0' : '-rotate-90'"
                                />
                                <span class="hidden sm:inline" x-text="open ? 'Ocultar servicios' : 'Ver servicios'"></span>
                            </x-ui.button>

                            {{-- Dropdown de acciones de la zona --}}
                            <x-ui.dropdown-menu align="end">
                                <x-ui.dropdown-menu.trigger>
                                    <x-ui.button variant="ghost" size="icon" class="size-8">
                                        <x-lucide-ellipsis class="size-4" />
                                    </x-ui.button>
                                </x-ui.dropdown-menu.trigger>
                                <x-ui.dropdown-menu.content>
                                    <x-ui.dropdown-menu.item
                                        @click="openEdit(
                                            {{ $zona->id }},
                                            {{ Js::from($zona->nombre) }},
                                            {{ Js::from($zona->hectareas) }},
                                            {{ Js::from($zona->barrios) }},
                                            {{ Js::from($zona->habitantes) }}
                                        )"
                                    >
                                        <x-lucide-pencil class="size-4" />
                                        Editar
                                    </x-ui.dropdown-menu.item>
                                    <x-ui.dropdown-menu.item
                                        variant="{{ $zona->activo ? 'destructive' : 'default' }}"
                                        :closeOnClick="false"
                                        @click="confirmToggle({{ $zona->id }}, {{ Js::from($zona->nombre) }}, {{ $zona->activo ? 'true' : 'false' }}); open = false"
                                    >
                                        @if($zona->activo)
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
                                        @click="confirmDelete({{ $zona->id }}, {{ Js::from($zona->nombre) }}); open = false"
                                    >
                                        <x-lucide-trash-2 class="size-4" />
                                        Eliminar
                                    </x-ui.dropdown-menu.item>
                                </x-ui.dropdown-menu.content>
                            </x-ui.dropdown-menu>

                        </div>
                    </div>
                </x-ui.card.header>

                {{-- Contenido colapsable --}}
                <x-ui.card.content x-show="open" x-collapse class="pt-0">
                    <x-ui.separator class="mb-4" />

                    {{-- Sección servicios asignados --}}
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-overline">Servicios asignados</span>
                        <x-ui.button
                            variant="ghost"
                            size="sm"
                            @click="openAsignarServicio({{ $zona->id }}, {{ Js::from($zona->nombre) }}, {{ Js::from($zona->zonaServicios->pluck('tipo_servicio_id')->toArray()) }})"
                        >
                            <x-lucide-plus class="size-3.5" />
                            Asignar
                        </x-ui.button>
                    </div>

                    @if($zona->zonaServicios->isEmpty())
                        <x-ui.empty-state
                            icon="layers"
                            title="Sin servicios asignados"
                            description="Esta zona no aparecerá en el formulario de pesaje hasta que tenga al menos un servicio."
                            class="rounded-md p-6"
                        >
                            <x-ui.button
                                variant="outline"
                                size="sm"
                                @click="openAsignarServicio({{ $zona->id }}, {{ Js::from($zona->nombre) }}, {{ Js::from($zona->zonaServicios->pluck('tipo_servicio_id')->toArray()) }})"
                            >
                                <x-lucide-plus class="size-3.5" />
                                Asignar servicio
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.table>
                            <x-ui.table.header>
                                <x-ui.table.row>
                                    <x-ui.table.head>Servicio</x-ui.table.head>
                                    <x-ui.table.head>Turnos</x-ui.table.head>
                                    <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
                                </x-ui.table.row>
                            </x-ui.table.header>
                            <x-ui.table.body>
                                @foreach($zona->zonaServicios as $zs)
                                    <x-ui.table.row>
                                        <x-ui.table.cell class="font-medium">
                                            {{ $zs->tipoServicio->nombre }}
                                        </x-ui.table.cell>
                                        <x-ui.table.cell>
                                            @if($zs->turnos->isNotEmpty())
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach($zs->turnos as $turno)
                                                        <x-ui.badge variant="secondary">{{ $turno->turno }}</x-ui.badge>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted-foreground text-sm">Sin turno</span>
                                            @endif
                                        </x-ui.table.cell>
                                        <x-ui.table.cell class="text-right">
                                            <x-ui.dropdown-menu align="end">
                                                <x-ui.dropdown-menu.trigger>
                                                    <x-ui.button variant="ghost" size="icon" class="size-7">
                                                        <x-lucide-ellipsis class="size-4" />
                                                    </x-ui.button>
                                                </x-ui.dropdown-menu.trigger>
                                                <x-ui.dropdown-menu.content>
                                                    <x-ui.dropdown-menu.item
                                                        @click="openEditServicio(
                                                            {{ $zona->id }},
                                                            {{ Js::from($zona->nombre) }},
                                                            {{ $zs->tipo_servicio_id }},
                                                            {{ Js::from($zs->tipoServicio->nombre) }},
                                                            {{ Js::from($zs->turnos_array) }},
                                                            {{ Js::from($zs->horarios_por_dia) }}
                                                        )"
                                                    >
                                                        <x-lucide-pencil class="size-4" />
                                                        Editar
                                                    </x-ui.dropdown-menu.item>
                                                    <x-ui.dropdown-menu.separator />
                                                    <x-ui.dropdown-menu.item
                                                        variant="destructive"
                                                        :closeOnClick="false"
                                                        @click="confirmQuitarServicio(
                                                            {{ $zona->id }},
                                                            {{ Js::from($zona->nombre) }},
                                                            {{ $zs->tipo_servicio_id }},
                                                            {{ Js::from($zs->tipoServicio->nombre) }}
                                                        ); open = false"
                                                    >
                                                        <x-lucide-trash-2 class="size-4" />
                                                        Quitar
                                                    </x-ui.dropdown-menu.item>
                                                </x-ui.dropdown-menu.content>
                                            </x-ui.dropdown-menu>
                                        </x-ui.table.cell>
                                    </x-ui.table.row>
                                @endforeach
                            </x-ui.table.body>
                        </x-ui.table>
                    @endif
                </x-ui.card.content>

            </x-ui.card>
        @endforeach
    </div>
@endif
