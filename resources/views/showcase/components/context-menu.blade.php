<x-layouts.showcase title="Context Menu — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Context Menu</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Menú activado por clic derecho. Se posiciona en las coordenadas exactas del cursor con flip automático si no hay espacio.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico — click derecho en el área</x-ui.typography>
        <x-ui.context-menu>
            <x-ui.context-menu.trigger>
                <div class="flex h-40 items-center justify-center rounded-lg border border-dashed border-border text-sm text-muted-foreground select-none">
                    Click derecho aquí
                </div>
            </x-ui.context-menu.trigger>
            <x-ui.context-menu.content>
                <x-ui.dropdown-menu.item><x-lucide-eye />Ver</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item><x-lucide-copy />Copiar</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item><x-lucide-external-link />Abrir en pestaña</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive"><x-lucide-x />Eliminar</x-ui.dropdown-menu.item>
            </x-ui.context-menu.content>
        </x-ui.context-menu>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con labels, grupos y checkbox</x-ui.typography>
        <x-ui.context-menu>
            <x-ui.context-menu.trigger>
                <div class="flex h-40 items-center justify-center rounded-lg bg-muted/30 border border-border text-sm text-muted-foreground select-none">
                    Click derecho para ver menú completo
                </div>
            </x-ui.context-menu.trigger>
            <x-ui.context-menu.content class="w-56">
                <x-ui.dropdown-menu.label>Panel de control</x-ui.dropdown-menu.label>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.group>
                    <x-ui.dropdown-menu.item><x-lucide-eye />Perfil</x-ui.dropdown-menu.item>
                    <x-ui.dropdown-menu.item><x-lucide-moon />Configuración</x-ui.dropdown-menu.item>
                </x-ui.dropdown-menu.group>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.checkbox-item :checked="true">Barra de estado</x-ui.dropdown-menu.checkbox-item>
                <x-ui.dropdown-menu.checkbox-item :checked="false">Panel lateral</x-ui.dropdown-menu.checkbox-item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive"><x-lucide-x />Salir</x-ui.dropdown-menu.item>
            </x-ui.context-menu.content>
        </x-ui.context-menu>
    </section>

</div>
</x-layouts.showcase>
