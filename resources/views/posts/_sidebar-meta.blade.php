{{-- Partial: metadatos del post. Usado en la columna lateral (desktop) y en el Sheet (mobile). --}}
<div class="space-y-4">

    {{-- Estado --}}
    <x-ui.form-field>
        <x-ui.label>Estado</x-ui.label>
        <x-ui.select name="status" value="draft">
            <x-ui.select.trigger>
                <x-ui.select.value placeholder="Seleccioná estado" />
            </x-ui.select.trigger>
            <x-ui.select.content>
                <x-ui.select.item value="draft">Borrador</x-ui.select.item>
                <x-ui.select.item value="review">En revisión</x-ui.select.item>
                <x-ui.select.item value="published">Publicado</x-ui.select.item>
            </x-ui.select.content>
        </x-ui.select>
    </x-ui.form-field>

    {{-- Visibilidad --}}
    <div class="flex items-center justify-between gap-3">
        <div class="space-y-1">
            <x-ui.label element="p">Visibilidad pública</x-ui.label>
            <x-ui.typography as="muted" class="text-xs">Accesible sin login</x-ui.typography>
        </div>
        <x-ui.switch name="public" :checked="true" />
    </div>

    <x-ui.separator />

    {{-- Fecha de publicación --}}
    <x-ui.form-field>
        <x-ui.label>Fecha de publicación</x-ui.label>
        <x-ui.date-picker name="published_at" value="2024-07-15" />
    </x-ui.form-field>

    {{-- Categorías --}}
    <x-ui.form-field>
        <x-ui.label>Categorías</x-ui.label>
        <x-ui.combobox
            name="category"
            placeholder="Seleccioná una categoría"
            :options="[
                'tech'    => 'Tecnología',
                'design'  => 'Diseño',
                'product' => 'Producto',
                'ux'      => 'UX / UI',
                'dev'     => 'Desarrollo',
            ]"
        />
    </x-ui.form-field>

    <x-ui.separator />

    {{-- SEO --}}
    <x-ui.accordion type="single" :collapsible="true">
        <x-ui.accordion.item value="seo">
            <x-ui.accordion.trigger class="text-sm font-medium no-underline hover:no-underline">
                SEO y visibilidad
            </x-ui.accordion.trigger>
            <x-ui.accordion.content>
                <div class="space-y-3 pt-1">
                    <x-ui.form-field for="meta-title">
                        <x-ui.label for="meta-title" class="text-xs">Meta título</x-ui.label>
                        <x-ui.input id="meta-title" size="sm" placeholder="Título para buscadores" value="Introducción al diseño de sistemas" />
                    </x-ui.form-field>
                    <x-ui.form-field for="meta-desc">
                        <x-ui.label for="meta-desc" class="text-xs">Meta descripción</x-ui.label>
                        <x-ui.textarea id="meta-desc" rows="2" class="text-xs" placeholder="Descripción breve...">Aprende cómo construir un design system escalable con shadcn/ui y Laravel Blade.</x-ui.textarea>
                    </x-ui.form-field>
                    <x-ui.form-field for="meta-slug">
                        <x-ui.label for="meta-slug" class="text-xs">URL slug</x-ui.label>
                        <x-ui.input-group size="sm">
                            <x-ui.input-group.addon>
                                <span class="text-xs text-muted-foreground">blog/</span>
                            </x-ui.input-group.addon>
                            <x-ui.input-group.input value="introduccion-design-system" class="text-xs" />
                        </x-ui.input-group>
                    </x-ui.form-field>
                </div>
            </x-ui.accordion.content>
        </x-ui.accordion.item>
    </x-ui.accordion>

</div>
