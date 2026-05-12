<x-layouts.showcase title="Label — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Label</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Etiqueta accesible para campos de formulario. Soporta estados semánticos y se integra con form-field para heredar estado automáticamente.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.label for="demo-input">Nombre completo</x-ui.label>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="flex flex-col gap-2">
            <x-ui.label>Default — sin estado</x-ui.label>
            <x-ui.label state="destructive">Destructive — campo con error</x-ui.label>
            <x-ui.label state="success">Success — campo válido</x-ui.label>
            <x-ui.label state="warning">Warning — atención requerida</x-ui.label>
            <x-ui.label state="info">Info — contexto adicional</x-ui.label>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto con input --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto</x-ui.typography>
        <div class="space-y-3 max-w-sm">
            <x-ui.form-field>
                <x-ui.label for="email-ok">Email</x-ui.label>
                <x-ui.input id="email-ok" type="email" placeholder="nombre@ejemplo.com" />
            </x-ui.form-field>

            <x-ui.form-field state="destructive" message="Email inválido.">
                <x-ui.label state="destructive" for="email-err">Email</x-ui.label>
                <x-ui.input id="email-err" type="email" state="destructive" value="invalido" />
            </x-ui.form-field>
        </div>
    </section>

</div>
</x-layouts.showcase>
