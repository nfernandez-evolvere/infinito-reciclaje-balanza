<x-layouts.showcase title="Button — Showcase">
    <div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        <div>
            <x-ui.typography as="h1">Button</x-ui.typography>
            <x-ui.typography as="muted" class="mt-1 max-w-prose">
                Componente de acción principal. Dos ejes independientes:
                <x-ui.typography as="code">variant</x-ui.typography> (forma) y
                <x-ui.typography as="code">state</x-ui.typography> (color semántico).
            </x-ui.typography>
        </div>

        {{-- Variantes --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Variantes</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button variant="default">Default</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="outline">Outline</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
                <x-ui.button variant="link">Link</x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Estados semánticos (filled) --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Estados semánticos — filled</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button state="destructive">Destructive</x-ui.button>
                <x-ui.button state="success">Success</x-ui.button>
                <x-ui.button state="warning">Warning</x-ui.button>
                <x-ui.button state="info">Info</x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Estados semánticos × outline --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Estados semánticos — outline</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button variant="outline" state="destructive">Destructive</x-ui.button>
                <x-ui.button variant="outline" state="success">Success</x-ui.button>
                <x-ui.button variant="outline" state="warning">Warning</x-ui.button>
                <x-ui.button variant="outline" state="info">Info</x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Estados semánticos × ghost --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Estados semánticos — ghost</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button variant="ghost" state="destructive">Destructive</x-ui.button>
                <x-ui.button variant="ghost" state="success">Success</x-ui.button>
                <x-ui.button variant="ghost" state="warning">Warning</x-ui.button>
                <x-ui.button variant="ghost" state="info">Info</x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Tamaños --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Tamaños</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button size="sm">Small</x-ui.button>
                <x-ui.button size="md">Medium</x-ui.button>
                <x-ui.button size="lg">Large</x-ui.button>
                <x-ui.button size="icon" aria-label="Acción">
                    <x-lucide-plus class="size-4" />
                </x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Con ícono --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Con ícono</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button>
                    <x-lucide-mail class="size-4" />
                    Enviar email
                </x-ui.button>
                <x-ui.button variant="outline">
                    <x-lucide-download class="size-4" />
                    Descargar
                </x-ui.button>
                <x-ui.button variant="link" state="success">
                    <x-lucide-download class="size-4" />
                    Descargar
                </x-ui.button>
            </div>
        </section>

        <x-ui.separator />

        {{-- Estados UI --}}
        <section class="space-y-4">
            <x-ui.typography as="section-label">Estados UI</x-ui.typography>
            <div class="flex flex-wrap gap-3 items-center">
                <x-ui.button disabled>Disabled</x-ui.button>
                <x-ui.button disabled variant="outline">Disabled outline</x-ui.button>
                <x-ui.button disabled>
                    <x-ui.spinner size="sm" class="text-primary-foreground opacity-70" />
                    Cargando...
                </x-ui.button>
            </div>
        </section>

    </div>
</x-layouts.showcase>