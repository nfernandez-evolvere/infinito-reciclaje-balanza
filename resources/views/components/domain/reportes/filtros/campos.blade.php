@props(['zonas', 'tiposServicio', 'tiposVehiculo', 'filters' => []])

{{--
    Campos del filtro/generador de reportes. Se reutilizan en el sheet mobile y en el
    panel inline (md+). Cada campo es un elemento raíz. Sin id/for fijos: el markup se
    renderiza dos veces (sheet + panel). El hidden `tab` mantiene la pestaña Generar.

    - Período: un único date-range-picker (desde/hasta).
    - Período rápido: un select que setea el rango (dispara `set-range`, que escuchan
      el date-range-picker y su calendario).
--}}

<input type="hidden" name="tab" value="generar">

<x-ui.form-field class="col-span-2">
    <x-ui.label>Período</x-ui.label>
    <x-ui.date-range-picker
        startName="desde"
        endName="hasta"
        :start="$filters['desde'] ?? null"
        :end="$filters['hasta'] ?? null"
        placeholder="Elegí un rango de fechas"
    />
</x-ui.form-field>

{{-- Período rápido: setea el rango vía el evento set-range --}}
<div
    x-data="{
        setPeriodo(v) {
            if (! v) return;
            const offset = v === 'mes_anterior' ? 1 : 0;
            const d = new Date();
            d.setDate(1);
            d.setMonth(d.getMonth() - offset);
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const last = new Date(y, d.getMonth() + 1, 0).getDate();
            this.$dispatch('set-range', {
                start: `${y}-${m}-01`,
                end:   `${y}-${m}-${String(last).padStart(2, '0')}`,
            });
        }
    }"
    @select-change="setPeriodo($event.detail.value)"
>
    <x-ui.form-field>
        <x-ui.label>Período rápido</x-ui.label>
        <x-ui.select>
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Elegí un atajo" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="mes_actual">Mes actual</x-ui.select.item>
                <x-ui.select.item value="mes_anterior">Mes anterior</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>
</div>

<x-ui.form-field>
    <x-ui.label>Zona</x-ui.label>
    <x-ui.select name="zona_id" :value="$filters['zona_id'] ?? ''">
        <x-ui.select.trigger>
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

<x-ui.form-field>
    <x-ui.label>Tipo de servicio</x-ui.label>
    <x-ui.select name="tipo_servicio_id" :value="$filters['tipo_servicio_id'] ?? ''">
        <x-ui.select.trigger>
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

<x-ui.form-field>
    <x-ui.label>Tipo de vehículo</x-ui.label>
    <x-ui.select name="tipo_vehiculo_id" :value="$filters['tipo_vehiculo_id'] ?? ''">
        <x-ui.select.trigger>
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
