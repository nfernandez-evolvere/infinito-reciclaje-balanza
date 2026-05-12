<x-layouts.showcase title="Textarea — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Textarea</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Campo de texto multilínea. Misma API de tamaños y estados semánticos que Input.</x-ui.typography>
    </div>

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="space-y-3">
            <x-ui.form-field>
                <x-ui.label>Small (sm)</x-ui.label>
                <x-ui.textarea size="sm" placeholder="Texto en sm..." />
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Medium (md) — default</x-ui.label>
                <x-ui.textarea placeholder="Texto en md..." />
            </x-ui.form-field>
            <x-ui.form-field>
                <x-ui.label>Large (lg)</x-ui.label>
                <x-ui.textarea size="lg" placeholder="Texto en lg..." />
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="space-y-4">
            <x-ui.form-field state="destructive" message="El mensaje no puede estar vacío.">
                <x-ui.label state="destructive">Descripción</x-ui.label>
                <x-ui.textarea state="destructive" placeholder="Escribí aquí..." />
            </x-ui.form-field>

            <x-ui.form-field state="success" message="Listo para enviar.">
                <x-ui.label state="success">Descripción</x-ui.label>
                <x-ui.textarea state="success">Este texto es válido y cumple todos los requisitos.</x-ui.textarea>
            </x-ui.form-field>

            <x-ui.form-field state="warning" message="Estás llegando al límite de caracteres (200/250).">
                <x-ui.label state="warning">Descripción</x-ui.label>
                <x-ui.textarea state="warning">Texto cercano al límite...</x-ui.textarea>
            </x-ui.form-field>

            <x-ui.form-field state="info" message="Máximo 250 caracteres.">
                <x-ui.label state="info">Descripción</x-ui.label>
                <x-ui.textarea state="info" placeholder="Describí tu proyecto..." />
            </x-ui.form-field>
        </div>
    </section>

    <x-ui.separator />

    {{-- Disabled --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Deshabilitado</x-ui.typography>
        <x-ui.form-field>
            <x-ui.label>Campo deshabilitado</x-ui.label>
            <x-ui.textarea disabled>Contenido no editable.</x-ui.textarea>
        </x-ui.form-field>
    </section>

</div>
</x-layouts.showcase>
