@props(['zonas', 'tiposServicio', 'tiposVehiculo', 'filters' => []])

<x-ui.filter-sheet
    controlledBy="filterOpen"
    action="{{ route('admin.reportes.index') }}"
    resetUrl="{{ route('admin.reportes.index') }}"
>
    {{-- Período rápido --}}
    <div
        x-data="{
            setMes(offset) {
                const d = new Date();
                d.setMonth(d.getMonth() - offset);
                const y = d.getFullYear();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const last = new Date(y, d.getMonth() + 1, 0).getDate();
                $dispatch('set-desde', { date: `${y}-${m}-01` });
                $dispatch('set-hasta', { date: `${y}-${m}-${String(last).padStart(2, '0')}` });
            }
        }"
        class="space-y-1.5"
    >
        <x-ui.label>Período rápido</x-ui.label>
        <div class="grid grid-cols-2 gap-2">
            <x-ui.button type="button" variant="outline" size="sm" @click="setMes(0)">
                Mes actual
            </x-ui.button>
            <x-ui.button type="button" variant="outline" size="sm" @click="setMes(1)">
                Mes anterior
            </x-ui.button>
        </div>
    </div>

    <x-ui.form-field for="filter-desde">
        <x-ui.label for="filter-desde">Desde</x-ui.label>
        <x-ui.date-picker
            id="filter-desde"
            name="desde"
            placeholder="Seleccioná una fecha"
            :value="$filters['desde'] ?? ''"
            @set-desde.window="value = $event.detail.date"
        />
    </x-ui.form-field>

    <x-ui.form-field for="filter-hasta">
        <x-ui.label for="filter-hasta">Hasta</x-ui.label>
        <x-ui.date-picker
            id="filter-hasta"
            name="hasta"
            placeholder="Seleccioná una fecha"
            :value="$filters['hasta'] ?? ''"
            @set-hasta.window="value = $event.detail.date"
        />
    </x-ui.form-field>

    <x-ui.separator />

    {{-- Zona --}}
    <x-ui.form-field for="filter-zona">
        <x-ui.label for="filter-zona">Zona</x-ui.label>
        <x-ui.select name="zona_id" :value="$filters['zona_id'] ?? ''">
            <x-ui.select.trigger id="filter-zona">
                <x-ui.select.value placeholder="Todas las zonas" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todas las zonas</x-ui.select.item>
                @foreach($zonas as $zona)
                    <x-ui.select.item value="{{ $zona->id }}">{{ $zona->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    {{-- Tipo de servicio --}}
    <x-ui.form-field for="filter-servicio">
        <x-ui.label for="filter-servicio">Tipo de servicio</x-ui.label>
        <x-ui.select name="tipo_servicio_id" :value="$filters['tipo_servicio_id'] ?? ''">
            <x-ui.select.trigger id="filter-servicio">
                <x-ui.select.value placeholder="Todos los servicios" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos los servicios</x-ui.select.item>
                @foreach($tiposServicio as $ts)
                    <x-ui.select.item value="{{ $ts->id }}">{{ $ts->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    {{-- Tipo de vehículo --}}
    <x-ui.form-field for="filter-vehiculo">
        <x-ui.label for="filter-vehiculo">Tipo de vehículo</x-ui.label>
        <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
            <x-ui.select.trigger id="filter-vehiculo">
                <x-ui.select.value placeholder="Todos los tipos" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="">Todos los tipos</x-ui.select.item>
                @foreach($tiposVehiculo as $tv)
                    <x-ui.select.item value="{{ $tv->id }}">{{ $tv->nombre }}</x-ui.select.item>
                @endforeach
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

</x-ui.filter-sheet>
