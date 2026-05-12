<x-layouts.app-sidebar title="Búsqueda">

    <x-slot:breadcrumb>
        <x-ui.breadcrumb>
            <x-ui.breadcrumb.list>
                <x-ui.breadcrumb.item>
                    <x-ui.breadcrumb.page>Búsqueda</x-ui.breadcrumb.page>
                </x-ui.breadcrumb.item>
            </x-ui.breadcrumb.list>
        </x-ui.breadcrumb>
    </x-slot:breadcrumb>

    <div class="max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        <div class="max-w-2xl space-y-8">

        {{-- Page header --}}
        <div class="space-y-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Búsqueda global</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Buscá miembros, páginas y acciones desde cualquier lugar.
                </x-ui.typography>
            </div>

            {{-- Trigger del command palette --}}
            <x-ui.dialog>
                <x-ui.dialog.trigger>
                    <button
                        type="button"
                        class="flex w-full items-center gap-3 rounded-lg border border-input bg-muted/30 px-4 py-3 text-sm text-muted-foreground hover:bg-muted/50 transition-colors"
                    >
                        <x-lucide-search class="size-4 shrink-0" />
                        <span class="flex-1 text-left">Buscar miembros, páginas, acciones...</span>
                        <div class="flex items-center gap-1">
                            <x-ui.kbd>⌘</x-ui.kbd>
                            <x-ui.kbd>K</x-ui.kbd>
                        </div>
                    </button>
                </x-ui.dialog.trigger>

                <x-ui.dialog.content class="p-0 gap-0 overflow-hidden" size="lg">
                    <x-ui.command>
                        <x-ui.command.input placeholder="Buscar miembros, páginas, acciones..." />
                        <x-ui.command.list class="max-h-80">

                            <x-ui.command.empty>Sin resultados para tu búsqueda.</x-ui.command.empty>

                            <x-ui.command.group heading="Acciones rápidas">
                                <x-ui.command.item value="invite" :keywords="['invitar', 'miembro', 'agregar']">
                                    <x-lucide-user-plus class="size-4" />
                                    Invitar miembro
                                    <x-ui.command.shortcut><x-ui.kbd>⌘I</x-ui.kbd></x-ui.command.shortcut>
                                </x-ui.command.item>
                                <x-ui.command.item value="new-post" :keywords="['crear', 'post', 'artículo']">
                                    <x-lucide-file-plus class="size-4" />
                                    Nuevo post
                                    <x-ui.command.shortcut><x-ui.kbd>⌘N</x-ui.kbd></x-ui.command.shortcut>
                                </x-ui.command.item>
                                <x-ui.command.item value="settings" :keywords="['configuración', 'ajustes']">
                                    <x-lucide-settings class="size-4" />
                                    Configuración
                                    <x-ui.command.shortcut><x-ui.kbd>⌘,</x-ui.kbd></x-ui.command.shortcut>
                                </x-ui.command.item>
                            </x-ui.command.group>

                            <x-ui.command.group heading="Páginas">
                                <x-ui.command.item value="dashboard" href="/examples/dashboard" :keywords="['inicio', 'métricas', 'panel']">
                                    <x-lucide-layout-dashboard class="size-4" />
                                    Dashboard
                                </x-ui.command.item>
                                <x-ui.command.item value="members" href="/examples/members" :keywords="['equipo', 'usuarios']">
                                    <x-lucide-users class="size-4" />
                                    Miembros
                                </x-ui.command.item>
                                <x-ui.command.item value="analytics" href="/examples/analytics" :keywords="['estadísticas', 'reportes']">
                                    <x-lucide-chart-area class="size-4" />
                                    Analytics
                                </x-ui.command.item>
                                <x-ui.command.item value="billing" href="/examples/billing" :keywords="['facturas', 'plan', 'pago']">
                                    <x-lucide-credit-card class="size-4" />
                                    Facturación
                                </x-ui.command.item>
                            </x-ui.command.group>

                            <x-ui.command.group heading="Miembros recientes">
                                @foreach([['María García','Administrador'],['Carlos López','Editor'],['Ana Rodríguez','Miembro']] as [$name, $role])
                                <x-ui.command.item value="{{ strtolower(str_replace(' ', '-', $name)) }}" :keywords="[strtolower($name), strtolower($role)]">
                                    <x-ui.avatar :alt="$name" size="sm" class="size-5" />
                                    <div class="flex-1 min-w-0">
                                        <span class="font-medium">{{ $name }}</span>
                                        <span class="ml-2 text-muted-foreground text-xs">{{ $role }}</span>
                                    </div>
                                </x-ui.command.item>
                                @endforeach
                            </x-ui.command.group>

                        </x-ui.command.list>

                        {{-- Footer con atajos --}}
                        <div class="flex items-center gap-4 border-t border-border px-4 py-2.5">
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <x-ui.kbd>↑↓</x-ui.kbd> navegar
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <x-ui.kbd>↵</x-ui.kbd> seleccionar
                            </div>
                            <div class="flex items-center gap-1.5 text-xs text-muted-foreground">
                                <x-ui.kbd>esc</x-ui.kbd> cerrar
                            </div>
                        </div>
                    </x-ui.command>
                </x-ui.dialog.content>
            </x-ui.dialog>
        </div>

        {{-- Command embebido para el showcase --}}
        <div class="space-y-3">
            <x-ui.typography as="section-label" element="p">Vista previa del command palette</x-ui.typography>
            <x-ui.card class="overflow-hidden">
                <x-ui.command>
                    <x-ui.command.input placeholder="Probá escribir: 'dashboard', 'María', 'invitar'..." />
                    <x-ui.command.list>
                        <x-ui.command.empty>Sin resultados.</x-ui.command.empty>
                        <x-ui.command.group heading="Acciones rápidas">
                            <x-ui.command.item value="invite2" :keywords="['invitar', 'miembro']">
                                <x-lucide-user-plus class="size-4" /> Invitar miembro
                                <x-ui.command.shortcut><x-ui.kbd>⌘I</x-ui.kbd></x-ui.command.shortcut>
                            </x-ui.command.item>
                            <x-ui.command.item value="new-post2" :keywords="['crear', 'post']">
                                <x-lucide-file-plus class="size-4" /> Nuevo post
                                <x-ui.command.shortcut><x-ui.kbd>⌘N</x-ui.kbd></x-ui.command.shortcut>
                            </x-ui.command.item>
                            <x-ui.command.item value="settings2" :keywords="['configuración']">
                                <x-lucide-settings class="size-4" /> Configuración
                                <x-ui.command.shortcut><x-ui.kbd>⌘,</x-ui.kbd></x-ui.command.shortcut>
                            </x-ui.command.item>
                        </x-ui.command.group>
                        <x-ui.command.group heading="Páginas">
                            <x-ui.command.item value="dash2" href="/examples/dashboard" :keywords="['inicio', 'dashboard', 'panel']">
                                <x-lucide-layout-dashboard class="size-4" /> Dashboard
                            </x-ui.command.item>
                            <x-ui.command.item value="mem2" href="/examples/members" :keywords="['miembros', 'equipo']">
                                <x-lucide-users class="size-4" /> Miembros
                            </x-ui.command.item>
                            <x-ui.command.item value="set2" href="/examples/settings" :keywords="['settings', 'configuración']">
                                <x-lucide-settings class="size-4" /> Configuración
                            </x-ui.command.item>
                        </x-ui.command.group>
                        <x-ui.command.group heading="Miembros">
                            @foreach([['María García','Administrador'],['Carlos López','Editor'],['Ana Rodríguez','Miembro'],['Sofía González','Administrador']] as [$name, $role])
                            <x-ui.command.item value="u-{{ $loop->index }}" :keywords="[strtolower($name), strtolower($role)]">
                                <x-ui.avatar :alt="$name" size="sm" class="size-5 shrink-0" />
                                <span class="font-medium">{{ $name }}</span>
                                <span class="text-muted-foreground text-xs">{{ $role }}</span>
                            </x-ui.command.item>
                            @endforeach
                        </x-ui.command.group>
                    </x-ui.command.list>
                </x-ui.command>
            </x-ui.card>
        </div>

        </div>{{-- /max-w-2xl --}}

    </div>

</x-layouts.app-sidebar>
