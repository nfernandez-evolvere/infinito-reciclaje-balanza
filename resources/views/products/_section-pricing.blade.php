<div
    x-data="{ margin: 40, price: 2500, cost: 1500, showStock: true }"
    class="space-y-4"
>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.form-field>
            <x-ui.label>Precio de venta</x-ui.label>
            <x-ui.input-group>
                <x-ui.input-group.addon>
                    <span class="text-sm text-muted-foreground font-medium">$</span>
                </x-ui.input-group.addon>
                <x-ui.input-group.input type="number" value="2500" placeholder="0.00" />
            </x-ui.input-group>
        </x-ui.form-field>
        <x-ui.form-field>
            <x-ui.label>Costo</x-ui.label>
            <x-ui.input-group>
                <x-ui.input-group.addon>
                    <span class="text-sm text-muted-foreground font-medium">$</span>
                </x-ui.input-group.addon>
                <x-ui.input-group.input type="number" value="1500" placeholder="0.00" />
            </x-ui.input-group>
        </x-ui.form-field>
    </div>

    {{-- Margen --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <x-ui.label element="p">Margen de ganancia</x-ui.label>
            <span class="text-sm font-semibold text-foreground tabular-nums" x-text="margin + '%'">40%</span>
        </div>
        <x-ui.slider :values="[40]" :min="0" :max="100" :step="1" name="margin" />
    </div>

    <x-ui.separator />

    {{-- Stock --}}
    <div class="flex items-center justify-between gap-3">
        <div class="space-y-1">
            <x-ui.label element="p">Gestionar stock</x-ui.label>
            <x-ui.typography as="muted" class="text-xs">Controlar inventario de este producto</x-ui.typography>
        </div>
        <x-ui.switch :checked="true" @click="showStock = !showStock" />
    </div>

    <div x-show="showStock" class="grid grid-cols-2 gap-4">
        <x-ui.form-field for="stock-init">
            <x-ui.label for="stock-init">Stock inicial</x-ui.label>
            <x-ui.input id="stock-init" type="number" value="50" />
        </x-ui.form-field>
        <x-ui.form-field for="stock-min">
            <x-ui.label for="stock-min">Stock mínimo</x-ui.label>
            <x-ui.input id="stock-min" type="number" value="5" />
        </x-ui.form-field>
    </div>
</div>
