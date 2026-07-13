@props(['tipos', 'activeFilters'])

@if($tipos->isEmpty())
    @if($activeFilters > 0)
        <x-ui.empty-state
            icon="filter-x"
            title="Sin resultados"
            description="Ningún servicio coincide con los filtros aplicados."
        >
            <x-ui.button href="{{ route('admin.tipos-servicio.index') }}">
                <x-lucide-x class="size-4" />
                Limpiar filtros
            </x-ui.button>
        </x-ui.empty-state>
    @else
        <x-ui.empty-state
            icon="layers"
            title="Todavía no hay servicios"
            description="Creá el primero para clasificar los pesajes y definir sus zonas de operación."
        >
            <x-ui.button size="sm" @click="openCreate()">
                <x-lucide-plus class="size-4" />
                Nuevo servicio
            </x-ui.button>
        </x-ui.empty-state>
    @endif
@else
    <div class="space-y-4">
        @foreach($tipos as $tipo)
            @php $zonaCount = $tipo->zonas->count(); @endphp

            <form id="toggle-servicio-{{ $tipo->id }}" method="POST"
                action="{{ route('admin.tipos-servicio.toggle', $tipo) }}" class="hidden">
                @csrf @method('PATCH')
            </form>
            <form id="delete-servicio-{{ $tipo->id }}" method="POST"
                action="{{ route('admin.tipos-servicio.destroy', $tipo) }}" class="hidden">
                @csrf @method('DELETE')
            </form>

            @foreach($tipo->zonas as $zona)
                <form id="toggle-zona-{{ $zona->id }}" method="POST"
                    action="{{ route('admin.zonas.toggle', $zona) }}" class="hidden">
                    @csrf @method('PATCH')
                </form>
                <form id="delete-zona-{{ $zona->id }}" method="POST"
                    action="{{ route('admin.zonas.destroy', $zona) }}" class="hidden">
                    @csrf @method('DELETE')
                </form>
            @endforeach

            <x-ui.card variant="elevated" :collapsible="true" :startOpen="false">

                <x-ui.card.header class="pb-3">
                    <div class="flex items-start justify-between gap-2 sm:gap-4">
                        <button
                            type="button"
                            class="flex-1 text-left min-w-0 space-y-1.5"
                            @click="open = !open"
                        >
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-base font-semibold">{{ $tipo->nombre }}</span>
                                @if($tipo->activo)
                                    <x-ui.badge variant="success">Activo</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary">Inactivo</x-ui.badge>
                                @endif
                            </div>
                            @if($tipo->tiposVehiculo->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach($tipo->tiposVehiculo as $tv)
                                        <x-ui.badge variant="secondary">{{ $tv->nombre }}</x-ui.badge>
                                    @endforeach
                                </div>
                            @endif
                            <p class="text-sm text-muted-foreground">
                                {{ $zonaCount }} {{ $zonaCount === 1 ? 'zona' : 'zonas' }}
                            </p>
                        </button>

                        <div class="flex items-center gap-1 shrink-0">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="open = !open">
                                <x-lucide-chevron-down
                                    class="size-4 transition-transform duration-200"
                                    x-bind:class="open ? 'rotate-0' : '-rotate-90'"
                                />
                                <span class="hidden sm:inline" x-text="open ? 'Ocultar zonas' : 'Ver zonas'"></span>
                            </x-ui.button>

                            <x-ui.dropdown-menu align="end">
                                <x-ui.dropdown-menu.trigger>
                                    <x-ui.button variant="ghost" size="icon" class="size-8">
                                        <x-lucide-ellipsis class="size-4" />
                                    </x-ui.button>
                                </x-ui.dropdown-menu.trigger>
                                <x-ui.dropdown-menu.content>
                                    <x-ui.dropdown-menu.item
                                        @click="openEdit({{ $tipo->id }}, {{ Js::from($tipo->nombre) }}, {{ Js::from($tipo->descripcion) }}, {{ Js::from($tipo->tiposVehiculo->pluck('id')->toArray()) }})"
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
                        </div>
                    </div>
                </x-ui.card.header>

                <x-ui.card.content x-show="open" x-collapse class="pt-0">
                    <x-ui.separator class="mb-4" />

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-overline">Zonas de operación</span>
                        <x-ui.button
                            variant="secondary"
                            size="sm"
                            @click="openCreateZona({{ $tipo->id }}, {{ Js::from($tipo->nombre) }})"
                        >
                            <x-lucide-plus class="size-3.5" />
                            Agregar zona
                        </x-ui.button>
                    </div>

                    @if($tipo->zonas->isEmpty())
                        <x-ui.empty-state
                            icon="map-pin"
                            title="Sin zonas"
                            description="Este servicio no aparecerá en el formulario de pesaje hasta que tenga al menos una zona."
                            class="rounded-md p-6"
                        >
                            <x-ui.button
                                variant="outline"
                                size="sm"
                                @click="openCreateZona({{ $tipo->id }}, {{ Js::from($tipo->nombre) }})"
                            >
                                <x-lucide-plus class="size-3.5" />
                                Agregar zona
                            </x-ui.button>
                        </x-ui.empty-state>
                    @else
                        <x-ui.table class="rounded-md sm:border" variant="flat">
                            <x-ui.table.header>
                                <x-ui.table.row>
                                    <x-ui.table.head>Zona</x-ui.table.head>
                                    <x-ui.table.head>Datos</x-ui.table.head>
                                    <x-ui.table.head>Turnos</x-ui.table.head>
                                    <x-ui.table.head class="w-16 text-right">Acciones</x-ui.table.head>
                                </x-ui.table.row>
                            </x-ui.table.header>
                            <x-ui.table.body>
                                @foreach($tipo->zonas as $zona)
                                    @php $zonaPayload = [
                                        'id'             => $zona->id,
                                        'tipo_servicio_id' => $zona->tipo_servicio_id,
                                        'nombre'         => $zona->nombre,
                                        'hectareas'      => $zona->hectareas,
                                        'barrios'        => $zona->barrios,
                                        'habitantes'     => $zona->habitantes,
                                        'geojson'        => $zona->geojson,
                                        'centro_lat'     => $zona->centro_lat,
                                        'centro_lng'     => $zona->centro_lng,
                                        'turnos'         => $zona->turnos_array,
                                        'horariosPorDia' => $zona->horarios_por_dia,
                                    ]; @endphp
                                    <x-ui.table.row>
                                        <x-ui.table.cell class="font-medium" data-label="Zona">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                {{ $zona->nombre }}
                                                @unless($zona->activo)
                                                    <x-ui.badge variant="secondary">Inactiva</x-ui.badge>
                                                @endunless
                                                @unless($zona->geojson)
                                                    <x-ui.badge variant="outline">Sin área</x-ui.badge>
                                                @endunless
                                            </div>
                                        </x-ui.table.cell>
                                        <x-ui.table.cell data-label="Datos" class="text-muted-foreground">
                                            @if($zona->hectareas) {{ number_format($zona->hectareas, 2, ',', '.') }} ha @endif
                                            @if($zona->hectareas && $zona->barrios) &middot; @endif
                                            @if($zona->barrios) {{ $zona->barrios }} {{ $zona->barrios === 1 ? 'barrio' : 'barrios' }} @endif
                                            @unless($zona->hectareas || $zona->barrios) — @endunless
                                        </x-ui.table.cell>
                                        <x-ui.table.cell data-label="Turnos">
                                            @if($zona->turnos->isNotEmpty())
                                                <div class="flex gap-1 flex-wrap">
                                                    @foreach($zona->turnos as $turno)
                                                        <x-ui.badge variant="secondary">{{ $turno->turno }}</x-ui.badge>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted-foreground text-sm">Sin turno</span>
                                            @endif
                                        </x-ui.table.cell>
                                        <x-ui.table.cell actions>
                                            <x-ui.dropdown-menu align="end">
                                                <x-ui.dropdown-menu.trigger>
                                                    <x-ui.button variant="ghost" size="icon" class="size-7">
                                                        <x-lucide-ellipsis class="size-4" />
                                                    </x-ui.button>
                                                </x-ui.dropdown-menu.trigger>
                                                <x-ui.dropdown-menu.content>
                                                    <x-ui.dropdown-menu.item
                                                        @click="openEditZona({{ Js::from($zonaPayload) }}, {{ Js::from($tipo->nombre) }})"
                                                    >
                                                        <x-lucide-pencil class="size-4" />
                                                        Editar
                                                    </x-ui.dropdown-menu.item>
                                                    <x-ui.dropdown-menu.item
                                                        variant="{{ $zona->activo ? 'destructive' : 'default' }}"
                                                        :closeOnClick="false"
                                                        @click="confirmToggleZona({{ $zona->id }}, {{ Js::from($zona->nombre) }}, {{ $zona->activo ? 'true' : 'false' }}); open = false"
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
                                                        @click="confirmDeleteZona({{ $zona->id }}, {{ Js::from($zona->nombre) }}); open = false"
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
                    @endif
                </x-ui.card.content>

            </x-ui.card>
        @endforeach
    </div>
@endif

@if($tipos->hasPages())
    <div class="flex items-center justify-between px-1 pt-2 text-sm text-muted-foreground">
        <span class="flex-1">Página {{ $tipos->currentPage() }} de {{ $tipos->lastPage() }} · {{ number_format($tipos->total(), 0, ',', '.') }} servicios</span>
        <x-ui.pagination :paginator="$tipos" />
    </div>
@endif
