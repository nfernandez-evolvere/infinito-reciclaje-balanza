<x-layouts.app-sidebar title="Nuevo producto">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Catálogo</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Productos</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Nuevo producto</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="max-w-2xl space-y-6">

        {{-- Page header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Nuevo producto</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">Completá la información del producto.</x-ui.typography>
            </div>
        </div>

        {{-- Sección básica (siempre visible) --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Información básica</x-ui.card.title>
            </x-ui.card.header>
            <x-ui.card.content>
                @include('products._section-basic')
            </x-ui.card.content>
        </x-ui.card>

        {{-- Accordion con secciones colapsables --}}
        <x-ui.accordion type="multiple" :collapsible="true" value="pricing">

            {{-- Precios y stock --}}
            <x-ui.card class="overflow-hidden">
                <x-ui.accordion.item value="pricing" class="border-0">
                    <x-ui.card.header class="py-4">
                        <x-ui.accordion.trigger class="hover:no-underline">
                            <div class="flex items-center gap-2">
                                <x-lucide-dollar-sign class="size-4 text-muted-foreground" />
                                Precios y stock
                            </div>
                        </x-ui.accordion.trigger>
                    </x-ui.card.header>
                    <x-ui.accordion.content>
                        <div class="px-6 pb-6">
                            @include('products._section-pricing')
                        </div>
                    </x-ui.accordion.content>
                </x-ui.accordion.item>
            </x-ui.card>

            {{-- Variantes --}}
            <x-ui.card class="overflow-hidden">
                <x-ui.accordion.item value="variants" class="border-0">
                    <x-ui.card.header class="py-4">
                        <x-ui.accordion.trigger class="hover:no-underline">
                            <div class="flex items-center gap-2">
                                <x-lucide-layers class="size-4 text-muted-foreground" />
                                Variantes
                            </div>
                        </x-ui.accordion.trigger>
                    </x-ui.card.header>
                    <x-ui.accordion.content>
                        <div class="px-6 pb-6">
                            @include('products._section-variants')
                        </div>
                    </x-ui.accordion.content>
                </x-ui.accordion.item>
            </x-ui.card>

            {{-- SEO y visibilidad --}}
            <x-ui.card class="overflow-hidden">
                <x-ui.accordion.item value="seo" class="border-0">
                    <x-ui.card.header class="py-4">
                        <x-ui.accordion.trigger class="hover:no-underline">
                            <div class="flex items-center gap-2">
                                <x-lucide-search class="size-4 text-muted-foreground" />
                                SEO y visibilidad
                            </div>
                        </x-ui.accordion.trigger>
                    </x-ui.card.header>
                    <x-ui.accordion.content>
                        <div class="px-6 pb-6">
                            @include('products._section-seo')
                        </div>
                    </x-ui.accordion.content>
                </x-ui.accordion.item>
            </x-ui.card>

        </x-ui.accordion>

        {{-- Footer de acciones --}}
        <div class="flex flex-col sm:flex-row-reverse gap-2 pt-2">
            <x-ui.button @click="$store.toast.success('Producto publicado correctamente')">
                <x-lucide-check class="size-4" />
                Publicar producto
            </x-ui.button>
            <x-ui.button variant="outline" @click="$store.toast.info('Borrador guardado')">
                Guardar borrador
            </x-ui.button>
            <x-ui.button variant="ghost" as="a" href="#">
                Cancelar
            </x-ui.button>
                </div>{{-- /max-w-2xl --}}

    </div>

</x-layouts.app-sidebar>
