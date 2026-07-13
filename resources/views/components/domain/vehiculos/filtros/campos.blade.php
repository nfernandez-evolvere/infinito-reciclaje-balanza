@props(['filters', 'tiposVehiculo', 'tab' => null])

{{--
    Campos del filtro de vehículos. Se reutilizan en el sheet mobile y en el panel
    inline (md+). Cada campo es un elemento raíz. Sin id/for fijos: el markup se
    renderiza dos veces (sheet + panel). El hidden `tab` preserva la pestaña activa
    al aplicar (el form GET descarta el query string del action).
--}}

@if($tab)
    <input type="hidden" name="tab" value="{{ $tab }}">
@endif

<x-ui.form-field>
    <x-ui.label>Patente</x-ui.label>
    <x-ui.input
        name="patente"
        type="search"
        placeholder="Buscar por patente…"
        :value="$filters['patente'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>N.° interno</x-ui.label>
    <x-ui.input
        name="numero_interno"
        type="search"
        placeholder="Buscar por número interno…"
        :value="$filters['numero_interno'] ?? ''"
    />
</x-ui.form-field>

<x-ui.form-field>
    <x-ui.label>Tipo de vehículo</x-ui.label>
    <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Todos" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            <x-ui.select.item value="">Todos</x-ui.select.item>
            @foreach($tiposVehiculo as $tipo)
                <x-ui.select.item value="{{ $tipo->id }}">{{ $tipo->nombre }}</x-ui.select.item>
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
