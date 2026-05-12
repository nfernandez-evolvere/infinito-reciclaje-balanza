<x-layouts.showcase title="Navigation Menu — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Navigation Menu</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Menú de navegación horizontal con paneles desplegables. Un ítem abierto a la vez; hacer click fuera cierra el panel.</x-ui.typography>
    </div>

    {{-- Full example --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Ejemplo completo</x-ui.typography>

        <x-ui.navigation-menu>
            <x-ui.navigation-menu.list>

                {{-- Getting Started (con contenido) --}}
                <x-ui.navigation-menu.item>
                    <x-ui.navigation-menu.trigger>Empezar</x-ui.navigation-menu.trigger>
                    <x-ui.navigation-menu.content>
                        <div class="grid gap-3 p-4 w-[400px] md:grid-cols-2">
                            <div class="row-span-3">
                                <div class="flex h-full select-none flex-col justify-end rounded-md bg-gradient-to-b from-primary/20 to-primary/10 p-6 no-underline outline-none">
                                    <div class="mb-2 mt-4 text-lg font-medium">shadcn/ui</div>
                                    <x-ui.typography as="muted" class="leading-tight">
                                        Componentes de UI reutilizables para Laravel + Blade + Alpine.js.
                                    </x-ui.typography>
                                </div>
                            </div>
                            @foreach([
                                ['Introducción', 'Re-usable components built using Radix UI and Tailwind CSS.'],
                                ['Instalación', 'How to install dependencies and structure your app.'],
                                ['Tipografía', 'Styles for headings, paragraphs, lists...and more'],
                            ] as [$title, $desc])
                                <a href="#" class="block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground">
                                    <div class="text-sm font-medium leading-none">{{ $title }}</div>
                                    <x-ui.typography as="muted" class="line-clamp-2 leading-snug">{{ $desc }}</x-ui.typography>
                                </a>
                            @endforeach
                        </div>
                    </x-ui.navigation-menu.content>
                </x-ui.navigation-menu.item>

                {{-- Componentes --}}
                <x-ui.navigation-menu.item>
                    <x-ui.navigation-menu.trigger>Componentes</x-ui.navigation-menu.trigger>
                    <x-ui.navigation-menu.content>
                        <ul class="grid w-[400px] gap-3 p-4 md:w-[500px] md:grid-cols-2 lg:w-[600px]">
                            @foreach([
                                ['alert-dialog', 'Alert Dialog', 'A modal dialog that interrupts the user.'],
                                ['hover-card',   'Hover Card',   'For sighted users to preview content.'],
                                ['progress',     'Progress',     'Displays an indicator showing task progress.'],
                                ['scroll-area',  'Scroll Area',  'Visually or semantically separates content.'],
                                ['tabs',         'Tabs',         'Switch between different views or sections.'],
                                ['tooltip',      'Tooltip',      'A popup that displays info on hover.'],
                            ] as [$slug, $name, $desc])
                                <li>
                                    <a href="/showcase/components/{{ $slug }}" class="block select-none space-y-1 rounded-md p-3 leading-none no-underline outline-none transition-colors hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground">
                                        <div class="text-sm font-medium leading-none">{{ $name }}</div>
                                        <x-ui.typography as="muted" class="line-clamp-2 leading-snug">{{ $desc }}</x-ui.typography>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </x-ui.navigation-menu.content>
                </x-ui.navigation-menu.item>

                {{-- Link simple --}}
                <x-ui.navigation-menu.item>
                    <x-ui.navigation-menu.link href="/showcase">
                        Documentación
                    </x-ui.navigation-menu.link>
                </x-ui.navigation-menu.item>

            </x-ui.navigation-menu.list>
        </x-ui.navigation-menu>
    </section>

    <x-ui.separator />

    {{-- Solo links --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Solo links</x-ui.typography>
        <x-ui.navigation-menu>
            <x-ui.navigation-menu.list>
                @foreach(['Inicio', 'Acerca de', 'Blog', 'Contacto'] as $link)
                    <x-ui.navigation-menu.item>
                        <x-ui.navigation-menu.link href="#">{{ $link }}</x-ui.navigation-menu.link>
                    </x-ui.navigation-menu.item>
                @endforeach
            </x-ui.navigation-menu.list>
        </x-ui.navigation-menu>
    </section>

</div>
</x-layouts.showcase>
