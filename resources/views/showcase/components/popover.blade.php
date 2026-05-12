<x-layouts.showcase title="Popover — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Popover</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Panel flotante anclado a un trigger. Se cierra con Escape o al hacer click afuera. Detecta automáticamente el espacio disponible y flipa la posición si es necesario.</x-ui.typography>
    </div>

    {{-- ── Básico ────────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Básico</x-ui.typography>
        <x-ui.popover>
            <x-slot:trigger>
                <x-ui.button variant="outline">Abrir popover</x-ui.button>
            </x-slot:trigger>
            <div class="space-y-2">
                <x-ui.typography as="p" class="font-semibold">Configuración</x-ui.typography>
                <x-ui.typography as="muted">Ajustá las opciones de esta sección.</x-ui.typography>
            </div>
        </x-ui.popover>
    </section>

    <x-ui.separator />

    {{-- ── Posiciones ────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Posiciones</x-ui.typography>
        <div class="flex flex-wrap gap-4 py-16 justify-center">
            @foreach([
                ['top', 'start'],
                ['top', 'center'],
                ['top', 'end'],
                ['bottom', 'start'],
                ['bottom', 'center'],
                ['bottom', 'end'],
                ['left', 'start'],
                ['left', 'center'],
                ['right', 'start'],
                ['right', 'center'],
            ] as [$side, $align])
                <x-ui.popover :side="$side" :align="$align">
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm">{{ $side }} · {{ $align }}</x-ui.button>
                    </x-slot:trigger>
                    <x-ui.typography as="muted">side: <span class="font-medium text-foreground">{{ $side }}</span> / align: <span class="font-medium text-foreground">{{ $align }}</span></x-ui.typography>
                </x-ui.popover>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Menú contextual ──────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Menú contextual</x-ui.typography>
        <x-ui.popover width="w-48">
            <x-slot:trigger>
                <x-ui.button variant="outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM18.75 12a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                    </svg>
                    Más opciones
                </x-ui.button>
            </x-slot:trigger>
            <div class="space-y-0.5 -mx-1">
                @foreach([
                    ['Editar',     'M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931zm0 0L19.5 7.125'],
                    ['Duplicar',   'M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75'],
                    ['Archivar',   'M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                ] as [$label, $path])
                    <button class="flex items-center gap-2 w-full text-left px-2 py-1.5 text-sm rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-muted-foreground shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                        {{ $label }}
                    </button>
                @endforeach

                <div class="my-1 -mx-1 h-px bg-border"></div>

                <button class="flex items-center gap-2 w-full text-left px-2 py-1.5 text-sm rounded-md text-destructive hover:bg-destructive-subtle hover:text-destructive-subtle-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                    </svg>
                    Eliminar
                </button>
            </div>
        </x-ui.popover>
    </section>

    <x-ui.separator />

    {{-- ── Perfil de usuario ────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Perfil de usuario</x-ui.typography>
        <x-ui.typography as="muted">Patrón típico de navbar: avatar como trigger, menu de cuenta al desplegarse.</x-ui.typography>

        <x-ui.popover side="bottom" align="end" width="w-64">
            <x-slot:trigger>
                <button class="flex items-center gap-2.5 rounded-lg border border-border px-2 py-1.5 hover:bg-accent transition-colors text-sm">
                    <x-ui.avatar fallback="MG" size="sm" />
                    <div class="text-left hidden sm:block">
                        <x-ui.typography as="p" class="font-medium leading-tight">María González</x-ui.typography>
                        <x-ui.typography as="muted" class="text-xs leading-tight">Admin</x-ui.typography>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5 text-muted-foreground ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
            </x-slot:trigger>

            {{-- Cabecera con info --}}
            <div class="flex items-center gap-3 pb-3">
                <x-ui.avatar fallback="MG" />
                <div class="min-w-0">
                    <x-ui.typography as="p" class="font-semibold truncate">María González</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs truncate">m.gonzalez@empresa.com</x-ui.typography>
                </div>
            </div>

            <div class="-mx-4 h-px bg-border mb-2"></div>

            <div class="space-y-0.5 -mx-1">
                @foreach([
                    ['Mi perfil',      'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0zM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                    ['Equipo',         'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0z'],
                    ['Facturación',    'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5z'],
                    ['Configuración',  'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z'],
                ] as [$label, $path])
                    <button class="flex items-center gap-2.5 w-full text-left px-2 py-1.5 text-sm rounded-md hover:bg-accent hover:text-accent-foreground transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-muted-foreground shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}"/>
                        </svg>
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="-mx-4 h-px bg-border my-2"></div>

            <div class="-mx-1">
                <button class="flex items-center gap-2.5 w-full text-left px-2 py-1.5 text-sm rounded-md text-destructive hover:bg-destructive-subtle hover:text-destructive-subtle-foreground transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
                    </svg>
                    Cerrar sesión
                </button>
            </div>
        </x-ui.popover>
    </section>

    <x-ui.separator />

    {{-- ── Filtros avanzados ────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Filtros avanzados</x-ui.typography>
        <x-ui.typography as="muted">El trigger muestra un badge con la cantidad de filtros activos. El panel tiene secciones con checkboxes y acciones de limpiar/aplicar.</x-ui.typography>

        <div
            x-data="{
                selected: { estado: [], prioridad: [] },
                get count() { return this.selected.estado.length + this.selected.prioridad.length; },
                toggle(group, val) {
                    const arr = this.selected[group];
                    const i = arr.indexOf(val);
                    i === -1 ? arr.push(val) : arr.splice(i, 1);
                },
                clear() { this.selected = { estado: [], prioridad: [] }; }
            }"
            class="inline-flex"
        >
            <x-ui.popover width="w-72">
                <x-slot:trigger>
                    <button class="inline-flex items-center gap-2 rounded-md border border-border bg-background px-3 py-2 text-sm font-medium hover:bg-accent transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3z"/>
                        </svg>
                        Filtros
                        <span
                            x-show="count > 0"
                            x-text="count"
                            x-cloak
                            class="inline-flex items-center justify-center size-5 rounded-full bg-primary text-[10px] font-semibold text-primary-foreground"
                        ></span>
                    </button>
                </x-slot:trigger>

                <div class="space-y-4">
                    {{-- Sección Estado --}}
                    <div>
                        <x-ui.typography as="section-label" class="mb-2 tracking-wider">Estado</x-ui.typography>
                        <div class="space-y-2">
                            @foreach([
                                ['activo',    'Activo',    'success'],
                                ['inactivo',  'Inactivo',  'default'],
                                ['pendiente', 'Pendiente', 'warning'],
                                ['archivado', 'Archivado', 'default'],
                            ] as [$val, $label, $variant])
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        @change="toggle('estado', '{{ $val }}')"
                                        :checked="selected.estado.includes('{{ $val }}')"
                                        class="size-4 rounded border-border text-primary cursor-pointer accent-primary"
                                    >
                                    <span class="text-sm group-hover:text-foreground transition-colors">{{ $label }}</span>
                                    <span class="ml-auto h-2 w-2 rounded-full
                                        {{ $variant === 'success' ? 'bg-success' : '' }}
                                        {{ $variant === 'warning' ? 'bg-warning' : '' }}
                                        {{ $variant === 'default' ? 'bg-muted-foreground/40' : '' }}
                                    "></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="-mx-4 h-px bg-border"></div>

                    {{-- Sección Prioridad --}}
                    <div>
                        <x-ui.typography as="section-label" class="mb-2 tracking-wider">Prioridad</x-ui.typography>
                        <div class="space-y-2">
                            @foreach([
                                ['alta',   'Alta',   'bg-destructive'],
                                ['media',  'Media',  'bg-warning'],
                                ['baja',   'Baja',   'bg-info'],
                            ] as [$val, $label, $color])
                                <label class="flex items-center gap-2.5 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        @change="toggle('prioridad', '{{ $val }}')"
                                        :checked="selected.prioridad.includes('{{ $val }}')"
                                        class="size-4 rounded border-border cursor-pointer accent-primary"
                                    >
                                    <span class="text-sm group-hover:text-foreground transition-colors">{{ $label }}</span>
                                    <span class="ml-auto h-2 w-2 rounded-full {{ $color }}"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="-mx-4 h-px bg-border"></div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-0">
                        <button
                            @click="clear()"
                            :class="count === 0 ? 'opacity-40 pointer-events-none' : ''"
                            class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                        >
                            Limpiar todo
                        </button>
                        <x-ui.button size="sm">Aplicar filtros</x-ui.button>
                    </div>
                </div>
            </x-ui.popover>
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Notificaciones ───────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Centro de notificaciones</x-ui.typography>
        <x-ui.typography as="muted">Trigger con badge de no leídas. Panel con lista y acción de marcar todo como leído.</x-ui.typography>

        <div
            x-data="{
                notifications: [
                    { id: 1, name: 'Lucas Herrera',   initials: 'LH', message: 'Comentó en el documento Propuesta Q4.', time: 'hace 2 min',  unread: true  },
                    { id: 2, name: 'Sistema',          initials: 'S',  message: 'Tu exportación está lista para descargar.', time: 'hace 18 min', unread: true  },
                    { id: 3, name: 'Valentina López',  initials: 'VL', message: 'Te asignó la tarea «Revisar contratos».', time: 'hace 1 h',  unread: true  },
                    { id: 4, name: 'Camila Romero',    initials: 'CR', message: 'Aceptó la invitación al equipo.', time: 'ayer',         unread: false },
                    { id: 5, name: 'Diego Martínez',   initials: 'DM', message: 'Compartió el archivo Diseño final.png.', time: 'ayer',     unread: false },
                ],
                get unread() { return this.notifications.filter(n => n.unread).length; },
                markAllRead() { this.notifications.forEach(n => n.unread = false); }
            }"
            class="inline-flex"
        >
            <x-ui.popover side="bottom" align="end" width="w-80">
                <x-slot:trigger>
                    <button class="relative inline-flex items-center justify-center size-9 rounded-md border border-border bg-background hover:bg-accent transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                        <span
                            x-show="unread > 0"
                            x-text="unread"
                            x-cloak
                            class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[16px] h-4 px-1 rounded-full bg-destructive text-[9px] font-bold text-destructive-foreground"
                        ></span>
                    </button>
                </x-slot:trigger>

                {{-- Header --}}
                <div class="-mx-4 -mt-4 px-4 py-3 border-b border-border flex items-center justify-between mb-0">
                    <div class="flex items-center gap-2">
                        <x-ui.typography as="p" class="font-semibold">Notificaciones</x-ui.typography>
                        <span x-show="unread > 0" x-cloak class="inline-flex items-center justify-center size-5 rounded-full bg-primary/10 text-primary text-xs font-semibold" x-text="unread"></span>
                    </div>
                    <button
                        @click="markAllRead()"
                        :class="unread === 0 ? 'opacity-40 pointer-events-none' : ''"
                        class="text-xs text-muted-foreground hover:text-foreground transition-colors"
                    >
                        Marcar todas leídas
                    </button>
                </div>

                {{-- Lista --}}
                <div class="-mx-4 divide-y divide-border">
                    <template x-for="n in notifications" :key="n.id">
                        <button
                            @click="n.unread = false"
                            :class="n.unread ? 'bg-primary/[0.04] hover:bg-primary/[0.07]' : 'hover:bg-accent'"
                            class="flex items-start gap-3 w-full text-left px-4 py-3 transition-colors"
                        >
                            <div
                                class="shrink-0 size-8 rounded-full bg-muted flex items-center justify-center text-xs font-semibold text-muted-foreground"
                                x-text="n.initials"
                            ></div>
                            <div class="min-w-0 flex-1">
                                <x-ui.typography as="p" class="text-xs font-medium leading-snug" x-text="n.name"></x-ui.typography>
                                <x-ui.typography as="muted" class="text-xs leading-snug line-clamp-2 mt-0.5" x-text="n.message"></x-ui.typography>
                                <x-ui.typography as="muted" class="text-[10px] text-muted-foreground/70 mt-1" x-text="n.time"></x-ui.typography>
                            </div>
                            <span
                                x-show="n.unread"
                                class="mt-1.5 shrink-0 size-1.5 rounded-full bg-primary"
                            ></span>
                        </button>
                    </template>
                </div>

                {{-- Footer --}}
                <div class="-mx-4 -mb-4 px-4 py-3 border-t border-border">
                    <button class="w-full text-center text-xs text-muted-foreground hover:text-foreground transition-colors">
                        Ver todas las notificaciones
                    </button>
                </div>
            </x-ui.popover>
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Compartir enlace ─────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Compartir enlace</x-ui.typography>
        <x-ui.typography as="muted">Panel para copiar un enlace público y configurar permisos de acceso.</x-ui.typography>

        <x-ui.popover side="bottom" align="start" width="w-80">
            <x-slot:trigger>
                <x-ui.button variant="outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185z"/>
                    </svg>
                    Compartir
                </x-ui.button>
            </x-slot:trigger>

            <div
                class="space-y-4"
                x-data="{
                    url: 'https://app.empresa.com/docs/propuesta-q4',
                    copied: false,
                    permission: 'read',
                    async copy() {
                        try { await navigator.clipboard.writeText(this.url); } catch {}
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                }"
            >
                {{-- Link público --}}
                <div>
                    <x-ui.typography as="section-label" class="mb-2 tracking-wider">Enlace público</x-ui.typography>
                    <div class="flex gap-1.5">
                        <input
                            type="text"
                            :value="url"
                            readonly
                            class="flex-1 min-w-0 rounded-md border border-border bg-muted px-3 py-1.5 text-xs text-muted-foreground font-mono truncate focus:outline-none"
                        >
                        <button
                            @click="copy()"
                            :class="copied ? 'bg-success text-success-foreground border-success' : 'bg-background hover:bg-accent border-border'"
                            class="shrink-0 inline-flex items-center gap-1.5 rounded-md border px-2.5 py-1.5 text-xs font-medium transition-colors"
                        >
                            <svg x-show="!copied" xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/>
                            </svg>
                            <svg x-show="copied" x-cloak xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                            <span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                        </button>
                    </div>
                </div>

                <div class="-mx-4 h-px bg-border"></div>

                {{-- Permisos --}}
                <div>
                    <x-ui.typography as="section-label" class="mb-2 tracking-wider">Permisos de acceso</x-ui.typography>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach([
                            ['read',  'Solo lectura',  'Puede ver el documento'],
                            ['write', 'Puede editar',  'Puede modificar el contenido'],
                        ] as [$val, $label, $desc])
                            <button
                                @click="permission = '{{ $val }}'"
                                :class="permission === '{{ $val }}' ? 'border-primary bg-primary/5 text-primary' : 'border-border hover:border-border/80 hover:bg-accent'"
                                class="flex flex-col items-start rounded-md border p-2.5 text-left transition-colors"
                            >
                                <span class="text-xs font-medium">{{ $label }}</span>
                                <span class="text-[11px] text-muted-foreground mt-0.5 leading-snug">{{ $desc }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="-mx-4 h-px bg-border"></div>

                {{-- Invitar --}}
                <div>
                    <x-ui.typography as="section-label" class="mb-2 tracking-wider">Invitar personas</x-ui.typography>
                    <div class="flex gap-1.5">
                        <x-ui.input
                            type="email"
                            placeholder="email@empresa.com"
                            size="sm"
                            class="flex-1 min-w-0"
                        />
                        <x-ui.button size="sm">Invitar</x-ui.button>
                    </div>
                    <x-ui.typography as="muted" class="text-[11px] mt-2">Recibirán un email con acceso <span x-text="permission === 'read' ? 'de solo lectura' : 'para editar'"></span>.</x-ui.typography>
                </div>
            </div>
        </x-ui.popover>
    </section>

</div>
</x-layouts.showcase>
