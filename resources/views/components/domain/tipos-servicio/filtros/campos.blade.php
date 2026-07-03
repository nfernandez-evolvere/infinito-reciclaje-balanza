@props(['filters', 'tiposVehiculo'])

{{--
    Campos del filtro de tipos de servicio. Se reutilizan en el sheet mobile y en el
    panel inline (md+). Cada campo es un elemento raíz. Sin id/for fijos: el markup se
    renderiza dos veces (sheet + panel).
--}}

<x-ui.form-field>
    <x-ui.label>Nombre</x-ui.label>
    <x-ui.input
        name="nombre"
        type="search"
        placeholder="Buscar por nombre…"
        :value="$filters['nombre'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Vehículo habitual</x-ui.label>
    <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
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

<x-ui.form-field>
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
</x-ui.form-field>
