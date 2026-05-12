<x-layouts.showcase title="Carousel — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Carousel</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Carrusel con scroll snap nativo y navegación por teclado. Soporta orientación horizontal y vertical.</x-ui.typography>
    </div>

    {{-- Básico --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal (por defecto)</x-ui.typography>
        <div class="px-12">
            <x-ui.carousel class="w-full max-w-xs mx-auto">
                <x-ui.carousel.content>
                    @foreach(range(1, 5) as $i)
                        <x-ui.carousel.item>
                            <div class="p-1">
                                <div class="flex aspect-square items-center justify-center rounded-xl border bg-card text-card-foreground shadow-sm">
                                    <span class="text-4xl font-semibold">{{ $i }}</span>
                                </div>
                            </div>
                        </x-ui.carousel.item>
                    @endforeach
                </x-ui.carousel.content>
                <x-ui.carousel.previous />
                <x-ui.carousel.next />
            </x-ui.carousel>
        </div>
        <x-ui.typography as="muted" class="text-center">Usa las flechas o las teclas ← → para navegar.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Múltiples items visibles --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Múltiples items visibles</x-ui.typography>
        <div class="px-12">
            <x-ui.carousel class="w-full max-w-sm mx-auto">
                <x-ui.carousel.content>
                    @foreach(range(1, 8) as $i)
                        <x-ui.carousel.item class="basis-1/3">
                            <div class="p-1">
                                <div class="flex aspect-square items-center justify-center rounded-xl border bg-card shadow-sm">
                                    <span class="text-xl font-semibold">{{ $i }}</span>
                                </div>
                            </div>
                        </x-ui.carousel.item>
                    @endforeach
                </x-ui.carousel.content>
                <x-ui.carousel.previous />
                <x-ui.carousel.next />
            </x-ui.carousel>
        </div>
        <x-ui.typography as="muted" class="text-center">Usa <x-ui.typography as="code">class="basis-1/3"</x-ui.typography> en el item para mostrar 3 a la vez.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Cards con contenido --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con contenido</x-ui.typography>
        <div class="px-12">
            <x-ui.carousel class="w-full max-w-xs mx-auto">
                <x-ui.carousel.content>
                    @foreach([
                        ['Diseño', 'Componentes visuales consistentes y accesibles.'],
                        ['Desarrollo', 'Tailwind v4 + Alpine.js + Blade para máxima simplicidad.'],
                        ['Producción', 'Deploy con Laravel 12 sobre cualquier servidor.'],
                    ] as [$title, $desc])
                        <x-ui.carousel.item>
                            <div class="p-1">
                                <x-ui.card class="p-6">
                                    <x-ui.typography as="h3" class="text-lg mb-2">{{ $title }}</x-ui.typography>
                                    <x-ui.typography as="muted">{{ $desc }}</x-ui.typography>
                                </x-ui.card>
                            </div>
                        </x-ui.carousel.item>
                    @endforeach
                </x-ui.carousel.content>
                <x-ui.carousel.previous />
                <x-ui.carousel.next />
            </x-ui.carousel>
        </div>
    </section>

    <x-ui.separator />

    {{-- Vertical --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Vertical</x-ui.typography>
        <div class="py-12 flex justify-center">
            <x-ui.carousel orientation="vertical" class="h-48 w-48">
                <x-ui.carousel.content>
                    @foreach(range(1, 5) as $i)
                        <x-ui.carousel.item>
                            <div class="p-1">
                                <div class="flex h-40 items-center justify-center rounded-xl border bg-card shadow-sm">
                                    <span class="text-4xl font-semibold">{{ $i }}</span>
                                </div>
                            </div>
                        </x-ui.carousel.item>
                    @endforeach
                </x-ui.carousel.content>
                <x-ui.carousel.previous />
                <x-ui.carousel.next />
            </x-ui.carousel>
        </div>
        <x-ui.typography as="muted" class="text-center">Usa las teclas ↑ ↓ para navegar.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Con dots indicator --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con indicador de posición</x-ui.typography>
        <div class="px-12 space-y-4">
            <x-ui.carousel class="w-full max-w-xs mx-auto">
                <x-ui.carousel.content>
                    @foreach(range(1, 5) as $i)
                        <x-ui.carousel.item>
                            <div class="p-1">
                                <div class="flex aspect-square items-center justify-center rounded-xl border bg-card shadow-sm">
                                    <span class="text-4xl font-semibold">{{ $i }}</span>
                                </div>
                            </div>
                        </x-ui.carousel.item>
                    @endforeach
                </x-ui.carousel.content>
                <x-ui.carousel.previous />
                <x-ui.carousel.next />

                {{-- Dots indicator (slot extra) --}}
                <div class="flex justify-center gap-1.5 mt-3">
                    <template x-for="i in total" :key="i">
                        <button
                            type="button"
                            @click="scrollTo(i - 1)"
                            :class="index === i - 1 ? 'bg-foreground' : 'bg-muted-foreground/30 hover:bg-muted-foreground/60'"
                            class="size-1.5 rounded-full transition-colors"
                            :aria-label="'Ir al slide ' + i"
                        ></button>
                    </template>
                </div>
            </x-ui.carousel>
        </div>
    </section>

</div>
</x-layouts.showcase>
