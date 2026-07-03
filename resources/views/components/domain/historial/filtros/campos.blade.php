@props([
    'filtros',
    'operarios',
    'zonas'         => collect(),
    'tiposServicio' => collect(),
    'tiposVehiculo' => collect(),
    'sortDirection' => 'desc',
])

{{--
    Campos del filtro de pesajes. Se reutilizan tal cual en el sheet mobile
    (<x-ui.filter-sheet>, stack vertical) y en el panel inline (<x-ui.filter-panel>,
    grilla). Cada campo es un elemento raíz para que herede el layout del contenedor.
--}}

<x-ui.form-field class="col-span-2">
    <x-ui.label>Período</x-ui.label>
    <x-ui.date-range-picker
        startName="desde"
        endName="hasta"
        :start="$filtros['desde']"
        :end="$filtros['hasta']"
        placeholder="Todas las fechas"
    />
</x-ui.form-field>

<div x-data="historialFiltroPatente({ value: '{{ $filtros['patente'] ?? '' }}', url: '{{ route('vehiculos.activos') }}' })">
    <x-ui.form-field>
        <x-ui.label>Patente</x-ui.label>
        <div class="relative">
            <x-ui.input
                type="text"
                name="patente"
                x-model="query"
                @focus="cargar()"
                @blur="setTimeout(() => showSugg = false, 150)"
                placeholder="ABC 123"
                autocomplete="off"
            />
            <div
                x-show="showSugg && matches.length > 0"
                x-cloak
                class="absolute left-0 right-0 top-full mt-1 bg-popover border border-border rounded-lg shadow-md overflow-hidden z-30 max-h-56 overflow-y-auto"
            >
                <template x-for="v in matches" :key="v.id">
                    <div
                        class="px-3 py-2 cursor-pointer text-sm hover:bg-accent transition-colors"
                        @mousedown.prevent="seleccionar(v.patente)"
                    >
                        <span class="font-medium" x-text="v.patente"></span>
                        <span class="text-muted-foreground text-xs" x-text="' · int. ' + v.interno"></span>
                    </div>
                </template>
            </div>
        </div>
    </x-ui.form-field>
</div>

<x-ui.form-field>
    <x-ui.label>Estado</x-ui.label>
    <x-ui.select name="estado" value="{{ $filtros['estado'] ?? '' }}">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos</x-ui.select.item>
            <x-ui.select.item value="Activos">Activos</x-ui.select.item>
            <x-ui.select.item value="Cancelado">Cancelados</x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Operario</x-ui.label>
    <x-ui.select name="operario_id" value="{{ $filtros['operario_id'] ?? '' }}">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos</x-ui.select.item>
            @foreach($operarios as $op)
                <x-ui.select.item value="{{ $op->id }}">{{ $op->name }}</x-ui.select.item>
            @endforeach
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>

@if($zonas->isNotEmpty())
    <x-ui.form-field>
        <x-ui.label>Origen</x-ui.label>
        <x-ui.select name="zona_id" value="{{ $filtros['zona_id'] ?? '' }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($zonas as $zona)
                    <x-ui.select.item value="{{ $zona->id }}">{{ $zona->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
@endif

@if($tiposServicio->isNotEmpty())
    <x-ui.form-field>
        <x-ui.label>Servicio</x-ui.label>
        <x-ui.select name="tipo_servicio_id" value="{{ $filtros['tipo_servicio_id'] ?? '' }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($tiposServicio as $ts)
                    <x-ui.select.item value="{{ $ts->id }}">{{ $ts->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
@endif

@if($tiposVehiculo->isNotEmpty())
    <x-ui.form-field>
        <x-ui.label>Tipo de vehículo</x-ui.label>
        <x-ui.select name="tipo_vehiculo_id" value="{{ $filtros['tipo_vehiculo_id'] ?? '' }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                @foreach($tiposVehiculo as $tv)
                    <x-ui.select.item value="{{ $tv->id }}">{{ $tv->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
@endif

@if($zonas->isNotEmpty())
    @php
        $mostrar = !empty($filtros['solo_alerta'])
            ? 'alerta'
            : (!empty($filtros['solo_editados']) ? 'editados' : '');
    @endphp
    <x-ui.form-field>
        <x-ui.label>Mostrar</x-ui.label>
        <x-ui.select name="mostrar" value="{{ $mostrar }}">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Todos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos</x-ui.select.item>
                <x-ui.select.item value="alerta">Con alerta</x-ui.select.item>
                <x-ui.select.item value="editados">Editados</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
@endif

<x-ui.form-field>
    <x-ui.label>Orden de fecha</x-ui.label>
    <x-ui.select name="direction" value="{{ $sortDirection }}">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Seleccionar" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="desc">
                <div class="flex items-center gap-1.5">
                    <x-lucide-arrow-down class="size-3.5" />
                    Más reciente primero
                </div>
            </x-ui.select.item>
            <x-ui.select.item value="asc">
                <div class="flex items-center gap-1.5">
                    <x-lucide-arrow-up class="size-3.5" />
                    Más antiguo primero
                </div>
            </x-ui.select.item>
        </x-ui.select.content>
    </x-ui.select>
</x-ui.form-field>
