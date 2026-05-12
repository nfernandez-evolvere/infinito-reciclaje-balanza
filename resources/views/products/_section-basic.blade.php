<div class="space-y-4">
    <x-ui.form-field for="p-name">
        <x-ui.label for="p-name">Nombre del producto</x-ui.label>
        <x-ui.input id="p-name" name="name" placeholder="Ej: Remera Premium Algodón" value="Remera Premium Algodón" />
    </x-ui.form-field>
    <x-ui.form-field for="p-desc">
        <x-ui.label for="p-desc">Descripción</x-ui.label>
        <x-ui.textarea id="p-desc" name="description" rows="3" placeholder="Describí el producto...">Remera de algodón peinado 100%, corte regular, disponible en varios colores.</x-ui.textarea>
    </x-ui.form-field>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.form-field>
            <x-ui.label>Categoría</x-ui.label>
            <x-ui.combobox
                name="category"
                placeholder="Seleccioná categoría"
                :options="['remeras' => 'Remeras', 'pantalones' => 'Pantalones', 'accesorios' => 'Accesorios', 'calzado' => 'Calzado']"
            />
        </x-ui.form-field>
        <x-ui.form-field>
            <x-ui.label>Marca</x-ui.label>
            <x-ui.native-select name="brand">
                <option value="">Seleccioná marca</option>
                <option value="propio" selected>Marca propia</option>
                <option value="partner">Partner</option>
            </x-ui.native-select>
        </x-ui.form-field>
    </div>
</div>
