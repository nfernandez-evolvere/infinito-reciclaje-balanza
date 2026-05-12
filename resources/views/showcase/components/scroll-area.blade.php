<x-layouts.showcase title="Scroll Area — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Scroll Area</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Contenedor con scrollbar personalizado. Soporta scroll vertical, horizontal o ambos.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Vertical</x-ui.typography>
        <x-ui.scroll-area class="h-72 w-80 rounded-lg border border-border p-4">
            <div class="space-y-4">
                @foreach(range(1, 20) as $i)
                    <div class="flex items-center gap-3">
                        <x-ui.avatar>
                            <x-ui.avatar.fallback class="text-xs">U{{ $i }}</x-ui.avatar.fallback>
                        </x-ui.avatar>
                        <div>
                            <x-ui.typography as="p" class="font-medium">Usuario {{ $i }}</x-ui.typography>
                            <x-ui.typography as="muted" class="text-xs">usuario{{ $i }}@ejemplo.com</x-ui.typography>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-ui.scroll-area>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal</x-ui.typography>
        <x-ui.scroll-area orientation="horizontal" class="w-96 rounded-lg border border-border p-4">
            <div class="flex gap-4" style="width: max-content">
                @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $month)
                    <div class="flex flex-col items-center gap-1 w-20 shrink-0">
                        <div class="h-16 w-full rounded bg-primary/20 flex items-end justify-center pb-1">
                            <div class="w-8 rounded-sm bg-primary" style="height: {{ rand(20, 60) }}%"></div>
                        </div>
                        <span class="text-xs text-muted-foreground">{{ $month }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.scroll-area>
    </section>

</div>
</x-layouts.showcase>
