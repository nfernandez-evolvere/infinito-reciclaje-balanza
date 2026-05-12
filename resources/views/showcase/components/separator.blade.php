<x-layouts.showcase title="Separator — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Separator</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Línea divisoria semántica. Soporta orientación horizontal y vertical.</x-ui.typography>
    </div>

    {{-- Horizontal --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal (default)</x-ui.typography>
        <div class="space-y-4">
            <x-ui.typography as="muted">Contenido arriba del separador.</x-ui.typography>
            <x-ui.separator />
            <x-ui.typography as="muted">Contenido debajo del separador.</x-ui.typography>
        </div>
    </section>

    <x-ui.separator />

    {{-- Vertical --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Vertical</x-ui.typography>
        <div class="flex items-center gap-4 h-6">
            <span class="text-sm font-medium">Blog</span>
            <x-ui.separator orientation="vertical" />
            <span class="text-sm font-medium">Docs</span>
            <x-ui.separator orientation="vertical" />
            <span class="text-sm font-medium">Fuente</span>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto — perfil --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto</x-ui.typography>
        <div class="space-y-1 max-w-xs">
            <x-ui.typography as="h4">Radix Primitives</x-ui.typography>
            <x-ui.typography as="muted">Una librería open-source de componentes UI.</x-ui.typography>
            <x-ui.separator class="my-4" />
            <div class="flex h-5 items-center gap-4 text-sm">
                <span>Blog</span>
                <x-ui.separator orientation="vertical" />
                <span>Docs</span>
                <x-ui.separator orientation="vertical" />
                <span>Fuente</span>
            </div>
        </div>
    </section>

</div>
</x-layouts.showcase>
