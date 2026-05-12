<x-layouts.showcase title="Progress — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Progress</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Barra de progreso accesible. Soporta tamaños sm/md/lg y estados semánticos que colorean el fill.</x-ui.typography>
    </div>

    {{-- Valores --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Valores</x-ui.typography>
        <div class="space-y-4">
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm"><span>0%</span></div>
                <x-ui.progress :value="0" />
            </div>
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm"><span>33%</span></div>
                <x-ui.progress :value="33" />
            </div>
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm"><span>66%</span></div>
                <x-ui.progress :value="66" />
            </div>
            <div class="space-y-1.5">
                <div class="flex justify-between text-sm"><span>100%</span></div>
                <x-ui.progress :value="100" />
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="space-y-4">
            <div class="space-y-1.5">
                <span class="text-sm text-muted-foreground">sm</span>
                <x-ui.progress size="sm" :value="60" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-muted-foreground">md (default)</span>
                <x-ui.progress size="md" :value="60" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-muted-foreground">lg</span>
                <x-ui.progress size="lg" :value="60" />
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="space-y-4">
            <div class="space-y-1.5">
                <span class="text-sm text-muted-foreground">Default</span>
                <x-ui.progress :value="75" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-destructive">Destructive</span>
                <x-ui.progress :value="75" state="destructive" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-success">Success</span>
                <x-ui.progress :value="100" state="success" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-warning">Warning</span>
                <x-ui.progress :value="85" state="warning" />
            </div>
            <div class="space-y-1.5">
                <span class="text-sm text-info">Info</span>
                <x-ui.progress :value="40" state="info" />
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — subida de archivo</x-ui.typography>
        <x-ui.card class="max-w-sm">
            <x-ui.card.content class="pt-6 space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">informe-2024.pdf</span>
                    <span class="text-muted-foreground">78%</span>
                </div>
                <x-ui.progress :value="78" state="info" />
                <x-ui.typography as="muted" class="text-xs">3.2 MB de 4.1 MB</x-ui.typography>
            </x-ui.card.content>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
