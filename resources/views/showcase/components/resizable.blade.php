<x-layouts.showcase title="Resizable — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Resizable</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Paneles redimensionables con drag. Soporta orientación horizontal y vertical, con o sin grip visual.</x-ui.typography>
    </div>

    {{-- Horizontal --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal</x-ui.typography>
        <div class="h-48 rounded-lg border border-border overflow-hidden">
            <x-ui.resizable.panel-group direction="horizontal">
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center p-4">
                        <span class="text-sm text-muted-foreground">Panel izquierdo</span>
                    </div>
                </x-ui.resizable.panel>
                <x-ui.resizable.handle :with-handle="true" :index="0" />
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center p-4">
                        <span class="text-sm text-muted-foreground">Panel derecho</span>
                    </div>
                </x-ui.resizable.panel>
            </x-ui.resizable.panel-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- Tres paneles --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tres paneles</x-ui.typography>
        <div class="h-48 rounded-lg border border-border overflow-hidden">
            <x-ui.resizable.panel-group direction="horizontal">
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center bg-muted/20">
                        <span class="text-sm text-muted-foreground">A</span>
                    </div>
                </x-ui.resizable.panel>
                <x-ui.resizable.handle :with-handle="true" :index="0" />
                <x-ui.resizable.panel :default-size="2">
                    <div class="flex h-full items-center justify-center">
                        <span class="text-sm text-muted-foreground">B (default 2x)</span>
                    </div>
                </x-ui.resizable.panel>
                <x-ui.resizable.handle :with-handle="true" :index="1" />
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center bg-muted/20">
                        <span class="text-sm text-muted-foreground">C</span>
                    </div>
                </x-ui.resizable.panel>
            </x-ui.resizable.panel-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- Vertical --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Vertical</x-ui.typography>
        <div class="h-64 rounded-lg border border-border overflow-hidden">
            <x-ui.resizable.panel-group direction="vertical">
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center p-4">
                        <span class="text-sm text-muted-foreground">Panel superior</span>
                    </div>
                </x-ui.resizable.panel>
                <x-ui.resizable.handle :with-handle="true" :index="0" />
                <x-ui.resizable.panel :default-size="1">
                    <div class="flex h-full items-center justify-center p-4">
                        <span class="text-sm text-muted-foreground">Panel inferior</span>
                    </div>
                </x-ui.resizable.panel>
            </x-ui.resizable.panel-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- Sin grip --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sin grip visible</x-ui.typography>
        <div class="h-48 rounded-lg border border-border overflow-hidden">
            <x-ui.resizable.panel-group direction="horizontal">
                <x-ui.resizable.panel :default-size="1">
                    <div class="h-full bg-muted/30 flex items-center justify-center">
                        <span class="text-sm text-muted-foreground">Izquierda</span>
                    </div>
                </x-ui.resizable.panel>
                <x-ui.resizable.handle :index="0" />
                <x-ui.resizable.panel :default-size="1">
                    <div class="h-full flex items-center justify-center">
                        <span class="text-sm text-muted-foreground">Derecha</span>
                    </div>
                </x-ui.resizable.panel>
            </x-ui.resizable.panel-group>
        </div>
        <x-ui.typography as="muted">El separador sigue siendo draggable, solo sin la barra visual.</x-ui.typography>
    </section>

</div>
</x-layouts.showcase>
