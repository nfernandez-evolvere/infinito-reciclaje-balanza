<x-layouts.app-sidebar title="Nuevo post">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Blog</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Nuevo post</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <x-ui.button variant="ghost" size="sm" as="a" href="#">
                <x-lucide-arrow-left class="size-4" />
                Volver
            </x-ui.button>
            <div class="flex items-center gap-2">

                {{-- Vista previa --}}
                <x-ui.dialog>
                    <x-ui.dialog.trigger>
                        <x-ui.button variant="outline" size="sm">
                            <x-lucide-eye class="size-4" />
                            Vista previa
                        </x-ui.button>
                    </x-ui.dialog.trigger>
                    <x-ui.dialog.content size="lg">
                        <x-ui.dialog.header>
                            <x-ui.dialog.title>Vista previa del post</x-ui.dialog.title>
                        </x-ui.dialog.header>
                        <div class="px-6 pb-6 space-y-4">
                            <x-ui.separator />
                            <x-ui.typography as="h2">Introducción al diseño de sistemas</x-ui.typography>
                            <x-ui.typography as="muted" class="text-xs">15 de julio de 2024 · Tecnología</x-ui.typography>
                            <x-ui.typography as="p" class="max-w-prose">
                                El diseño de sistemas es la práctica de crear un conjunto reutilizable de componentes,
                                patrones y guías que permiten a los equipos construir productos consistentes...
                            </x-ui.typography>
                        </div>
                    </x-ui.dialog.content>
                </x-ui.dialog>

                {{-- Acciones principales --}}
                <x-ui.button-group>
                    <x-ui.button variant="outline" size="sm">
                        <x-lucide-save class="size-4" />
                        Borrador
                    </x-ui.button>
                    <x-ui.button size="sm">
                        <x-lucide-send class="size-4" />
                        Publicar
                    </x-ui.button>
                </x-ui.button-group>

                {{-- Configuración mobile --}}
                <div class="lg:hidden">
                    <x-ui.sheet>
                        <x-slot:trigger>
                            <x-ui.button variant="outline" size="sm">
                                <x-lucide-settings class="size-4" />
                            </x-ui.button>
                        </x-slot:trigger>
                        <x-ui.sheet.header class="px-6 pt-6 pb-2">
                            <x-ui.typography as="h3">Configuración del post</x-ui.typography>
                        </x-ui.sheet.header>
                        <x-ui.sheet.content>
                            @include('posts._sidebar-meta')
                        </x-ui.sheet.content>
                    </x-ui.sheet>
                </div>

            </div>
        </div>

        {{-- Layout 2 columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_260px] gap-6 items-start">

            {{-- Editor --}}
            <div class="space-y-4">
                <x-ui.card>
                    <x-ui.card.content class="p-6 space-y-4">
                        <input
                            type="text"
                            placeholder="Título del artículo"
                            value="Introducción al diseño de sistemas"
                            class="w-full border-0 bg-transparent text-2xl font-bold text-foreground placeholder:text-muted-foreground/50 focus:outline-none focus:ring-0"
                        />
                        <x-ui.separator />
                        <x-ui.textarea
                            name="content"
                            placeholder="Escribí el contenido de tu post en Markdown..."
                            rows="20"
                            class="border-0 shadow-none focus-visible:ring-0 resize-none"
                        >El diseño de sistemas es la práctica de crear un conjunto reutilizable de componentes, patrones y guías que permiten a los equipos construir productos consistentes y escalables.

## ¿Por qué necesitás un design system?

Un design system bien estructurado resuelve varios problemas comunes en los equipos de producto:

- **Consistencia visual**: todos los componentes comparten los mismos tokens de diseño
- **Velocidad de desarrollo**: los equipos pueden construir interfaces más rápido
- **Comunicación**: reduce la fricción entre diseño y desarrollo

## Arquitectura de componentes

El sistema sigue Atomic Design adaptado a Blade. Los niveles son:

1. **Átomos**: tokens CSS, typography, icon, badge
2. **Moléculas**: button, input, form-field
3. **Organismos**: card, dialog, data-table
                        </x-ui.textarea>
                    </x-ui.card.content>
                </x-ui.card>
            </div>

            {{-- Metadata sidebar (desktop) --}}
            <div class="hidden lg:block sticky top-20">
                <x-ui.card>
                    <x-ui.card.header>
                        <x-ui.card.title>Configuración</x-ui.card.title>
                    </x-ui.card.header>
                    <x-ui.card.content>
                        @include('posts._sidebar-meta')
                    </x-ui.card.content>
                </x-ui.card>
            </div>

        </div>

    </div>

</x-layouts.app-sidebar>
