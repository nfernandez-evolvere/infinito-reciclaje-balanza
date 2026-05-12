<x-layouts.showcase title="Drawer — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Drawer</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Panel deslizable desde el borde inferior. Ideal para mobile. Comparte sub-componentes con Dialog y Sheet. Se cierra con el handle, la overlay o Escape.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.drawer>
            <x-slot:trigger>
                <x-ui.button variant="outline">Abrir Drawer</x-ui.button>
            </x-slot:trigger>
            <x-ui.drawer.header>
                <x-ui.drawer.title>Drawer</x-ui.drawer.title>
                <x-ui.drawer.description>Deslizá hacia abajo o tocá el handle para cerrar.</x-ui.drawer.description>
            </x-ui.drawer.header>
            <x-ui.drawer.content>
                <x-ui.typography as="muted">Contenido del drawer. Puede scrollear si el contenido supera la altura máxima.</x-ui.typography>
            </x-ui.drawer.content>
            <x-ui.drawer.footer>
                <x-ui.button>Confirmar</x-ui.button>
                <x-ui.button variant="outline" @click="open = false">Cancelar</x-ui.button>
            </x-ui.drawer.footer>
        </x-ui.drawer>
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-wrap gap-3">
            @foreach(['sm' => 'Small (40vh)', 'md' => 'Medium (60vh)', 'lg' => 'Large (85vh)', 'auto' => 'Auto'] as $size => $label)
                <x-ui.drawer size="{{ $size }}">
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm">{{ $label }}</x-ui.button>
                    </x-slot:trigger>
                    <x-ui.drawer.header>
                        <x-ui.drawer.title>Drawer {{ $label }}</x-ui.drawer.title>
                        <x-ui.drawer.description>size="{{ $size }}"</x-ui.drawer.description>
                    </x-ui.drawer.header>
                    <x-ui.drawer.content>
                        <x-ui.typography as="muted">Contenido del drawer con size="{{ $size }}".</x-ui.typography>
                    </x-ui.drawer.content>
                </x-ui.drawer>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto — carrito --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — resumen de carrito</x-ui.typography>
        <x-ui.drawer size="lg">
            <x-slot:trigger>
                <x-ui.button>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    Ver carrito (3)
                </x-ui.button>
            </x-slot:trigger>
            <x-ui.drawer.header>
                <x-ui.drawer.title>Tu carrito</x-ui.drawer.title>
                <x-ui.drawer.description>3 productos · $487.00</x-ui.drawer.description>
            </x-ui.drawer.header>
            <x-ui.drawer.content>
                <div class="space-y-4">
                    @foreach([
                        ['Plan Pro',          '$99.00',  1],
                        ['Soporte Premium',   '$29.00',  1],
                        ['Almacenamiento 50G','$179.50', 2],
                    ] as [$name, $price, $qty])
                        <div class="flex items-center justify-between py-3 border-b border-border last:border-0">
                            <div>
                                <x-ui.typography as="p" class="font-medium">{{ $name }}</x-ui.typography>
                                <x-ui.typography as="muted" class="text-xs">Cantidad: {{ $qty }}</x-ui.typography>
                            </div>
                            <span class="text-sm font-semibold">{{ $price }}</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.drawer.content>
            <x-ui.drawer.footer>
                <x-ui.button class="w-full">Ir al checkout · $487.00</x-ui.button>
            </x-ui.drawer.footer>
        </x-ui.drawer>
    </section>

</div>
</x-layouts.showcase>
