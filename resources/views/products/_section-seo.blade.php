<div class="space-y-4">
    <div class="space-y-2">
        <x-ui.label element="p">Visibilidad</x-ui.label>
        <x-ui.radio-group value="public" name="visibility" class="space-y-2">
            @foreach([
                ['public',  'Público',            'Visible en la tienda y buscadores'],
                ['private', 'Solo con enlace',    'Accesible solo con la URL directa'],
                ['hidden',  'Oculto',             'No aparece en ningún listado'],
            ] as [$val, $label, $desc])
            <label class="flex items-start gap-3 cursor-pointer" @click="value = '{{ $val }}'">
                <x-ui.radio-group.item value="{{ $val }}" class="mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-foreground">{{ $label }}</p>
                    <p class="text-xs text-muted-foreground">{{ $desc }}</p>
                </div>
            </label>
            @endforeach
        </x-ui.radio-group>
    </div>

    <x-ui.separator />

    <x-ui.form-field for="seo-slug">
        <x-ui.label for="seo-slug">URL del producto</x-ui.label>
        <x-ui.input-group>
            <x-ui.input-group.addon>
                <span class="text-xs text-muted-foreground">tienda.com/</span>
            </x-ui.input-group.addon>
            <x-ui.input-group.input id="seo-slug" value="remera-premium-algodon" />
        </x-ui.input-group>
    </x-ui.form-field>

    <x-ui.form-field for="seo-title">
        <x-ui.label for="seo-title">Meta título</x-ui.label>
        <x-ui.input id="seo-title" value="Remera Premium Algodón – Tu Tienda" />
    </x-ui.form-field>

    <x-ui.form-field for="seo-desc">
        <x-ui.label for="seo-desc">Meta descripción</x-ui.label>
        <x-ui.textarea id="seo-desc" rows="2" placeholder="Descripción para buscadores...">Remera de algodón peinado 100% disponible en múltiples tallas y colores. Envío gratis.</x-ui.textarea>
    </x-ui.form-field>
</div>
