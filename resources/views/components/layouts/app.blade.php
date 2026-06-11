@props(['title' => 'Panel'])

@php
    $user = auth()->user();

    $operacionItems = [
        ['route' => 'admin.dashboard',      'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ['route' => 'admin.pesajes.index',  'icon' => 'scale',            'label' => 'Pesajes',  'match' => 'admin.pesajes.*'],
        ['route' => 'admin.reportes.index', 'icon' => 'file-bar-chart',   'label' => 'Reportes', 'match' => 'admin.reportes.*'],
    ];

    $alertasNoLeidas = $user?->isAdmin()
        ? app(\App\Repositories\AlertaRepository::class)->countNoLeidas($user->id)
        : 0;

    $configProgress = $user?->isAdmin()
        ? app(\App\Services\ConfiguracionInicialService::class)->getProgress()
        : null;
    $padronItems = [
        ['route' => 'admin.zonas.index',          'icon' => 'map-pin',        'label' => 'Zonas'],
        ['route' => 'admin.tipos-servicio.index', 'icon' => 'clipboard-list', 'label' => 'Servicios'],
        ['route' => 'admin.vehiculos.index',      'icon' => 'truck',          'label' => 'Vehículos'],
    ];
    $sistemaItems = [
        ['route' => 'admin.alertas.index',  'icon' => 'triangle-alert', 'label' => 'Alertas', 'match' => 'admin.alertas.*'],
        ['route' => 'admin.usuarios.index', 'icon' => 'users',          'label' => 'Usuarios'],
    ];
    $operadorItems = [
        ['route' => 'balanza',   'icon' => 'scale', 'label' => 'Pesaje', 'match' => ['balanza', 'pesajes.*']],
        ['route' => 'historial', 'icon' => 'list',  'label' => 'Historial'],
    ];


    $superItems = [
        ['route' => 'super.dashboard',            'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ['route' => 'super.organizaciones.index', 'icon' => 'building-2',       'label' => 'Organizaciones'],
    ];

    $homeRoute = match(true) {
        $user?->isSuperAdmin() => route('super.dashboard'),
        $user?->isAdmin()      => route('admin.dashboard'),
        default                => route('balanza'),
    };

    $section = match(true) {
        request()->routeIs('admin.pesajes.*')                                               => 'Operación',
        request()->routeIs('admin.reportes.*')                                               => 'Reportes',
        request()->routeIs('admin.zonas.*', 'admin.tipos-servicio.*', 'admin.vehiculos.*')  => 'Padrón',
        request()->routeIs('admin.usuarios.*')                                               => 'Sistema',
        default => null,
    };
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="h-dvh overflow-hidden bg-background text-foreground antialiased">

<x-ui.sidebar.provider class="h-dvh">

    {{-- Sidebar --}}
    <x-ui.sidebar collapsible="icon">

        <x-ui.sidebar.header class="h-14 flex-row items-center border-b border-sidebar-border p-0 px-4">
            <a href="{{ $homeRoute }}" class="flex items-center gap-2 min-w-0">
                <div class="size-6 shrink-0 rounded bg-primary flex items-center justify-center">
                    <span class="text-[10px] font-bold text-primary-foreground leading-none">IR</span>
                </div>
                <div class="flex flex-col min-w-0">
                    <span class="text-sm font-semibold text-sidebar-foreground truncate leading-tight">Administración</span>
                    @isset($organizacion)
                        <span class="text-xs text-sidebar-foreground/50 truncate leading-tight">{{ $organizacion->nombre }}</span>
                    @endisset
                </div>
            </a>
        </x-ui.sidebar.header>

        <x-ui.sidebar.content>

            @if($user?->isSuperAdmin())

                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Administración</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($superItems as $item)
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="request()->routeIs($item['route'])"
                                        :tooltip="$item['label']"
                                    >
                                        <span class="{{ request()->routeIs($item['route']) ? 'bg-primary/20 text-primary' : '' }} inline-flex size-8 items-center justify-center rounded-full shrink-0 transition-colors group-hover:bg-primary/20 group-hover:text-primary">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" />
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

            @elseif($user?->isAdmin())

                {{-- Operación --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Operación</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($operacionItems as $item)
                                @php $isActive = request()->routeIs($item['match'] ?? $item['route']); @endphp
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="$isActive"
                                        :tooltip="$item['label']"
                                    >
                                        <span class="{{ $isActive ? 'bg-primary/20 text-primary' : '' }} inline-flex size-8 items-center justify-center rounded-full shrink-0 transition-colors group-hover:bg-primary/20 group-hover:text-primary">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" />
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

                <x-ui.sidebar.separator />

                {{-- Padrón --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Padrón</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($padronItems as $item)
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="request()->routeIs($item['route'])"
                                        :tooltip="$item['label']"
                                    >
                                        <span class="{{ request()->routeIs($item['route']) ? 'bg-primary/20 text-primary' : '' }} inline-flex size-8 items-center justify-center rounded-full shrink-0 transition-colors group-hover:bg-primary/20 group-hover:text-primary">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" />
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

                <x-ui.sidebar.separator />

                {{-- Sistema --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Sistema</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($sistemaItems as $item)
                                @php $isActive = request()->routeIs($item['match'] ?? $item['route']); @endphp
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="$isActive"
                                        :tooltip="$item['label']"
                                    >
                                        <span class="{{ $isActive ? 'bg-primary/20 text-primary' : '' }} inline-flex size-8 items-center justify-center rounded-full shrink-0 transition-colors group-hover:bg-primary/20 group-hover:text-primary">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" />
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

            @elseif($user)

                {{-- Operador --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Operación</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($operadorItems as $item)
                                @php $isActive = request()->routeIs(...(array) ($item['match'] ?? $item['route'])); @endphp
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="$isActive"
                                        :tooltip="$item['label']"
                                    >
                                        <span class="{{ $isActive ? 'bg-primary/20 text-primary' : '' }} inline-flex size-8 items-center justify-center rounded-full shrink-0 transition-colors group-hover:bg-primary/20 group-hover:text-primary">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']" />
                                        </span>
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

            @endif

        </x-ui.sidebar.content>

        {{-- Progreso de configuración inicial --}}
        @if($configProgress && $configProgress['completado'] < $configProgress['total'])
        @php
            $pendientes  = $configProgress['total'] - $configProgress['completado'];
            $completados = array_filter($configProgress['steps'], fn($s) =>  $s['done']);
            $faltantes   = array_filter($configProgress['steps'], fn($s) => !$s['done']);
        @endphp
        <div class="px-2 py-2" x-data="{ sheetOpen: false }">

            {{-- Trigger + hover dropdown --}}
            <x-ui.dropdown-menu side="right" class="block w-full">

                {{-- x-ref="anchor" es requerido por _place() del dropdown --}}
                <div
                    x-ref="anchor"
                    @mouseenter="openHover()"
                    @mouseleave="closeHover()"
                    @click="sheetOpen = true"
                    class="cursor-pointer"
                >
                    {{-- Expandido --}}
                    <div x-show="!isCollapsed" x-cloak
                         class="flex flex-col gap-2 rounded-md border border-primary/40 bg-primary/10 px-3 py-2.5 hover:bg-primary/20 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <x-lucide-list-checks class="size-3.5 shrink-0 text-primary" />
                                <span class="text-xs font-medium text-foreground">Configuración inicial</span>
                            </div>
                            <span class="text-[11px] tabular-nums font-medium text-muted-foreground">
                                {{ $configProgress['completado'] }}/{{ $configProgress['total'] }}
                            </span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-primary/20">
                            <div class="h-full rounded-full bg-primary transition-all duration-500"
                                 style="width: {{ $configProgress['porcentaje'] }}%"></div>
                        </div>
                        <p class="text-[11px] leading-none text-muted-foreground">
                            {{ $pendientes }} paso{{ $pendientes !== 1 ? 's' : '' }} pendiente{{ $pendientes !== 1 ? 's' : '' }}
                        </p>
                    </div>

                    {{-- Colapsado --}}
                    <div x-show="isCollapsed" x-cloak class="flex justify-center">
                        <div class="relative flex size-9 items-center justify-center rounded-md bg-primary/10 hover:bg-primary/20 transition-colors">
                            <x-lucide-list-checks class="size-4 text-primary" />
                            <span class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-primary text-[9px] font-bold leading-none text-primary-foreground">
                                {{ $configProgress['completado'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Hover popover content --}}
                <x-ui.dropdown-menu.content
                    @mouseenter="clearTimeout(_ht); open = true"
                    @mouseleave="closeHover()"
                    class="w-56"
                >
                    <x-ui.dropdown-menu.label>Configuración inicial</x-ui.dropdown-menu.label>
                    <x-ui.dropdown-menu.separator />

                    @foreach($completados as $step)
                        <x-ui.dropdown-menu.item :disabled="true">
                            <x-lucide-circle-check class="size-4 text-success" />
                            <span>{{ $step['label'] }}</span>
                        </x-ui.dropdown-menu.item>
                    @endforeach

                    @if(count($completados) > 0 && count($faltantes) > 0)
                        <x-ui.dropdown-menu.separator />
                    @endif

                    @foreach($faltantes as $step)
                        <x-ui.dropdown-menu.item :href="route($step['route'], $step['params'] ?: [])">
                            <x-lucide-circle class="size-4 text-muted-foreground/50" />
                            <span class="flex-1">{{ $step['label'] }}</span>
                            <x-lucide-arrow-right class="size-3 text-muted-foreground/50" />
                        </x-ui.dropdown-menu.item>
                    @endforeach

                    <x-ui.dropdown-menu.separator />
                    <x-ui.dropdown-menu.label class="text-[11px] font-normal">Clic para ver detalles</x-ui.dropdown-menu.label>
                </x-ui.dropdown-menu.content>

            </x-ui.dropdown-menu>

            {{-- Sheet de detalle --}}
            <x-ui.sheet controlled-by="sheetOpen" side="right">
                <div class="flex h-full flex-col">

                    <div class="flex items-center justify-between border-b border-border p-6 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-primary/10">
                                <x-lucide-list-checks class="size-4 text-primary" />
                            </div>
                            <div>
                                <h2 class="text-h4">Configuración inicial</h2>
                                <p class="text-caption mt-0.5">
                                    {{ $configProgress['completado'] }} de {{ $configProgress['total'] }} pasos completados
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="px-6 py-4 border-b border-border">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-muted-foreground">Progreso general</span>
                            <span class="text-xs font-semibold tabular-nums">{{ $configProgress['porcentaje'] }}%</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                            <div class="h-full rounded-full bg-primary transition-all duration-500"
                                 style="width: {{ $configProgress['porcentaje'] }}%"></div>
                        </div>
                    </div>

                    {{-- Steps --}}
                    <div class="flex-1 overflow-y-auto p-6 space-y-6">

                        @if(count($completados) > 0)
                        <div class="space-y-2">
                            <p class="text-overline">Completados</p>
                            <ul class="space-y-1.5">
                                @foreach($completados as $step)
                                <li class="flex items-center gap-3 rounded-lg bg-success/5 border border-success/20 px-3 py-2.5">
                                    <x-lucide-circle-check class="size-4 shrink-0 text-success" />
                                    <span class="text-sm font-medium text-foreground">{{ $step['label'] }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                        @if(count($faltantes) > 0)
                        <div class="space-y-2">
                            <p class="text-overline">Pendientes</p>
                            <ul class="space-y-1.5">
                                @foreach($faltantes as $step)
                                <li>
                                    <a
                                        href="{{ route($step['route'], $step['params'] ?: []) }}"
                                        @click="sheetOpen = false"
                                        class="group flex items-center gap-3 rounded-lg border border-border px-3 py-2.5 hover:bg-accent hover:border-accent transition-colors"
                                    >
                                        <x-lucide-circle class="size-4 shrink-0 text-muted-foreground/40 group-hover:text-muted-foreground transition-colors" />
                                        <span class="flex-1 text-sm text-muted-foreground group-hover:text-foreground transition-colors">{{ $step['label'] }}</span>
                                        <x-lucide-arrow-right class="size-3.5 shrink-0 text-muted-foreground/40 group-hover:text-muted-foreground transition-colors" />
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif

                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-border p-4">
                        <x-ui.button
                            class="w-full gap-2"
                            @click="sheetOpen = false"
                            tag="a"
                            href="{{ route('manual.show', 'configuracion-inicial') }}"
                        >
                            <x-lucide-book-open class="size-4" />
                            Ver guía completa
                        </x-ui.button>
                    </div>

                </div>
            </x-ui.sheet>

        </div>
        @endif

        {{-- User footer --}}
        @if($user)
        <x-ui.sidebar.footer class="border-t border-sidebar-border p-2">
            <div x-data="{
                    open: false,
                    px: 0, py: 0, pw: 0,
                    toggle() {
                        if (this.open) { this.open = false; return }
                        const r = this.$refs.trigger.getBoundingClientRect()
                        this.px = r.left
                        this.py = r.top - 8
                        this.pw = Math.max(224, r.width)
                        this.open = true
                    }
                }" @keydown.escape.window="open = false">

                <x-ui.button type="button" x-ref="trigger" @click="toggle()"
                    x-bind:aria-expanded="open.toString()" variant="ghost"
                    x-bind:class="isCollapsed ? 'justify-center px-0 w-full bg-transparent! hover:bg-transparent!' : 'w-full px-2'"
                    class="flex items-center gap-2.5 rounded-md py-2 text-left h-auto">
                    <x-ui.avatar :fallback="substr($user->name, 0, 2)" class="h-8 w-8 shrink-0" />
                    <div x-show="!isCollapsed" x-cloak class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-sidebar-foreground truncate leading-tight">{{ $user->name }}</p>
                        <p class="text-xs text-sidebar-foreground/60 truncate capitalize">{{ $user->role }}</p>
                    </div>
                    <x-lucide-chevrons-up-down x-show="!isCollapsed" x-cloak class="size-3.5 shrink-0 text-sidebar-foreground/60" />
                </x-ui.button>

                <template x-teleport="body">
                    <div x-show="open" x-cloak @click.outside="open = false"
                        :style="`position:fixed;left:${px}px;top:${py}px;width:${pw}px;transform:translateY(-100%);z-index:var(--z-popover)`"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="origin-bottom-left rounded-lg bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10">

                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <x-ui.avatar :fallback="substr($user->name, 0, 2)" class="h-8 w-8 shrink-0" />
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-foreground truncate">{{ $user->name }}</p>
                                <p class="text-xs text-muted-foreground truncate capitalize">{{ $user->role }}</p>
                            </div>
                        </div>

                        <div role="separator" class="-mx-1 my-1 h-px bg-border"></div>

                        <a
                            href="{{ route('perfil.show') }}"
                            @click="open = false"
                            class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-accent transition-colors"
                        >
                            <x-lucide-user class="size-4 text-muted-foreground" />
                            Mi perfil
                        </a>

                        <a
                            href="{{ route('manual.show') }}"
                            @click="open = false"
                            class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-accent transition-colors"
                        >
                            <x-lucide-book-open class="size-4 text-muted-foreground" />
                            Manual de uso
                        </a>

                        <div role="separator" class="-mx-1 my-1 h-px bg-border"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="ghost" class="w-full justify-start gap-2 text-destructive bg-destructive/10 hover:bg-destructive/20">
                                <x-lucide-log-out class="size-4" /> Cerrar sesión
                            </x-ui.button>
                        </form>
                    </div>
                </template>
            </div>
        </x-ui.sidebar.footer>
        @endif

    </x-ui.sidebar>

    {{-- Main --}}
    <x-ui.sidebar.inset class="min-h-0 overflow-hidden flex flex-col">

        <header class="flex h-14 shrink-0 items-center gap-2 border-b border-border bg-sidebar px-4">
            <x-ui.sidebar.trigger class="-ml-1 text-muted-foreground" />

            @isset($breadcrumb)
                {{ $breadcrumb }}
            @else
                <x-ui.breadcrumb>
                    <x-ui.breadcrumb.list>
                        <x-ui.breadcrumb.item class="hidden sm:inline-flex">
                            <x-ui.breadcrumb.link :href="$homeRoute">Inicio</x-ui.breadcrumb.link>
                        </x-ui.breadcrumb.item>
                        <x-ui.breadcrumb.separator class="hidden sm:block" />
                        @if($section)
                            <x-ui.breadcrumb.item class="hidden sm:inline-flex">
                                <span>{{ $section }}</span>
                            </x-ui.breadcrumb.item>
                            <x-ui.breadcrumb.separator class="hidden sm:block" />
                        @endif
                        <x-ui.breadcrumb.item>
                            <x-ui.breadcrumb.page>{{ $title }}</x-ui.breadcrumb.page>
                        </x-ui.breadcrumb.item>
                    </x-ui.breadcrumb.list>
                </x-ui.breadcrumb>
            @endisset

            <div class="ml-auto flex items-center gap-1">

                {{-- Notificaciones: visible en todos los tamaños, maneja mobile/desktop internamente --}}
                @if($user?->isAdmin())
                    <x-domain.alertas.notificaciones-dropdown :count="$alertasNoLeidas" />
                @endif

                {{-- Desktop --}}
                <div class="hidden sm:flex items-center gap-1">
                    @if($user?->isOperador())
                        <x-ui.tooltip content="Ayuda" side="bottom">
                            <x-ui.button size="icon" variant="ghost"
                                aria-label="Ayuda"
                                @click="$dispatch('abrir-onboarding')"
                            >
                                <x-lucide-circle-help class="size-6" />
                            </x-ui.button>
                        </x-ui.tooltip>
                    @endif
                    @if($user?->isAdmin())
                        <x-ui.tooltip content="Ayuda" side="bottom">
                            <x-ui.button size="icon" variant="ghost"
                                aria-label="Ayuda"
                                @click="$dispatch('abrir-onboarding-admin')"
                            >
                                <x-lucide-circle-help class="size-6" />
                            </x-ui.button>
                        </x-ui.tooltip>
                    @endif

                    <x-ui.tooltip content="Cambiar tema" side="bottom">
                        <x-ui.button size="icon" variant="ghost" @click="$store.theme.toggle()"
                            aria-label="Cambiar tema">
                            <x-lucide-sun x-show="!$store.theme.dark" />
                            <x-lucide-moon x-show="$store.theme.dark" x-cloak />
                        </x-ui.button>
                    </x-ui.tooltip>

                    <x-ui.tooltip content="Cerrar sesión" side="bottom">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" size="icon" variant="ghost"
                                aria-label="Cerrar sesión">
                                <x-lucide-log-out />
                            </x-ui.button>
                        </form>
                    </x-ui.tooltip>
                </div>

                {{-- Mobile --}}
                <div class="flex sm:hidden">
                    <x-ui.dropdown-menu>
                        <x-ui.dropdown-menu.trigger>
                            <x-ui.button size="icon" variant="ghost" aria-label="Más opciones">
                                <x-lucide-ellipsis-vertical class="size-4" />
                            </x-ui.button>
                        </x-ui.dropdown-menu.trigger>
                        <x-ui.dropdown-menu.content align="end">
                            @if($user?->isOperador())
                                <x-ui.dropdown-menu.item @click="$dispatch('abrir-onboarding')">
                                    <x-lucide-circle-help /> Ayuda
                                </x-ui.dropdown-menu.item>
                            @endif
                            @if($user?->isAdmin())
                                <x-ui.dropdown-menu.item @click="$dispatch('abrir-onboarding-admin')">
                                    <x-lucide-circle-help /> Ayuda
                                </x-ui.dropdown-menu.item>
                            @endif
                            <x-ui.dropdown-menu.item @click="$store.theme.toggle()">
                                <span x-show="!$store.theme.dark" class="flex items-center gap-1.5">
                                    <x-lucide-moon /> Modo oscuro
                                </span>
                                <span x-show="$store.theme.dark" x-cloak class="flex items-center gap-1.5">
                                    <x-lucide-sun /> Modo claro
                                </span>
                            </x-ui.dropdown-menu.item>
                            <x-ui.dropdown-menu.separator />
                            <x-ui.dropdown-menu.item
                                variant="destructive"
                                @click="document.getElementById('logout-form-mobile').submit()"
                            >
                                <x-lucide-log-out /> Cerrar sesión
                            </x-ui.dropdown-menu.item>
                        </x-ui.dropdown-menu.content>
                    </x-ui.dropdown-menu>
                    <form id="logout-form-mobile" method="POST" action="{{ route('logout') }}" class="hidden">
                        @csrf
                    </form>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </div>

        <div id="layout-action-bar" class="shrink-0"></div>

        @if(isset($footerTurno) || isset($footerUltimo))
            <footer class="shrink-0 border-t border-border bg-sidebar">
                <div class="flex flex-col gap-1 py-2 px-4 sm:flex-row sm:items-center sm:justify-between sm:h-11 sm:py-0 sm:gap-0">
                    <div class="flex items-center justify-between text-caption text-muted-foreground sm:justify-start sm:gap-3">
                        {{ $footerTurno ?? '' }}
                    </div>
                    <div class="text-caption text-muted-foreground">
                        {{ $footerUltimo ?? '' }}
                    </div>
                </div>
            </footer>
        @endif

    </x-ui.sidebar.inset>

</x-ui.sidebar.provider>

@if($user?->isAdmin())
    <x-onboarding.bienvenida-admin :forzar="!$user->onboarding_visto" />
@endif

<x-ui.sonner />

@if(session('toast'))
<script>
    document.addEventListener('alpine:initialized', () => {
        Alpine.store('toast').add(@json(session('toast')));
    });
</script>
@endif


@stack('scripts')
</body>
</html>
