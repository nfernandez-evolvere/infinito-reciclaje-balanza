<x-layouts.showcase title="Hover Card — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Hover Card</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Tarjeta de preview que aparece al pasar el cursor sobre un trigger. Se abre con un delay de 150ms para evitar cierres accidentales.</x-ui.typography>
    </div>

    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico</x-ui.typography>
        <div class="flex flex-wrap gap-6 py-8 justify-center">
            <x-ui.hover-card>
                <x-ui.hover-card.trigger>
                    <a href="#" class="text-sm font-medium underline underline-offset-4">@shadcn</a>
                </x-ui.hover-card.trigger>
                <x-ui.hover-card.content>
                    <div class="flex gap-3">
                        <x-ui.avatar class="size-10">
                            <x-ui.avatar.fallback>SC</x-ui.avatar.fallback>
                        </x-ui.avatar>
                        <div class="space-y-1">
                            <x-ui.typography as="p" class="font-semibold">shadcn/ui</x-ui.typography>
                            <x-ui.typography as="muted" class="text-xs">Accesible and customizable components.</x-ui.typography>
                            <x-ui.typography as="muted" class="text-xs">Joined December 2022</x-ui.typography>
                        </div>
                    </div>
                </x-ui.hover-card.content>
            </x-ui.hover-card>
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Posiciones</x-ui.typography>
        <div class="flex flex-wrap gap-4 py-12 justify-center">
            @foreach(['top', 'bottom', 'left', 'right'] as $side)
                <x-ui.hover-card>
                    <x-ui.hover-card.trigger>
                        <x-ui.badge variant="outline">{{ $side }}</x-ui.badge>
                    </x-ui.hover-card.trigger>
                    <x-ui.hover-card.content :side="$side">
                        <x-ui.typography as="p">Panel posicionado en <strong>{{ $side }}</strong>.</x-ui.typography>
                    </x-ui.hover-card.content>
                </x-ui.hover-card>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    <section class="space-y-4">
        <x-ui.typography as="section-label">Con contenido rico</x-ui.typography>
        <div class="flex flex-wrap gap-6 py-4 justify-center">
            <x-ui.hover-card>
                <x-ui.hover-card.trigger>
                    <x-ui.button variant="link">Ver estadísticas</x-ui.button>
                </x-ui.hover-card.trigger>
                <x-ui.hover-card.content class="w-72">
                    <div class="space-y-3">
                        <x-ui.typography as="p" class="font-semibold">Resumen del mes</x-ui.typography>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([['Visitas', '12,430'], ['Conversiones', '2.4%'], ['Ingresos', '$8,291'], ['Promedio', '3:42']] as [$label, $val])
                                <div class="rounded-md bg-muted p-2">
                                    <x-ui.typography as="muted" class="text-xs">{{ $label }}</x-ui.typography>
                                    <x-ui.typography as="p" class="font-semibold">{{ $val }}</x-ui.typography>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-ui.hover-card.content>
            </x-ui.hover-card>
        </div>
    </section>

</div>
</x-layouts.showcase>
