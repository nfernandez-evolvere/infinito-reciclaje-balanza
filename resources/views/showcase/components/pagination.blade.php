<x-layouts.showcase title="Pagination — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Pagination</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Navegación entre páginas. API compositiva: Pagination → PaginationContent → PaginationItem / PaginationPrevious / PaginationNext / PaginationLink / PaginationEllipsis.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.pagination>
            <x-ui.pagination.content>
                <x-ui.pagination.item>
                    <x-ui.pagination.previous href="#" />
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">1</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#" :active="true">2</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">3</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.next href="#" />
                </x-ui.pagination.item>
            </x-ui.pagination.content>
        </x-ui.pagination>
    </section>

    <x-ui.separator />

    {{-- Con elipsis --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con elipsis</x-ui.typography>
        <x-ui.pagination>
            <x-ui.pagination.content>
                <x-ui.pagination.item>
                    <x-ui.pagination.previous href="#" />
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">1</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">2</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#" :active="true">3</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.ellipsis />
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">10</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.next href="#" />
                </x-ui.pagination.item>
            </x-ui.pagination.content>
        </x-ui.pagination>
    </section>

    <x-ui.separator />

    {{-- Primera página (prev deshabilitado) --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Primera página — Anterior deshabilitado</x-ui.typography>
        <x-ui.pagination>
            <x-ui.pagination.content>
                <x-ui.pagination.item>
                    <x-ui.pagination.previous disabled />
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#" :active="true">1</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">2</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">3</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.ellipsis />
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.link href="#">12</x-ui.pagination.link>
                </x-ui.pagination.item>
                <x-ui.pagination.item>
                    <x-ui.pagination.next href="#" />
                </x-ui.pagination.item>
            </x-ui.pagination.content>
        </x-ui.pagination>
    </section>

    <x-ui.separator />

    {{-- En contexto — tabla paginada --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — tabla paginada</x-ui.typography>
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Usuarios</x-ui.card.title>
                <x-ui.card.description>Mostrando 1–10 de 247 resultados.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <div class="space-y-2">
                    @foreach(['Ana García', 'Carlos López', 'María Martínez', 'Juan Rodríguez', 'Lucía Fernández'] as $name)
                        <div class="flex items-center justify-between py-2 border-b border-border last:border-0">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar size="sm">
                                    <x-ui.avatar.fallback>{{ substr($name, 0, 1) }}</x-ui.avatar.fallback>
                                </x-ui.avatar>
                                <span class="text-sm font-medium">{{ $name }}</span>
                            </div>
                            <x-ui.badge variant="secondary">Activo</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            </x-ui.card.content>
            <x-ui.card.footer class="flex items-center justify-between">
                <x-ui.typography as="muted">Página 4 de 25</x-ui.typography>
                <x-ui.pagination class="mx-0 w-auto justify-end">
                    <x-ui.pagination.content>
                        <x-ui.pagination.item>
                            <x-ui.pagination.previous href="#" />
                        </x-ui.pagination.item>
                        <x-ui.pagination.item>
                            <x-ui.pagination.link href="#">3</x-ui.pagination.link>
                        </x-ui.pagination.item>
                        <x-ui.pagination.item>
                            <x-ui.pagination.link href="#" :active="true">4</x-ui.pagination.link>
                        </x-ui.pagination.item>
                        <x-ui.pagination.item>
                            <x-ui.pagination.link href="#">5</x-ui.pagination.link>
                        </x-ui.pagination.item>
                        <x-ui.pagination.item>
                            <x-ui.pagination.next href="#" />
                        </x-ui.pagination.item>
                    </x-ui.pagination.content>
                </x-ui.pagination>
            </x-ui.card.footer>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
