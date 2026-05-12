<x-layouts.showcase title="Button Group — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Button Group</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Agrupa botones fusionando sus bordes intermedios. Sin JS.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal</x-ui.typography>
        <div class="flex flex-wrap gap-4">
            <x-ui.button-group>
                <x-ui.button variant="outline">Anterior</x-ui.button>
                <x-ui.button variant="outline">Siguiente</x-ui.button>
            </x-ui.button-group>

            <x-ui.button-group>
                <x-ui.button variant="outline">Día</x-ui.button>
                <x-ui.button variant="outline">Semana</x-ui.button>
                <x-ui.button variant="outline">Mes</x-ui.button>
            </x-ui.button-group>

            <x-ui.button-group>
                <x-ui.button variant="outline" size="sm">
                    <x-lucide-chevron-left />
                </x-ui.button>
                <x-ui.button variant="outline" size="sm">1</x-ui.button>
                <x-ui.button variant="outline" size="sm">2</x-ui.button>
                <x-ui.button variant="outline" size="sm">3</x-ui.button>
                <x-ui.button variant="outline" size="sm">
                    <x-lucide-chevron-right />
                </x-ui.button>
            </x-ui.button-group>
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Vertical</x-ui.typography>
        <x-ui.button-group orientation="vertical" class="w-32">
            <x-ui.button variant="outline">Arriba</x-ui.button>
            <x-ui.button variant="outline">Centro</x-ui.button>
            <x-ui.button variant="outline">Abajo</x-ui.button>
        </x-ui.button-group>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con variantes</x-ui.typography>
        <div class="flex flex-wrap gap-4">
            <x-ui.button-group>
                <x-ui.button>Publicar</x-ui.button>
                <x-ui.button>
                    <x-lucide-chevron-down />
                </x-ui.button>
            </x-ui.button-group>

            <x-ui.button-group>
                <x-ui.button variant="outline">Copiar</x-ui.button>
                <x-ui.button variant="outline">Cortar</x-ui.button>
                <x-ui.button variant="outline">Pegar</x-ui.button>
            </x-ui.button-group>
        </div>
    </section>

</div>
</x-layouts.showcase>
