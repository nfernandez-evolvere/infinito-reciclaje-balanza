<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }} — Dashboard</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Previene flash de tema incorrecto al cargar la página --}}
    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body
    x-data="{
        dark: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches),
        collapsed: localStorage.getItem('sidebar') === 'collapsed',
        sidebarOpen: false,

        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.dark);
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar', this.collapsed ? 'collapsed' : 'expanded');
        }
    }"
    class="min-h-screen bg-background text-foreground antialiased"
>

{{-- Mobile overlay --}}
<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-black/50 lg:hidden"
    x-cloak
></div>

{{-- Sidebar --}}
<aside
    :class="[collapsed ? 'w-16' : 'w-64', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-card border-r transition-all duration-300"
>
    {{-- Logo + Collapse button --}}
    <div class="flex h-16 items-center border-b px-3 gap-2">
        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-primary text-primary-foreground text-xs font-bold">
            A
        </div>
        <span x-show="!collapsed" x-cloak class="font-semibold text-sm flex-1 truncate">
            {{ config('app.name') }}
        </span>
        <button
            @click="toggleCollapse()"
            class="hidden lg:flex h-7 w-7 shrink-0 items-center justify-center rounded-md hover:bg-accent transition-colors text-muted-foreground"
            :aria-label="collapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
            :title="collapsed ? 'Expandir sidebar' : 'Colapsar sidebar'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                :class="collapsed ? 'rotate-180' : ''"
                class="transition-transform duration-300">
                <path d="m15 18-6-6 6-6"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden p-2 space-y-0.5">

        @php
        $groups = [
            'Operación' => [
                ['icon' => 'home',      'label' => 'Dashboard',          'href' => '/admin/dashboard',      'active' => request()->is('admin/dashboard')],
                ['icon' => 'list',      'label' => 'Pesajes',            'href' => '/admin/pesajes',        'active' => request()->is('admin/pesajes')],
            ],
            'Transporte' => [
                ['icon' => 'truck',     'label' => 'Vehículos',          'href' => '/admin/vehiculos',      'active' => request()->is('admin/vehiculos*')],
                ['icon' => 'car',       'label' => 'Tipos de vehículo',  'href' => '/admin/tipos-vehiculo', 'active' => request()->is('admin/tipos-vehiculo*')],
            ],
            'Orígenes' => [
                ['icon' => 'map-pin',   'label' => 'Orígenes',           'href' => '/admin/origenes',       'active' => request()->is('admin/origenes*')],
            ],
            'Servicios' => [
                ['icon' => 'layers',    'label' => 'Tipos de servicio',  'href' => '/admin/servicios',      'active' => request()->is('admin/servicios*')],
            ],
            'Sistema' => [
                ['icon' => 'users-2',   'label' => 'Usuarios',           'href' => '/admin/usuarios',       'active' => request()->is('admin/usuarios*')],
            ],
            'Análisis' => [
                ['icon' => 'bar-chart', 'label' => 'Reportes',           'href' => '/admin/reportes',       'active' => request()->is('admin/reportes*')],
            ],
        ];

        $svgPaths = [
            'home'      => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
            'list'      => '<line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/>',
            'truck'     => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
            'car'       => '<path d="M19 17H5a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2z"/><path d="M3 9h18"/><circle cx="7.5" cy="17" r="1.5"/><circle cx="16.5" cy="17" r="1.5"/>',
            'map-pin'   => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'layers'    => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
            'users-2'   => '<path d="M14 19a6 6 0 0 0-12 0"/><circle cx="8" cy="9" r="4"/><path d="M22 19a6 6 0 0 0-6-6 4 4 0 1 0 0-8"/>',
            'bar-chart' => '<line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/>',
        ];
        @endphp

        @foreach($groups as $groupLabel => $items)
            <p x-show="!collapsed" x-cloak class="px-3 pt-3 pb-1 text-xs font-medium text-muted-foreground uppercase tracking-wide">
                {{ $groupLabel }}
            </p>
            <div x-show="collapsed" x-cloak class="my-2 h-px bg-border mx-2"></div>

            @foreach($items as $item)
            <div class="relative group/nav">
                <a
                    href="{{ $item['href'] }}"
                    :class="collapsed ? 'justify-center px-0 w-10 mx-auto' : 'px-3'"
                    class="flex items-center gap-3 rounded-md py-2 text-sm transition-colors {{ ($item['active'] ?? false) ? 'bg-accent text-accent-foreground font-medium' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground' }}"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                        {!! $svgPaths[$item['icon']] !!}
                    </svg>
                    <span x-show="!collapsed" x-cloak class="flex-1 truncate">{{ $item['label'] }}</span>
                    @if(isset($item['badge']))
                        <span x-show="!collapsed" x-cloak class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-muted px-1.5 text-xs font-medium text-muted-foreground">
                            {{ $item['badge'] }}
                        </span>
                    @endif
                </a>
                {{-- Tooltip cuando está colapsado --}}
                <div
                    x-show="collapsed"
                    x-cloak
                    class="pointer-events-none absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-md bg-popover border px-2.5 py-1.5 text-xs font-medium shadow-md opacity-0 transition-opacity group-hover/nav:opacity-100"
                >
                    {{ $item['label'] }}
                    @if(isset($item['badge']))
                        <span class="ml-1 rounded-full bg-muted px-1.5 py-0.5">{{ $item['badge'] }}</span>
                    @endif
                </div>
            </div>
            @endforeach
        @endforeach

    </nav>

    {{-- User footer --}}
    <div class="border-t p-2">
        <x-dropdown-menu>
            <x-dropdown-menu.trigger
                :class="collapsed ? 'justify-center px-0 w-10 mx-auto' : 'px-3'"
                class="flex w-full items-center gap-3 rounded-md py-2 text-sm hover:bg-accent transition-colors"
            >
                <x-avatar fallback="NR" class="h-7 w-7 shrink-0 text-xs" />
                <div x-show="!collapsed" x-cloak class="flex-1 text-left min-w-0">
                    <p class="font-medium leading-none truncate">Nicolás Ramírez</p>
                    <p class="text-xs text-muted-foreground mt-0.5 truncate">nico@ejemplo.com</p>
                </div>
                <svg x-show="!collapsed" x-cloak xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-muted-foreground"><path d="m6 9 6 6 6-6"/></svg>
            </x-dropdown-menu.trigger>
            <x-dropdown-menu.content class="w-56" side="top">
                <x-dropdown-menu.label>Mi cuenta</x-dropdown-menu.label>
                <x-dropdown-menu.separator />
                <x-dropdown-menu.item>Perfil</x-dropdown-menu.item>
                <x-dropdown-menu.item>Facturación</x-dropdown-menu.item>
                <x-dropdown-menu.item>Configuración</x-dropdown-menu.item>
                <x-dropdown-menu.separator />
                <x-dropdown-menu.item :destructive="true">Cerrar sesión</x-dropdown-menu.item>
            </x-dropdown-menu.content>
        </x-dropdown-menu>
    </div>
</aside>

{{-- Main wrapper --}}
<div
    :class="collapsed ? 'lg:pl-16' : 'lg:pl-64'"
    class="flex flex-col min-h-screen transition-all duration-300"
>
    {{-- Topbar --}}
    <header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b bg-card px-4 sm:px-6">

        {{-- Mobile toggle --}}
        <button @click="sidebarOpen = true" class="lg:hidden p-1.5 rounded-md hover:bg-accent" aria-label="Abrir navegación">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>

        {{-- Breadcrumb --}}
        <div class="flex-1 hidden sm:block">
            {{ $breadcrumb ?? '' }}
        </div>

        {{-- Right actions --}}
        <div class="flex items-center gap-1.5 ml-auto">

            {{-- Search --}}
            <div class="relative hidden md:block">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <x-input placeholder="Buscar..." class="pl-9 w-52 h-8 text-xs" />
            </div>

            {{-- Theme toggle --}}
            <x-button variant="ghost" size="icon" @click="toggleDark()" class="h-8 w-8" x-bind:aria-label="dark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'">
                {{-- Sol (dark mode activo → mostrar sol para volver a light) --}}
                <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                {{-- Luna (light mode activo → mostrar luna para pasar a dark) --}}
                <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            </x-button>

            {{-- Notifications --}}
            <x-sheet>
                <x-sheet.trigger aria-label="Notificaciones" class="relative inline-flex h-8 w-8 items-center justify-center rounded-md hover:bg-accent transition-colors text-foreground">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                    <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-destructive"></span>
                </x-sheet.trigger>
                <x-sheet.content side="right" class="w-80">
                    <div class="mt-2 space-y-1">
                        <p class="text-base font-semibold">Notificaciones</p>
                        <p class="text-sm text-muted-foreground">3 sin leer</p>
                    </div>
                    <x-separator class="my-4" />
                    <div class="space-y-3">
                        @foreach([
                            ['title' => 'Nuevo pedido #1042',   'desc' => 'Ana García realizó un pedido de $240',       'time' => 'Hace 5 min',  'unread' => true],
                            ['title' => 'Pago confirmado',       'desc' => 'El pago del pedido #1041 fue procesado',     'time' => 'Hace 23 min', 'unread' => true],
                            ['title' => 'Stock bajo',            'desc' => '"Auriculares Pro" tiene 3 unidades',         'time' => 'Hace 1 h',    'unread' => true],
                            ['title' => 'Reseña nueva',          'desc' => 'Carlos dejó una reseña de 5 estrellas',      'time' => 'Hace 3 h',    'unread' => false],
                            ['title' => 'Exportación lista',     'desc' => 'Tu reporte de ventas está disponible',       'time' => 'Ayer',        'unread' => false],
                        ] as $notif)
                        <div class="flex gap-3 rounded-lg p-2 {{ $notif['unread'] ? 'bg-accent' : '' }}">
                            <div class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notif['unread'] ? 'bg-primary' : 'bg-transparent' }}"></div>
                            <div class="flex-1 space-y-0.5">
                                <p class="text-sm font-medium leading-none">{{ $notif['title'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $notif['desc'] }}</p>
                                <p class="text-xs text-muted-foreground">{{ $notif['time'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </x-sheet.content>
            </x-sheet>

        </div>
    </header>

    {{-- Page content --}}
    <main class="flex-1 p-4 sm:p-6">
        {{ $slot }}
    </main>

</div>

<x-toast />
</body>
</html>
