<x-layouts.app title="Showcase de Componentes">

<div class="space-y-16">

    <div>
        <h1 class="text-3xl font-bold">Design System Showcase</h1>
        <p class="mt-2 text-muted-foreground">Todos los componentes Blade inspirados en shadcn/ui.</p>
    </div>

    {{-- BADGE --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Badge</h2>
        <div class="flex flex-wrap gap-2">
            <x-badge variant="default">Default</x-badge>
            <x-badge variant="secondary">Secondary</x-badge>
            <x-badge variant="destructive">Destructive</x-badge>
            <x-badge variant="outline">Outline</x-badge>
            <x-badge variant="warning">Warning</x-badge>
            <x-badge variant="success">Success</x-badge>
        </div>
    </section>

    {{-- BUTTON --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Button</h2>
        <div class="flex flex-wrap items-center gap-3">
            <x-button variant="default">Default</x-button>
            <x-button variant="secondary">Secondary</x-button>
            <x-button variant="destructive">Destructive</x-button>
            <x-button variant="outline">Outline</x-button>
            <x-button variant="ghost">Ghost</x-button>
            <x-button variant="link">Link</x-button>
            <x-button variant="default" size="sm">Small</x-button>
            <x-button variant="default" size="lg">Large</x-button>
            <x-button variant="outline" size="icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </x-button>
            <x-button disabled>Disabled</x-button>
        </div>
    </section>

    {{-- ALERT --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Alert</h2>
        <x-alert>
            <x-alert.title>Default</x-alert.title>
            <x-alert.description>Alerta informativa sin ícono.</x-alert.description>
        </x-alert>
        <x-alert variant="destructive">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
            <x-alert.title>Destructive</x-alert.title>
            <x-alert.description>Algo salió mal.</x-alert.description>
        </x-alert>
    </section>

    {{-- CARD --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Card</h2>
        <div class="grid md:grid-cols-2 gap-4">
            <x-card>
                <x-card.header>
                    <x-card.title>Título de la card</x-card.title>
                    <x-card.description>Descripción opcional de la card.</x-card.description>
                </x-card.header>
                <x-card.content>
                    <p class="text-sm text-muted-foreground">Contenido de la card.</p>
                </x-card.content>
                <x-card.footer>
                    <x-button variant="default" size="sm">Acción</x-button>
                </x-card.footer>
            </x-card>
            <x-card>
                <x-card.header>
                    <x-card.title>Skeleton</x-card.title>
                    <x-card.description>Estado de carga.</x-card.description>
                </x-card.header>
                <x-card.content class="space-y-2">
                    <x-skeleton class="h-4 w-full" />
                    <x-skeleton class="h-4 w-3/4" />
                    <x-skeleton class="h-4 w-1/2" />
                </x-card.content>
            </x-card>
        </div>
    </section>

    {{-- FORM CONTROLS --}}
    <section class="space-y-6">
        <h2 class="text-xl font-semibold border-b pb-2">Controles de formulario</h2>
        <div class="grid md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <x-label for="name">Nombre</x-label>
                <x-input id="name" placeholder="Juan García" />
            </div>
            <div class="space-y-2">
                <x-label for="email">Email</x-label>
                <x-input id="email" type="email" placeholder="juan@ejemplo.com" />
            </div>
            <div class="space-y-2">
                <x-label for="msg">Mensaje</x-label>
                <x-textarea id="msg" placeholder="Escribe tu mensaje..." rows="3" />
            </div>
            <div class="space-y-2">
                <x-label for="role">Rol</x-label>
                <x-select id="role">
                    <option value="">Seleccionar...</option>
                    <option value="admin">Administrador</option>
                    <option value="editor">Editor</option>
                    <option value="viewer">Viewer</option>
                </x-select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-6">
            <label class="flex items-center gap-2 text-sm">
                <x-checkbox /> Acepto los términos
            </label>
            <label class="flex items-center gap-2 text-sm">
                <x-radio name="plan" value="free" :checked="true" /> Plan gratuito
            </label>
            <label class="flex items-center gap-2 text-sm">
                <x-radio name="plan" value="pro" /> Plan Pro
            </label>
            <label class="flex items-center gap-2 text-sm">
                <x-switch /> Notificaciones
            </label>
        </div>
        <div class="space-y-2">
            <x-label for="input-error">Campo con error</x-label>
            <x-input id="input-error" :error="true" value="valor incorrecto" />
        </div>
    </section>

    {{-- PROGRESS --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Progress</h2>
        <x-progress :value="33" class="w-full" />
        <x-progress :value="66" class="w-full" />
        <x-progress :value="100" class="w-full" />
    </section>

    {{-- AVATAR --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Avatar</h2>
        <div class="flex items-center gap-3">
            <x-avatar src="https://github.com/shadcn.png" alt="shadcn" />
            <x-avatar fallback="JG" />
            <x-avatar fallback="AB" class="h-12 w-12" />
            <x-avatar fallback="?" class="h-8 w-8" />
        </div>
    </section>

    {{-- SEPARATOR --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Separator</h2>
        <p class="text-sm">Texto arriba</p>
        <x-separator />
        <p class="text-sm">Texto abajo</p>
        <div class="flex h-5 items-center gap-4 text-sm">
            <span>Perfil</span>
            <x-separator orientation="vertical" />
            <span>Configuración</span>
            <x-separator orientation="vertical" />
            <span>Cerrar sesión</span>
        </div>
    </section>

    {{-- TABS --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Tabs</h2>
        <x-tabs default="cuenta">
            <x-tabs.list>
                <x-tabs.trigger value="cuenta">Cuenta</x-tabs.trigger>
                <x-tabs.trigger value="contrasena">Contraseña</x-tabs.trigger>
            </x-tabs.list>
            <x-tabs.content value="cuenta">
                <x-card>
                    <x-card.header>
                        <x-card.title>Cuenta</x-card.title>
                        <x-card.description>Gestioná la información de tu cuenta.</x-card.description>
                    </x-card.header>
                    <x-card.content>
                        <x-input placeholder="Nombre de usuario" />
                    </x-card.content>
                    <x-card.footer><x-button>Guardar</x-button></x-card.footer>
                </x-card>
            </x-tabs.content>
            <x-tabs.content value="contrasena">
                <x-card>
                    <x-card.header>
                        <x-card.title>Contraseña</x-card.title>
                        <x-card.description>Cambiá tu contraseña aquí.</x-card.description>
                    </x-card.header>
                    <x-card.content>
                        <x-input type="password" placeholder="Nueva contraseña" />
                    </x-card.content>
                    <x-card.footer><x-button>Actualizar</x-button></x-card.footer>
                </x-card>
            </x-tabs.content>
        </x-tabs>
    </section>

    {{-- ACCORDION --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Accordion</h2>
        <x-accordion class="max-w-lg">
            <x-accordion.item value="item-1">
                <x-accordion.trigger>¿Es accesible?</x-accordion.trigger>
                <x-accordion.content>Sí. Usa atributos ARIA correctamente.</x-accordion.content>
            </x-accordion.item>
            <x-accordion.item value="item-2">
                <x-accordion.trigger>¿Está estilizado?</x-accordion.trigger>
                <x-accordion.content>Sí. Viene con estilos de shadcn/ui traducidos a Tailwind.</x-accordion.content>
            </x-accordion.item>
            <x-accordion.item value="item-3">
                <x-accordion.trigger>¿Es animado?</x-accordion.trigger>
                <x-accordion.content>Sí. Usa Alpine Collapse para la animación de apertura/cierre.</x-accordion.content>
            </x-accordion.item>
        </x-accordion>
    </section>

    {{-- TOGGLE --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Toggle</h2>
        <div class="flex gap-2">
            <x-toggle>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="M6 12h12"/><path d="M6 6h12"/><path d="M6 18h7"/></svg>
            </x-toggle>
            <x-toggle variant="outline">Negrita</x-toggle>
            <x-toggle :pressed="true">Activo</x-toggle>
        </div>
    </section>

    {{-- COLLAPSIBLE --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Collapsible</h2>
        <x-collapsible class="max-w-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Repositorios (3)</span>
                <x-collapsible.trigger>
                    <x-button variant="ghost" size="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></svg>
                    </x-button>
                </x-collapsible.trigger>
            </div>
            <div class="rounded-md border px-4 py-2 text-sm">@radix-ui/primitives</div>
            <x-collapsible.content class="space-y-2">
                <div class="rounded-md border px-4 py-2 text-sm">@radix-ui/colors</div>
                <div class="rounded-md border px-4 py-2 text-sm">@stitches/react</div>
            </x-collapsible.content>
        </x-collapsible>
    </section>

    {{-- DROPDOWN MENU --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Dropdown Menu</h2>
        <x-dropdown-menu>
            <x-dropdown-menu.trigger>
                <x-button variant="outline">Abrir menú</x-button>
            </x-dropdown-menu.trigger>
            <x-dropdown-menu.content>
                <x-dropdown-menu.label>Mi cuenta</x-dropdown-menu.label>
                <x-dropdown-menu.separator />
                <x-dropdown-menu.item>Perfil</x-dropdown-menu.item>
                <x-dropdown-menu.item>Configuración</x-dropdown-menu.item>
                <x-dropdown-menu.separator />
                <x-dropdown-menu.item :destructive="true">Cerrar sesión</x-dropdown-menu.item>
            </x-dropdown-menu.content>
        </x-dropdown-menu>
    </section>

    {{-- TOOLTIP --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Tooltip</h2>
        <div class="flex gap-4">
            <x-tooltip text="Parte superior">
                <x-button variant="outline">Top</x-button>
            </x-tooltip>
            <x-tooltip text="Parte inferior" side="bottom">
                <x-button variant="outline">Bottom</x-button>
            </x-tooltip>
            <x-tooltip text="Izquierda" side="left">
                <x-button variant="outline">Left</x-button>
            </x-tooltip>
            <x-tooltip text="Derecha" side="right">
                <x-button variant="outline">Right</x-button>
            </x-tooltip>
        </div>
    </section>

    {{-- POPOVER --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Popover</h2>
        <x-popover>
            <x-popover.trigger>
                <x-button variant="outline">Abrir Popover</x-button>
            </x-popover.trigger>
            <x-popover.content>
                <div class="space-y-2">
                    <p class="text-sm font-medium">Configuración</p>
                    <p class="text-xs text-muted-foreground">Ajustá tus preferencias de notificaciones.</p>
                    <x-separator />
                    <div class="space-y-2">
                        <x-label class="flex items-center gap-2 text-sm font-normal">
                            <x-switch /> Email
                        </x-label>
                        <x-label class="flex items-center gap-2 text-sm font-normal">
                            <x-switch :checked="true" /> Push
                        </x-label>
                    </div>
                </div>
            </x-popover.content>
        </x-popover>
    </section>

    {{-- DIALOG --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Dialog</h2>
        <x-dialog>
            <x-dialog.trigger>
                <x-button>Abrir Dialog</x-button>
            </x-dialog.trigger>
            <x-dialog.content>
                <x-dialog.header>
                    <x-dialog.title>¿Confirmar acción?</x-dialog.title>
                    <x-dialog.description>Esta acción no se puede deshacer.</x-dialog.description>
                </x-dialog.header>
                <x-dialog.footer>
                    <x-button variant="outline" @click="open = false">Cancelar</x-button>
                    <x-button variant="destructive" @click="open = false">Confirmar</x-button>
                </x-dialog.footer>
            </x-dialog.content>
        </x-dialog>
    </section>

    {{-- SHEET --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Sheet</h2>
        <div class="flex gap-3">
            @foreach(['right', 'left', 'top', 'bottom'] as $side)
            <x-sheet>
                <x-sheet.trigger>
                    <x-button variant="outline">{{ ucfirst($side) }}</x-button>
                </x-sheet.trigger>
                <x-sheet.content side="{{ $side }}">
                    <div class="mt-6 space-y-2">
                        <p class="text-lg font-semibold">Sheet — {{ ucfirst($side) }}</p>
                        <p class="text-sm text-muted-foreground">Panel lateral deslizante desde el {{ $side }}.</p>
                    </div>
                </x-sheet.content>
            </x-sheet>
            @endforeach
        </div>
    </section>

    {{-- BREADCRUMB --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Breadcrumb</h2>
        <x-breadcrumb>
            <x-breadcrumb.item><x-breadcrumb.link href="/">Inicio</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.link href="/docs">Docs</x-breadcrumb.link></x-breadcrumb.item>
            <x-breadcrumb.separator />
            <x-breadcrumb.item><x-breadcrumb.page>Componentes</x-breadcrumb.page></x-breadcrumb.item>
        </x-breadcrumb>
    </section>

    {{-- PAGINATION --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Pagination</h2>
        <x-pagination>
            <x-pagination.content>
                <x-pagination.item>
                    <x-pagination.link href="#" :disabled="true">« Anterior</x-pagination.link>
                </x-pagination.item>
                @foreach([1,2,3,4,5] as $p)
                <x-pagination.item>
                    <x-pagination.link href="#" :active="$p === 2">{{ $p }}</x-pagination.link>
                </x-pagination.item>
                @endforeach
                <x-pagination.item>
                    <x-pagination.link href="#">Siguiente »</x-pagination.link>
                </x-pagination.item>
            </x-pagination.content>
        </x-pagination>
    </section>

    {{-- TABLE --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Table</h2>
        <x-card>
            <x-table>
                <x-table.caption>Listado de usuarios</x-table.caption>
                <x-table.header>
                    <x-table.row>
                        <x-table.head>Nombre</x-table.head>
                        <x-table.head>Email</x-table.head>
                        <x-table.head>Rol</x-table.head>
                        <x-table.head class="text-right">Estado</x-table.head>
                    </x-table.row>
                </x-table.header>
                <x-table.body>
                    @foreach([
                        ['Ana García', 'ana@ejemplo.com', 'Admin', 'default'],
                        ['Carlos López', 'carlos@ejemplo.com', 'Editor', 'secondary'],
                        ['María Torres', 'maria@ejemplo.com', 'Viewer', 'outline'],
                    ] as $row)
                    <x-table.row>
                        <x-table.cell class="font-medium">{{ $row[0] }}</x-table.cell>
                        <x-table.cell>{{ $row[1] }}</x-table.cell>
                        <x-table.cell>{{ $row[2] }}</x-table.cell>
                        <x-table.cell class="text-right">
                            <x-badge variant="{{ $row[3] }}">Activo</x-badge>
                        </x-table.cell>
                    </x-table.row>
                    @endforeach
                </x-table.body>
            </x-table>
        </x-card>
    </section>

    {{-- TOAST --}}
    <section class="space-y-3">
        <h2 class="text-xl font-semibold border-b pb-2">Toast</h2>
        <div class="flex flex-wrap gap-3">
            <x-button variant="outline" @click="$dispatch('toast', { message: 'Operación exitosa.', variant: 'success' })">
                Toast éxito
            </x-button>
            <x-button variant="outline" @click="$dispatch('toast', { message: 'Cambios guardados.' })">
                Toast default
            </x-button>
            <x-button variant="outline" @click="$dispatch('toast', { message: 'Algo salió mal.', variant: 'destructive' })">
                Toast error
            </x-button>
        </div>
    </section>

</div>

</x-layouts.app>
