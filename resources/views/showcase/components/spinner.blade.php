<x-layouts.showcase title="Spinner — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Spinner</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Indicador de carga circular. Soporta tamaños sm/md/lg y hereda el color del texto del padre o acepta color explícito.</x-ui.typography>
    </div>

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-end">
            <div class="flex flex-col items-center gap-2">
                <x-ui.spinner size="sm" />
                <span class="text-xs text-muted-foreground">sm</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <x-ui.spinner size="md" />
                <span class="text-xs text-muted-foreground">md</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <x-ui.spinner size="lg" />
                <span class="text-xs text-muted-foreground">lg</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Colores --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Colores (hereda text-color)</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <x-ui.spinner class="text-foreground" />
            <x-ui.spinner class="text-muted-foreground" />
            <x-ui.spinner class="text-primary" />
            <x-ui.spinner class="text-destructive" />
            <x-ui.spinner class="text-success" />
            <x-ui.spinner class="text-warning" />
            <x-ui.spinner class="text-info" />
        </div>
    </section>

    <x-ui.separator />

    {{-- En botones --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En botones</x-ui.typography>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.button disabled>
                <x-ui.spinner size="sm" class="text-primary-foreground opacity-70" />
                Guardando...
            </x-ui.button>
            <x-ui.button variant="outline" disabled>
                <x-ui.spinner size="sm" />
                Cargando...
            </x-ui.button>
            <x-ui.button state="success" disabled>
                <x-ui.spinner size="sm" class="text-success-foreground opacity-70" />
                Procesando...
            </x-ui.button>
        </div>
    </section>

    <x-ui.separator />

    {{-- Loading overlay --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Overlay de carga</x-ui.typography>
        <div class="relative max-w-sm">
            <x-ui.card>
                <x-ui.card.content class="pt-6 h-32 flex items-center justify-center">
                    <x-ui.spinner size="lg" class="text-muted-foreground" />
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </section>

</div>
</x-layouts.showcase>
