<x-layouts.showcase title="Command — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Command</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Paleta de comandos con búsqueda en tiempo real, navegación por teclado y grupos. Incluye variante de diálogo modal (Cmd+K).</x-ui.typography>
    </div>

    {{-- Inline --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Inline</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.command>
                <x-ui.command.input placeholder="Escribí para buscar..." />
                <x-ui.command.list>
                    <x-ui.command.empty>Sin resultados.</x-ui.command.empty>

                    <x-ui.command.group heading="Sugerencias">
                        <x-ui.command.item value="calendar" keywords="['fecha', 'agendar']">
                            <x-lucide-calendar-days class="size-4" />
                            Calendario
                        </x-ui.command.item>
                        <x-ui.command.item value="search-emoji" keywords="['emoji', 'ícono']">
                            <x-lucide-search class="size-4" />
                            Buscar emoji
                        </x-ui.command.item>
                        <x-ui.command.item value="calculator" keywords="['math', 'calcular']">
                            <x-lucide-plus class="size-4" />
                            Calculadora
                        </x-ui.command.item>
                    </x-ui.command.group>

                    <x-ui.command.separator />

                    <x-ui.command.group heading="Configuración">
                        <x-ui.command.item value="profile" keywords="['cuenta', 'usuario']">
                            <x-lucide-user class="size-4" />
                            Perfil
                            <x-ui.command.shortcut>⌘P</x-ui.command.shortcut>
                        </x-ui.command.item>
                        <x-ui.command.item value="billing" keywords="['pago', 'factura']">
                            <x-lucide-credit-card class="size-4" />
                            Facturación
                            <x-ui.command.shortcut>⌘B</x-ui.command.shortcut>
                        </x-ui.command.item>
                        <x-ui.command.item value="settings" keywords="['opciones', 'preferencias']">
                            <x-lucide-settings class="size-4" />
                            Configuración
                            <x-ui.command.shortcut>⌘S</x-ui.command.shortcut>
                        </x-ui.command.item>
                        <x-ui.command.item value="logout" keywords="['salir', 'cerrar']" disabled>
                            <x-lucide-log-out class="size-4" />
                            Cerrar sesión
                        </x-ui.command.item>
                    </x-ui.command.group>
                </x-ui.command.list>
            </x-ui.command>
        </div>
        <x-ui.typography as="muted">Usa ↑ ↓ para navegar, Enter para seleccionar.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Dialog / Cmd+K --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Dialog (Cmd+K / Ctrl+K)</x-ui.typography>

        <x-ui.command.dialog>
            <x-ui.command>
                <x-ui.command.input placeholder="Escribí un comando..." />
                <x-ui.command.list>
                    <x-ui.command.empty>Sin resultados.</x-ui.command.empty>

                    <x-ui.command.group heading="Acciones rápidas">
                        <x-ui.command.item value="new-doc">
                            <x-lucide-plus class="size-4" />
                            Nuevo documento
                            <x-ui.command.shortcut>⌘N</x-ui.command.shortcut>
                        </x-ui.command.item>
                        <x-ui.command.item value="open">
                            <x-lucide-folder-open class="size-4" />
                            Abrir archivo
                        </x-ui.command.item>
                        <x-ui.command.item value="save">
                            <x-lucide-save class="size-4" />
                            Guardar
                            <x-ui.command.shortcut>⌘S</x-ui.command.shortcut>
                        </x-ui.command.item>
                    </x-ui.command.group>

                    <x-ui.command.separator />

                    <x-ui.command.group heading="Navegación">
                        <x-ui.command.item value="home" href="/showcase">
                            <x-lucide-home class="size-4" />
                            Inicio
                        </x-ui.command.item>
                        <x-ui.command.item value="settings-page" href="/showcase/components/typography">
                            <x-lucide-settings class="size-4" />
                            Tipografía
                        </x-ui.command.item>
                    </x-ui.command.group>
                </x-ui.command.list>
            </x-ui.command>
        </x-ui.command.dialog>

        <x-ui.button variant="outline" @click="$dispatch('keydown', { key: 'k', metaKey: true, bubbles: true })"
            x-data x-on:click="$root.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', metaKey: true, bubbles: true }))">
            Abrir paleta de comandos
            <x-ui.kbd>⌘K</x-ui.kbd>
        </x-ui.button>
        <x-ui.typography as="muted">O presioná <x-ui.kbd>⌘K</x-ui.kbd> / <x-ui.kbd>Ctrl+K</x-ui.kbd> en cualquier momento.</x-ui.typography>
    </section>

    <x-ui.separator />

    {{-- Sin grupos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sin grupos</x-ui.typography>
        <div class="max-w-xs">
            <x-ui.command>
                <x-ui.command.input placeholder="Buscar framework..." />
                <x-ui.command.list>
                    <x-ui.command.empty>No encontrado.</x-ui.command.empty>
                    @foreach(['Laravel', 'Rails', 'Django', 'Express', 'NestJS', 'Spring Boot', 'Phoenix'] as $fw)
                        <x-ui.command.item value="{{ strtolower($fw) }}">{{ $fw }}</x-ui.command.item>
                    @endforeach
                </x-ui.command.list>
            </x-ui.command>
        </div>
    </section>

</div>
</x-layouts.showcase>
