<x-layouts.showcase title="Kbd — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Kbd</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Elemento semántico <x-ui.typography as="code">&lt;kbd&gt;</x-ui.typography> para mostrar atajos de teclado y combinaciones de teclas.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Teclas individuales</x-ui.typography>
        <div class="flex flex-wrap gap-2">
            @foreach(['⌘', '⇧', '⌥', '⌃', 'Enter', 'Esc', 'Tab', '↑', '↓', '←', '→', 'Space'] as $key)
                <x-ui.kbd>{{ $key }}</x-ui.kbd>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Combinaciones</x-ui.typography>
        <div class="flex flex-wrap items-center gap-4">
            <span class="flex items-center gap-1 text-sm text-muted-foreground">
                Guardar <x-ui.kbd>⌘</x-ui.kbd><x-ui.kbd>S</x-ui.kbd>
            </span>
            <span class="flex items-center gap-1 text-sm text-muted-foreground">
                Buscar <x-ui.kbd>⌘</x-ui.kbd><x-ui.kbd>K</x-ui.kbd>
            </span>
            <span class="flex items-center gap-1 text-sm text-muted-foreground">
                Deshacer <x-ui.kbd>⌘</x-ui.kbd><x-ui.kbd>Z</x-ui.kbd>
            </span>
            <span class="flex items-center gap-1 text-sm text-muted-foreground">
                Copiar <x-ui.kbd>⌘</x-ui.kbd><x-ui.kbd>C</x-ui.kbd>
            </span>
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto de UI</x-ui.typography>
        <div class="rounded-lg border border-border divide-y divide-border max-w-sm">
            @foreach([
                ['Buscar', '⌘K'],
                ['Nuevo archivo', '⌘N'],
                ['Cerrar ventana', '⌘W'],
                ['Preferencias', '⌘,'],
            ] as [$action, $shortcut])
                <div class="flex items-center justify-between px-4 py-2.5 text-sm">
                    <span>{{ $action }}</span>
                    <x-ui.kbd>{{ $shortcut }}</x-ui.kbd>
                </div>
            @endforeach
        </div>
    </section>

</div>
</x-layouts.showcase>
