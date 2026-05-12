<x-layouts.showcase title="Native Select — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Native Select</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Elemento <x-ui.typography as="code">&lt;select&gt;</x-ui.typography> nativo estilizado. Más accesible en mobile, sin Alpine.js.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-xs">
            @foreach(['sm', 'md', 'lg'] as $size)
                <x-ui.native-select :size="$size">
                    <option value="">Seleccioná una opción</option>
                    <option value="1">Opción 1</option>
                    <option value="2">Opción 2</option>
                    <option value="3">Opción 3</option>
                </x-ui.native-select>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-xs">
            <x-ui.native-select state="destructive">
                <option>Estado: destructive</option>
            </x-ui.native-select>
            <x-ui.native-select state="success">
                <option>Estado: success</option>
            </x-ui.native-select>
            <x-ui.native-select state="warning">
                <option>Estado: warning</option>
            </x-ui.native-select>
            <x-ui.native-select :disabled="true">
                <option>Deshabilitado</option>
            </x-ui.native-select>
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con optgroup</x-ui.typography>
        <x-ui.native-select class="max-w-xs">
            <optgroup label="Frutas">
                <option>Manzana</option>
                <option>Naranja</option>
                <option>Pera</option>
            </optgroup>
            <optgroup label="Verduras">
                <option>Zanahoria</option>
                <option>Lechuga</option>
                <option>Tomate</option>
            </optgroup>
        </x-ui.native-select>
    </section>

</div>
</x-layouts.showcase>
