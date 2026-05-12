<x-layouts.showcase title="Menubar — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Menubar</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Barra de menús horizontal estilo app de escritorio. Al abrir un menú y mover el cursor a otro trigger, éste se abre automáticamente.</x-ui.typography>
    </div>

    {{-- ── 1. Editor de código ─────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Editor de código</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">Shortcuts, sub-menús anidados, checkboxes y radio groups — como VS Code.</x-ui.typography>

        <x-ui.menubar>

            {{-- Archivo --}}
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Archivo</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        <x-lucide-plus />
                        Nuevo archivo
                        <x-ui.menubar.shortcut>⌘N</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-folder-open />
                        Abrir archivo...
                        <x-ui.menubar.shortcut>⌘O</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger inset>Abrir recientes</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>app/Models/User.php</x-ui.menubar.item>
                            <x-ui.menubar.item>routes/web.php</x-ui.menubar.item>
                            <x-ui.menubar.item>resources/css/app.css</x-ui.menubar.item>
                            <x-ui.menubar.separator />
                            <x-ui.menubar.item>Limpiar recientes</x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-save />
                        Guardar
                        <x-ui.menubar.shortcut>⌘S</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Guardar como...
                        <x-ui.menubar.shortcut>⇧⌘S</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Guardar todo
                        <x-ui.menubar.shortcut>⌥⌘S</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.checkbox-item :checked="true" :closeOnClick="false">
                        Guardado automático
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>Revertir archivo</x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Cerrar editor
                        <x-ui.menubar.shortcut>⌘W</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item disabled>Cerrar carpeta</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            {{-- Edición --}}
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Edición</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        Deshacer
                        <x-ui.menubar.shortcut>⌘Z</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Rehacer
                        <x-ui.menubar.shortcut>⇧⌘Z</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-copy />
                        Cortar
                        <x-ui.menubar.shortcut>⌘X</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-copy />
                        Copiar
                        <x-ui.menubar.shortcut>⌘C</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Pegar
                        <x-ui.menubar.shortcut>⌘V</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-search />
                        Buscar
                        <x-ui.menubar.shortcut>⌘F</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Reemplazar
                        <x-ui.menubar.shortcut>⌥⌘F</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Buscar en archivos
                        <x-ui.menubar.shortcut>⇧⌘F</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>Selección</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>
                                Seleccionar todo
                                <x-ui.menubar.shortcut>⌘A</x-ui.menubar.shortcut>
                            </x-ui.menubar.item>
                            <x-ui.menubar.item>Expandir selección</x-ui.menubar.item>
                            <x-ui.menubar.item>Contraer selección</x-ui.menubar.item>
                            <x-ui.menubar.separator />
                            <x-ui.menubar.item>
                                Agregar cursor arriba
                                <x-ui.menubar.shortcut>⌥↑</x-ui.menubar.shortcut>
                            </x-ui.menubar.item>
                            <x-ui.menubar.item>
                                Agregar cursor abajo
                                <x-ui.menubar.shortcut>⌥↓</x-ui.menubar.shortcut>
                            </x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Formatear documento
                        <x-ui.menubar.shortcut>⇧⌥F</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            {{-- Vista --}}
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Vista</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.label>Panel</x-ui.menubar.label>
                    <x-ui.menubar.checkbox-item :checked="true" :closeOnClick="false">
                        Mostrar barra lateral
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.checkbox-item :checked="true" :closeOnClick="false">
                        Mostrar minimap
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.checkbox-item :closeOnClick="false">
                        Mostrar espacio en blanco
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.checkbox-item :checked="true" :closeOnClick="false">
                        Mostrar barra de estado
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.label>Zoom</x-ui.menubar.label>
                    <x-ui.menubar.radio-group value="100" name="zoom">
                        <x-ui.menubar.radio-item value="90">90%</x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="100">100%</x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="125">125%</x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="150">150%</x-ui.menubar.radio-item>
                    </x-ui.menubar.radio-group>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Pantalla completa
                        <x-ui.menubar.shortcut>F11</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            {{-- Ir a --}}
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Ir a</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        <x-lucide-search />
                        Ir a archivo...
                        <x-ui.menubar.shortcut>⌘P</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Ir a símbolo...
                        <x-ui.menubar.shortcut>⇧⌘O</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Ir a línea/columna...
                        <x-ui.menubar.shortcut>⌃G</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>Definición</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>
                                Ir a definición
                                <x-ui.menubar.shortcut>F12</x-ui.menubar.shortcut>
                            </x-ui.menubar.item>
                            <x-ui.menubar.item>Ir a declaración</x-ui.menubar.item>
                            <x-ui.menubar.item>Ir a tipo</x-ui.menubar.item>
                            <x-ui.menubar.item>Ir a implementación</x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-arrow-left />
                        Atrás
                        <x-ui.menubar.shortcut>⌃-</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-arrow-right />
                        Adelante
                        <x-ui.menubar.shortcut>⌃⇧-</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            {{-- Terminal --}}
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Terminal</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        <x-lucide-plus />
                        Nuevo terminal
                        <x-ui.menubar.shortcut>⌃`</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Terminal externo
                        <x-ui.menubar.shortcut>⇧⌘C</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Dividir terminal
                        <x-ui.menubar.shortcut>⌘\</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>Ejecutar tarea</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>
                                Compilar
                                <x-ui.menubar.shortcut>⇧⌘B</x-ui.menubar.shortcut>
                            </x-ui.menubar.item>
                            <x-ui.menubar.item>Ejecutar pruebas</x-ui.menubar.item>
                            <x-ui.menubar.item>Ejecutar cobertura</x-ui.menubar.item>
                            <x-ui.menubar.separator />
                            <x-ui.menubar.item>Limpiar salida</x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item variant="destructive">Terminar todos</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

        </x-ui.menubar>
    </section>

    <x-ui.separator />

    {{-- ── 2. Menú de cuenta con íconos ───────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Menú de cuenta con íconos</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">Ítems con íconos a la izquierda — patrón típico en menús de usuario, configuración y workspace.</x-ui.typography>

        <x-ui.menubar>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Cuenta</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.label>nicolas@ejemplo.com</x-ui.menubar.label>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-user />
                        Perfil
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-settings />
                        Configuración
                        <x-ui.menubar.shortcut>⌘,</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-bell />
                        Notificaciones
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-credit-card />
                        Facturación
                    </x-ui.menubar.item>
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>
                            <x-lucide-users />
                            Equipo
                        </x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>
                                <x-lucide-plus />
                                Invitar miembro
                            </x-ui.menubar.item>
                            <x-ui.menubar.item>
                                <x-lucide-users />
                                Ver equipo
                            </x-ui.menubar.item>
                            <x-ui.menubar.separator />
                            <x-ui.menubar.item>
                                <x-lucide-settings />
                                Configurar roles
                            </x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item variant="destructive">
                        <x-lucide-log-out />
                        Cerrar sesión
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Workspace</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.label>Entorno activo</x-ui.menubar.label>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.radio-group value="produccion" name="env">
                        <x-ui.menubar.radio-item value="produccion">
                            <x-lucide-circle-check />
                            Producción
                        </x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="staging">
                            <x-lucide-copy />
                            Staging
                        </x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="local">
                            <x-lucide-home />
                            Local
                        </x-ui.menubar.radio-item>
                    </x-ui.menubar.radio-group>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-plus />
                        Nuevo workspace
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Ayuda</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        <x-lucide-info />
                        Documentación
                        <x-ui.menubar.shortcut>F1</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        <x-lucide-external-link />
                        Soporte
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        <x-lucide-triangle-alert />
                        Reportar problema
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item disabled>Versión 1.0.0</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

        </x-ui.menubar>
    </section>

    <x-ui.separator />

    {{-- ── 3. Ejemplo completo ─────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Ejemplo completo</x-ui.typography>

        <x-ui.menubar>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Archivo</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        Nuevo tab
                        <x-ui.menubar.shortcut>⌘T</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Nueva ventana
                        <x-ui.menubar.shortcut>⌘N</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item disabled>Nueva ventana privada</x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>Compartir</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>Correo electrónico</x-ui.menubar.item>
                            <x-ui.menubar.item>Mensaje</x-ui.menubar.item>
                            <x-ui.menubar.item>Notas</x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Imprimir
                        <x-ui.menubar.shortcut>⌘P</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Edición</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>
                        Deshacer
                        <x-ui.menubar.shortcut>⌘Z</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Rehacer
                        <x-ui.menubar.shortcut>⇧⌘Z</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.sub>
                        <x-ui.menubar.sub-trigger>Buscar</x-ui.menubar.sub-trigger>
                        <x-ui.menubar.sub-content>
                            <x-ui.menubar.item>Buscar en la página...</x-ui.menubar.item>
                            <x-ui.menubar.item>Buscar siguiente</x-ui.menubar.item>
                            <x-ui.menubar.item>Buscar anterior</x-ui.menubar.item>
                        </x-ui.menubar.sub-content>
                    </x-ui.menubar.sub>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>Cortar</x-ui.menubar.item>
                    <x-ui.menubar.item>Copiar</x-ui.menubar.item>
                    <x-ui.menubar.item>Pegar</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Ver</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.checkbox-item :checked="true" :closeOnClick="false">
                        Mostrar barra de herramientas
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.checkbox-item :closeOnClick="false">
                        Mostrar barra de estado
                    </x-ui.menubar.checkbox-item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>
                        Acercar
                        <x-ui.menubar.shortcut>⌘+</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.item>
                        Alejar
                        <x-ui.menubar.shortcut>⌘-</x-ui.menubar.shortcut>
                    </x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>Pantalla completa</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Perfiles</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.label>Perfiles</x-ui.menubar.label>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.radio-group value="andy">
                        <x-ui.menubar.radio-item value="andy">Andy</x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="benita">Benita</x-ui.menubar.radio-item>
                        <x-ui.menubar.radio-item value="luis">Luis</x-ui.menubar.radio-item>
                    </x-ui.menubar.radio-group>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item>Editar perfil...</x-ui.menubar.item>
                    <x-ui.menubar.item>Agregar perfil</x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>

        </x-ui.menubar>
    </section>

    <x-ui.separator />

    {{-- ── 4. Ítem destructivo ─────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Ítem destructivo</x-ui.typography>
        <x-ui.menubar>
            <x-ui.menubar.menu>
                <x-ui.menubar.trigger>Cuenta</x-ui.menubar.trigger>
                <x-ui.menubar.content>
                    <x-ui.menubar.item>Configuración</x-ui.menubar.item>
                    <x-ui.menubar.item>Perfil</x-ui.menubar.item>
                    <x-ui.menubar.separator />
                    <x-ui.menubar.item variant="destructive">
                        <x-lucide-log-out />
                        Cerrar sesión
                    </x-ui.menubar.item>
                </x-ui.menubar.content>
            </x-ui.menubar.menu>
        </x-ui.menubar>
    </section>

    <x-ui.separator />

    {{-- ── 5. Loop navigation ──────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Loop navigation</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Con <x-ui.typography as="code">loop</x-ui.typography> en el content, las flechas ↑↓ vuelven al primer/último ítem en vez de quedarse en el borde.
        </x-ui.typography>

        <div class="flex gap-8">
            <div class="space-y-2">
                <x-ui.typography as="muted" class="text-xs">Sin loop (default)</x-ui.typography>
                <x-ui.menubar>
                    <x-ui.menubar.menu>
                        <x-ui.menubar.trigger>Menú</x-ui.menubar.trigger>
                        <x-ui.menubar.content>
                            <x-ui.menubar.item>Elemento 1</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 2</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 3</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 4</x-ui.menubar.item>
                        </x-ui.menubar.content>
                    </x-ui.menubar.menu>
                </x-ui.menubar>
            </div>
            <div class="space-y-2">
                <x-ui.typography as="muted" class="text-xs">Con loop</x-ui.typography>
                <x-ui.menubar>
                    <x-ui.menubar.menu>
                        <x-ui.menubar.trigger>Menú</x-ui.menubar.trigger>
                        <x-ui.menubar.content loop>
                            <x-ui.menubar.item>Elemento 1</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 2</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 3</x-ui.menubar.item>
                            <x-ui.menubar.item>Elemento 4</x-ui.menubar.item>
                        </x-ui.menubar.content>
                    </x-ui.menubar.menu>
                </x-ui.menubar>
            </div>
        </div>
    </section>

</div>
</x-layouts.showcase>
