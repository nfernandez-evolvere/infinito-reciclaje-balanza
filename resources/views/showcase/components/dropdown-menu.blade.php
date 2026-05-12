<x-layouts.showcase title="Dropdown Menu — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Dropdown Menu</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Menú contextual anclado a un trigger. Soporta ítems, grupos, labels, separadores, checkboxes, radios, atajos de teclado y sub-menús.</x-ui.typography>
    </div>

    {{-- ── Básico ──────────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Abrir menú</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content>
                <x-ui.dropdown-menu.item>Perfil</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item>Configuración</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item>Equipo</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive">Cerrar sesión</x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Con íconos y shortcuts ──────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con íconos y atajos</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Mi cuenta</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content class="w-56">
                <x-ui.dropdown-menu.label>Mi cuenta</x-ui.dropdown-menu.label>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.group>
                    <x-ui.dropdown-menu.item>
                        <x-lucide-eye />
                        Perfil
                        <x-ui.dropdown-menu.shortcut>⇧⌘P</x-ui.dropdown-menu.shortcut>
                    </x-ui.dropdown-menu.item>
                    <x-ui.dropdown-menu.item>
                        <x-lucide-copy />
                        Facturación
                        <x-ui.dropdown-menu.shortcut>⌘B</x-ui.dropdown-menu.shortcut>
                    </x-ui.dropdown-menu.item>
                    <x-ui.dropdown-menu.item>
                        <x-lucide-moon />
                        Configuración
                        <x-ui.dropdown-menu.shortcut>⌘S</x-ui.dropdown-menu.shortcut>
                    </x-ui.dropdown-menu.item>
                </x-ui.dropdown-menu.group>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item>
                    <x-lucide-plus />
                    Nuevo equipo
                    <x-ui.dropdown-menu.shortcut>⌘T</x-ui.dropdown-menu.shortcut>
                </x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive">
                    <x-lucide-x />
                    Cerrar sesión
                    <x-ui.dropdown-menu.shortcut>⇧⌘Q</x-ui.dropdown-menu.shortcut>
                </x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Checkboxes ──────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Checkbox items</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Ver opciones</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content class="w-52">
                <x-ui.dropdown-menu.label>Visibilidad</x-ui.dropdown-menu.label>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.checkbox-item :checked="true">
                    Barra de estado
                </x-ui.dropdown-menu.checkbox-item>
                <x-ui.dropdown-menu.checkbox-item :checked="false">
                    Panel de actividad
                </x-ui.dropdown-menu.checkbox-item>
                <x-ui.dropdown-menu.checkbox-item :checked="true">
                    Panel lateral
                </x-ui.dropdown-menu.checkbox-item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Radio group ─────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Radio group</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Posición del panel</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content class="w-48">
                <x-ui.dropdown-menu.label>Posición</x-ui.dropdown-menu.label>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.radio-group value="bottom">
                    <x-ui.dropdown-menu.radio-item value="top">Arriba</x-ui.dropdown-menu.radio-item>
                    <x-ui.dropdown-menu.radio-item value="bottom">Abajo</x-ui.dropdown-menu.radio-item>
                    <x-ui.dropdown-menu.radio-item value="right">Derecha</x-ui.dropdown-menu.radio-item>
                </x-ui.dropdown-menu.radio-group>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Sub-menú ─────────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con sub-menú</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Opciones</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content class="w-52">
                <x-ui.dropdown-menu.item>
                    <x-lucide-eye />
                    Perfil
                </x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item>
                    <x-lucide-copy />
                    Facturación
                </x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.sub>
                    <x-ui.dropdown-menu.sub-trigger>
                        <x-lucide-plus />
                        Invitar usuarios
                    </x-ui.dropdown-menu.sub-trigger>
                    <x-ui.dropdown-menu.sub-content>
                        <x-ui.dropdown-menu.item>Por email</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>Por mensaje</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.separator />
                        <x-ui.dropdown-menu.item>Más opciones...</x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.sub-content>
                </x-ui.dropdown-menu.sub>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive">
                    <x-lucide-x />
                    Eliminar equipo
                </x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Items deshabilitados ─────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Items deshabilitados</x-ui.typography>
        <x-ui.dropdown-menu>
            <x-ui.dropdown-menu.trigger>
                <x-ui.button variant="outline">Acciones</x-ui.button>
            </x-ui.dropdown-menu.trigger>
            <x-ui.dropdown-menu.content class="w-48">
                <x-ui.dropdown-menu.item>Editar</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item :disabled="true">Duplicar</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.item :disabled="true">Archivar</x-ui.dropdown-menu.item>
                <x-ui.dropdown-menu.separator />
                <x-ui.dropdown-menu.item variant="destructive">Eliminar</x-ui.dropdown-menu.item>
            </x-ui.dropdown-menu.content>
        </x-ui.dropdown-menu>
    </section>

    <x-ui.separator />

    {{-- ── Alineaciones ─────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Alineaciones</x-ui.typography>
        <div class="flex flex-wrap gap-4">
            @foreach(['start', 'center', 'end'] as $align)
                <x-ui.dropdown-menu>
                    <x-ui.dropdown-menu.trigger>
                        <x-ui.button variant="outline">align={{ $align }}</x-ui.button>
                    </x-ui.dropdown-menu.trigger>
                    <x-ui.dropdown-menu.content :align="$align">
                        <x-ui.dropdown-menu.item>Ítem 1</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>Ítem 2</x-ui.dropdown-menu.item>
                        <x-ui.dropdown-menu.item>Ítem 3</x-ui.dropdown-menu.item>
                    </x-ui.dropdown-menu.content>
                </x-ui.dropdown-menu>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Como menú de fila de tabla ───────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Uso típico — acciones de fila</x-ui.typography>
        <div class="rounded-lg border border-border overflow-hidden">
            @foreach(['Ana García', 'Luis Pérez', 'María López'] as $name)
                <div class="flex items-center justify-between px-4 py-3 border-b border-border last:border-0 hover:bg-muted/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar>
                            <x-ui.avatar.fallback>{{ substr($name, 0, 2) }}</x-ui.avatar.fallback>
                        </x-ui.avatar>
                        <span class="text-sm font-medium">{{ $name }}</span>
                    </div>
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button variant="ghost" size="icon" class="size-8">
                                <x-lucide-ellipsis class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="end">
                            <x-ui.dropdown-menu.item>Ver perfil</x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.item>Editar</x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.separator />
                            <x-ui.dropdown-menu.item variant="destructive">Eliminar</x-ui.dropdown-menu.item>
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                </div>
            @endforeach
        </div>
    </section>

</div>
</x-layouts.showcase>
