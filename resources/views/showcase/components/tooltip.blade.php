<x-layouts.showcase title="Tooltip — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Tooltip</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Información contextual en hover o focus. El ícono o elemento disparador debe estar dentro del slot; el texto del tooltip va en el prop <x-ui.typography as="code">content</x-ui.typography>.</x-ui.typography>
    </div>

    {{-- Lados --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Lados</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center justify-center py-8">
            <x-ui.tooltip content="Tooltip arriba" side="top">
                <x-ui.button variant="outline" size="sm">Top</x-ui.button>
            </x-ui.tooltip>
            <x-ui.tooltip content="Tooltip abajo" side="bottom">
                <x-ui.button variant="outline" size="sm">Bottom</x-ui.button>
            </x-ui.tooltip>
            <x-ui.tooltip content="Tooltip izquierda" side="left">
                <x-ui.button variant="outline" size="sm">Left</x-ui.button>
            </x-ui.tooltip>
            <x-ui.tooltip content="Tooltip derecha" side="right">
                <x-ui.button variant="outline" size="sm">Right</x-ui.button>
            </x-ui.tooltip>
        </div>
    </section>

    <x-ui.separator />

    {{-- Con ícono --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En íconos de acción</x-ui.typography>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.tooltip content="Editar">
                <x-ui.button size="icon" variant="ghost" aria-label="Editar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                </x-ui.button>
            </x-ui.tooltip>
            <x-ui.tooltip content="Eliminar" side="bottom">
                <x-ui.button size="icon" variant="ghost" state="destructive" aria-label="Eliminar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                </x-ui.button>
            </x-ui.tooltip>
            <x-ui.tooltip content="Copiar enlace" side="right">
                <x-ui.button size="icon" variant="outline" aria-label="Copiar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                </x-ui.button>
            </x-ui.tooltip>
        </div>
    </section>

    <x-ui.separator />

    {{-- En texto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En texto con ayuda</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Tu plan actual incluye
            <x-ui.tooltip content="Hasta 5 proyectos activos simultáneos">
                <span class="underline underline-offset-2 decoration-dashed cursor-help font-medium text-foreground">5 proyectos</span>
            </x-ui.tooltip>
            y
            <x-ui.tooltip content="Colaboradores con acceso de edición" side="top">
                <span class="underline underline-offset-2 decoration-dashed cursor-help font-medium text-foreground">3 colaboradores</span>
            </x-ui.tooltip>.
        </x-ui.typography>
    </section>

</div>
</x-layouts.showcase>
