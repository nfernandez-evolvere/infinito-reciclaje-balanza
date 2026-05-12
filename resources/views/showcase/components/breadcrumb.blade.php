<x-layouts.showcase title="Breadcrumb — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Breadcrumb</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Navegación de migas de pan. API compositiva: Breadcrumb → BreadcrumbList → BreadcrumbItem / BreadcrumbSeparator. La página actual usa BreadcrumbPage (sin enlace).</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Inicio</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Componentes</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Breadcrumb</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </section>

    <x-ui.separator />

    {{-- Separador custom --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Separador custom (slash)</x-ui.typography>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Docs</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator>
                    <span class="text-muted-foreground">/</span>
                </x-ui.breadcrumb.separator>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Navegación</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator>
                    <span class="text-muted-foreground">/</span>
                </x-ui.breadcrumb.separator>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Breadcrumb</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </section>

    <x-ui.separator />

    {{-- Con íconos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con íconos</x-ui.typography>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#" class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        Inicio
                    </x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#" class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/>
                        </svg>
                        Componentes
                    </x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page class="flex items-center gap-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                        </svg>
                        Breadcrumb
                    </x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </section>

    <x-ui.separator />

    {{-- Con ellipsis --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con elipsis (ruta larga)</x-ui.typography>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Inicio</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.ellipsis />
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.link href="#">Navegación</x-ui.breadcrumb.link>
                </x-ui.breadcrumb.item>
                <x-ui.breadcrumb.separator />
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Breadcrumb</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </section>

    <x-ui.separator />

    {{-- En contexto — topbar de app --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — header de página</x-ui.typography>
        @foreach([
            [['Inicio', '#'], ['Usuarios', '#'], ['Perfil']],
            [['Dashboard', '#'], ['Proyectos', '#'], ['Diseño', '#'], ['Componentes']],
            [['Admin', '#'], ['Configuración']],
        ] as $crumbs)
            <div class="rounded-lg border border-border p-4">
                <x-ui.breadcrumb>
                    <x-ui.breadcrumb.list>
                        @foreach($crumbs as $i => $crumb)
                            @if($i > 0)
                                <x-ui.breadcrumb.separator />
                            @endif
                            <x-ui.breadcrumb.item>
                                @if(count($crumb) === 2)
                                    <x-ui.breadcrumb.link href="{{ $crumb[1] }}">{{ $crumb[0] }}</x-ui.breadcrumb.link>
                                @else
                                    <x-ui.breadcrumb.page>{{ $crumb[0] }}</x-ui.breadcrumb.page>
                                @endif
                            </x-ui.breadcrumb.item>
                        @endforeach
                    </x-ui.breadcrumb.list>
                </x-ui.breadcrumb>
            </div>
        @endforeach
    </section>

</div>
</x-layouts.showcase>
