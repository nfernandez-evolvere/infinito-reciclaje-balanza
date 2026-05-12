<x-layouts.showcase title="Sidebar — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Sidebar</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">
            Sistema completo de sidebar con estado persistente, colapso a íconos y soporte mobile.
            Shortcut: <x-ui.kbd>⌘B</x-ui.kbd> / <x-ui.kbd>Ctrl+B</x-ui.kbd>
        </x-ui.typography>
    </div>

    {{-- Demo container --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Demo interactivo</x-ui.typography>

        <div class="h-[500px] rounded-xl border border-border overflow-hidden">
            <x-ui.sidebar.provider class="h-full">

                {{-- Sidebar --}}
                <x-ui.sidebar>
                    <x-ui.sidebar.header>
                        <div class="flex items-center gap-2 px-1 py-1">
                            <div class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground font-bold text-sm">
                                DS
                            </div>
                            <div class="flex flex-col leading-none">
                                <span class="font-semibold text-sm">Design System</span>
                                <span class="text-xs text-muted-foreground">v1.0.0</span>
                            </div>
                        </div>
                    </x-ui.sidebar.header>

                    <x-ui.sidebar.content>

                        {{-- Búsqueda --}}
                        <div class="px-2 pb-2">
                            <x-ui.sidebar.input placeholder="Buscar..." />
                        </div>

                        <x-ui.sidebar.separator />

                        {{-- Grupo principal --}}
                        <x-ui.sidebar.group>
                            <x-ui.sidebar.group-label>Navegación</x-ui.sidebar.group-label>
                            <x-ui.sidebar.group-content>
                                <x-ui.sidebar.menu>
                                    @foreach([
                                        ['home',         'Inicio',       true],
                                        ['chart',        'Dashboard',    false],
                                        ['users',        'Usuarios',     false],
                                        ['settings',     'Configuración',false],
                                    ] as [$icon, $label, $active])
                                        <x-ui.sidebar.menu-item>
                                            <x-ui.sidebar.menu-button :active="$active">
                                                <x-lucide-{{ $icon }} />
                                                <span>{{ $label }}</span>
                                            </x-ui.sidebar.menu-button>
                                        </x-ui.sidebar.menu-item>
                                    @endforeach
                                </x-ui.sidebar.menu>
                            </x-ui.sidebar.group-content>
                        </x-ui.sidebar.group>

                        <x-ui.sidebar.separator />

                        {{-- Grupo con sub-items --}}
                        <x-ui.sidebar.group>
                            <x-ui.sidebar.group-label>Componentes</x-ui.sidebar.group-label>
                            <x-ui.sidebar.group-content>
                                <x-ui.sidebar.menu>
                                    <x-ui.sidebar.menu-item x-data="{ subOpen: false }">
                                        <x-ui.sidebar.menu-button @click="subOpen = !subOpen">
                                            <x-lucide-folder-open />
                                            <span>Formularios</span>
                                            <x-lucide-chevron-right class="ml-auto transition-transform" x-bind:class="subOpen ? 'rotate-90' : ''" />
                                        </x-ui.sidebar.menu-button>
                                        <x-ui.sidebar.menu-sub x-show="subOpen" x-cloak>
                                            @foreach(['Input', 'Select', 'Checkbox', 'Combobox'] as $comp)
                                                <x-ui.sidebar.menu-sub-item>
                                                    <x-ui.sidebar.menu-sub-button href="/showcase/components/{{ strtolower($comp) }}">
                                                        {{ $comp }}
                                                    </x-ui.sidebar.menu-sub-button>
                                                </x-ui.sidebar.menu-sub-item>
                                            @endforeach
                                        </x-ui.sidebar.menu-sub>
                                    </x-ui.sidebar.menu-item>

                                    <x-ui.sidebar.menu-item x-data="{ subOpen: false }">
                                        <x-ui.sidebar.menu-button @click="subOpen = !subOpen">
                                            <x-lucide-panel-left />
                                            <span>Overlays</span>
                                            <x-lucide-chevron-right class="ml-auto transition-transform" x-bind:class="subOpen ? 'rotate-90' : ''" />
                                        </x-ui.sidebar.menu-button>
                                        <x-ui.sidebar.menu-sub x-show="subOpen" x-cloak>
                                            @foreach(['Dialog', 'Sheet', 'Drawer', 'Popover'] as $comp)
                                                <x-ui.sidebar.menu-sub-item>
                                                    <x-ui.sidebar.menu-sub-button href="/showcase/components/{{ strtolower($comp) }}">
                                                        {{ $comp }}
                                                    </x-ui.sidebar.menu-sub-button>
                                                </x-ui.sidebar.menu-sub-item>
                                            @endforeach
                                        </x-ui.sidebar.menu-sub>
                                    </x-ui.sidebar.menu-item>
                                </x-ui.sidebar.menu>
                            </x-ui.sidebar.group-content>
                        </x-ui.sidebar.group>

                        {{-- Grupo con badges --}}
                        <x-ui.sidebar.group>
                            <x-ui.sidebar.group-label>Notificaciones</x-ui.sidebar.group-label>
                            <x-ui.sidebar.group-content>
                                <x-ui.sidebar.menu>
                                    <x-ui.sidebar.menu-item>
                                        <x-ui.sidebar.menu-button>
                                            <x-lucide-inbox />
                                            <span>Bandeja</span>
                                            <x-ui.sidebar.menu-badge>12</x-ui.sidebar.menu-badge>
                                        </x-ui.sidebar.menu-button>
                                    </x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-item>
                                        <x-ui.sidebar.menu-button>
                                            <x-lucide-bell />
                                            <span>Alertas</span>
                                            <x-ui.sidebar.menu-badge>3</x-ui.sidebar.menu-badge>
                                        </x-ui.sidebar.menu-button>
                                    </x-ui.sidebar.menu-item>
                                </x-ui.sidebar.menu>
                            </x-ui.sidebar.group-content>
                        </x-ui.sidebar.group>

                        {{-- Skeleton loading example --}}
                        <x-ui.sidebar.group>
                            <x-ui.sidebar.group-label>Cargando...</x-ui.sidebar.group-label>
                            <x-ui.sidebar.group-content>
                                <x-ui.sidebar.menu>
                                    @foreach([true, false, false] as $showIcon)
                                        <x-ui.sidebar.menu-skeleton :show-icon="$showIcon" />
                                    @endforeach
                                </x-ui.sidebar.menu>
                            </x-ui.sidebar.group-content>
                        </x-ui.sidebar.group>

                    </x-ui.sidebar.content>

                    <x-ui.sidebar.footer>
                        <x-ui.sidebar.menu>
                            <x-ui.sidebar.menu-item>
                                <x-ui.sidebar.menu-button>
                                    <x-lucide-user />
                                    <span>Nicolas Fernandez</span>
                                </x-ui.sidebar.menu-button>
                            </x-ui.sidebar.menu-item>
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.footer>
                </x-ui.sidebar>

                {{-- Main content --}}
                <x-ui.sidebar.inset>
                    <header class="flex h-14 items-center gap-2 border-b border-border px-4">
                        <x-ui.sidebar.trigger />
                        <x-ui.separator orientation="vertical" class="h-4" />
                        <span class="text-sm text-muted-foreground">Presioná el botón o <x-ui.kbd>⌘B</x-ui.kbd> para colapsar/expandir</span>
                    </header>
                    <div class="flex flex-1 flex-col gap-4 p-4">
                        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                            @foreach(range(1,3) as $i)
                                <div class="aspect-video rounded-xl bg-muted/50"></div>
                            @endforeach
                        </div>
                        <div class="min-h-[100px] flex-1 rounded-xl bg-muted/50"></div>
                    </div>
                </x-ui.sidebar.inset>

            </x-ui.sidebar.provider>
        </div>
    </section>

    <x-ui.separator />

    {{-- API reference --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sub-componentes</x-ui.typography>
        <div class="overflow-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-border text-left">
                        <th class="pb-2 pr-4 font-medium">Componente</th>
                        <th class="pb-2 pr-4 font-medium">Descripción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach([
                        ['x-ui.sidebar.provider',         'Contexto global: estado open/collapsed, ⌘B'],
                        ['x-ui.sidebar',                  'Panel principal con overlay mobile'],
                        ['x-ui.sidebar.trigger',          'Botón de toggle con ícono panel-left'],
                        ['x-ui.sidebar.header',           'Zona superior del sidebar'],
                        ['x-ui.sidebar.content',          'Área scrolleable central'],
                        ['x-ui.sidebar.footer',           'Zona inferior del sidebar'],
                        ['x-ui.sidebar.separator',        'Separador horizontal'],
                        ['x-ui.sidebar.group',            'Sección con padding'],
                        ['x-ui.sidebar.group-label',      'Título de sección (se oculta en modo icono)'],
                        ['x-ui.sidebar.group-content',    'Contenido de la sección'],
                        ['x-ui.sidebar.menu',             '<ul> de items'],
                        ['x-ui.sidebar.menu-item',        '<li> contenedor'],
                        ['x-ui.sidebar.menu-button',      'Botón de navegación (variant, size, active, href)'],
                        ['x-ui.sidebar.menu-action',      'Acción secundaria (ej: "...")'],
                        ['x-ui.sidebar.menu-badge',       'Badge numérico en el item'],
                        ['x-ui.sidebar.menu-skeleton',    'Skeleton de carga'],
                        ['x-ui.sidebar.menu-sub',         '<ul> para sub-items'],
                        ['x-ui.sidebar.menu-sub-item',    '<li> del sub-menú'],
                        ['x-ui.sidebar.menu-sub-button',  'Botón del sub-item (size sm)'],
                        ['x-ui.sidebar.input',            'Input de búsqueda interno'],
                        ['x-ui.sidebar.inset',            '<main> para el contenido principal'],
                    ] as [$comp, $desc])
                        <tr>
                            <td class="py-2 pr-4 font-mono text-xs text-foreground">{{ $comp }}</td>
                            <td class="py-2 text-muted-foreground">{{ $desc }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

</div>
</x-layouts.showcase>
