@props(['title' => 'Panel'])

@php
    $user = auth()->user();

    $operacionItems = [
        ['route' => 'admin.dashboard',     'icon' => 'layout-dashboard', 'label' => 'Dashboard'],
        ['route' => 'admin.pesajes.index',  'icon' => 'scale',            'label' => 'Pesajes'],
        ['route' => 'admin.reportes.index', 'icon' => 'file-bar-chart',   'label' => 'Reportes'],
    ];
    $padronItems = [
        ['route' => 'admin.zonas.index',     'icon' => 'map-pin',        'label' => 'Zonas'],
        ['route' => 'admin.servicios.index', 'icon' => 'clipboard-list', 'label' => 'Tipos de servicio'],
    ];
    $transporteItems = [
        ['route' => 'admin.vehiculos.index',      'icon' => 'truck', 'label' => 'Vehículos'],
        ['route' => 'admin.tipos-vehiculo.index', 'icon' => 'car',   'label' => 'Tipos de vehículo'],
    ];
    $sistemaItems = [
        ['route' => 'admin.usuarios.index', 'icon' => 'users', 'label' => 'Usuarios'],
    ];
    $operadorItems = [
        ['route' => 'balanza',   'icon' => 'scale', 'label' => 'Pesaje'],
        ['route' => 'historial', 'icon' => 'list',  'label' => 'Historial'],
    ];

    $transporteActive = collect($transporteItems)->contains(fn($i) => request()->routeIs($i['route']));
    $sistemaActive    = collect($sistemaItems)->contains(fn($i) => request()->routeIs($i['route']));

    $homeRoute = $user->isAdmin() ? route('admin.dashboard') : route('balanza');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <x-layouts.head :title="$title" />
</head>
<body class="h-screen overflow-hidden bg-background text-foreground antialiased">

<x-ui.sidebar.provider class="h-screen">

    {{-- Sidebar --}}
    <x-ui.sidebar collapsible="icon">

        <x-ui.sidebar.header class="h-14 flex-row items-center border-b border-sidebar-border p-0 px-4">
            <a href="{{ $homeRoute }}" class="flex items-center gap-2 min-w-0">
                <div class="size-6 shrink-0 rounded bg-primary flex items-center justify-center">
                    <span class="text-[10px] font-bold text-primary-foreground leading-none">IR</span>
                </div>
                <span class="text-sm font-semibold text-sidebar-foreground truncate">Balanza</span>
            </a>
        </x-ui.sidebar.header>

        <x-ui.sidebar.content>

            @if($user->isAdmin())

                {{-- Operación --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Operación</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($operacionItems as $item)
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="request()->routeIs($item['route'])"
                                        :tooltip="$item['label']"
                                    >
                                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-4 shrink-0" />
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
                                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-4 shrink-0" />
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

                {{-- Transporte --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            <x-ui.sidebar.menu-item x-data="{ open: {{ $transporteActive ? 'true' : 'false' }} }">
                                <x-ui.sidebar.menu-button @click="open = !open" tooltip="Transporte">
                                    <x-lucide-truck class="size-4 shrink-0" />
                                    <span>Transporte</span>
                                    <span class="ml-auto transition-transform" :class="open ? 'rotate-180' : ''">
                                        <x-lucide-chevron-down class="size-3" />
                                    </span>
                                </x-ui.sidebar.menu-button>
                                <x-ui.sidebar.menu-sub x-show="open && !isCollapsed" x-cloak x-collapse>
                                    @foreach($transporteItems as $item)
                                        <x-ui.sidebar.menu-sub-item>
                                            <x-ui.sidebar.menu-sub-button
                                                :href="route($item['route'])"
                                                :active="request()->routeIs($item['route'])"
                                            >
                                                <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-4 shrink-0" />
                                                <span>{{ $item['label'] }}</span>
                                            </x-ui.sidebar.menu-sub-button>
                                        </x-ui.sidebar.menu-sub-item>
                                    @endforeach
                                </x-ui.sidebar.menu-sub>
                            </x-ui.sidebar.menu-item>
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

                {{-- Sistema --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            <x-ui.sidebar.menu-item x-data="{ open: {{ $sistemaActive ? 'true' : 'false' }} }">
                                <x-ui.sidebar.menu-button @click="open = !open" tooltip="Sistema">
                                    <x-lucide-settings class="size-4 shrink-0" />
                                    <span>Sistema</span>
                                    <span class="ml-auto transition-transform" :class="open ? 'rotate-180' : ''">
                                        <x-lucide-chevron-down class="size-3" />
                                    </span>
                                </x-ui.sidebar.menu-button>
                                <x-ui.sidebar.menu-sub x-show="open && !isCollapsed" x-cloak x-collapse>
                                    @foreach($sistemaItems as $item)
                                        <x-ui.sidebar.menu-sub-item>
                                            <x-ui.sidebar.menu-sub-button
                                                :href="route($item['route'])"
                                                :active="request()->routeIs($item['route'])"
                                            >
                                                <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-4 shrink-0" />
                                                <span>{{ $item['label'] }}</span>
                                            </x-ui.sidebar.menu-sub-button>
                                        </x-ui.sidebar.menu-sub-item>
                                    @endforeach
                                </x-ui.sidebar.menu-sub>
                            </x-ui.sidebar.menu-item>
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

            @else

                {{-- Operador --}}
                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label x-show="!isCollapsed" x-cloak>Operación</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($operadorItems as $item)
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button
                                        :href="route($item['route'])"
                                        :active="request()->routeIs($item['route'])"
                                        :tooltip="$item['label']"
                                    >
                                        <x-dynamic-component :component="'lucide-' . $item['icon']" class="size-4 shrink-0" />
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

            @endif

        </x-ui.sidebar.content>

        {{-- User footer --}}
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
                    class="flex w-full items-center gap-2.5 rounded-md px-2 py-2 text-left h-auto">
                    <x-ui.avatar :fallback="substr($user->name, 0, 2)" class="h-8 w-8 shrink-0" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-sidebar-foreground truncate leading-tight">{{ $user->name }}</p>
                        <p class="text-xs text-sidebar-foreground/60 truncate capitalize">{{ $user->role }}</p>
                    </div>
                    <x-lucide-chevrons-up-down class="size-3.5 shrink-0 text-sidebar-foreground/60" />
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
                        class="origin-bottom-left rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10">

                        <div class="flex items-center gap-3 px-3 py-2.5">
                            <x-ui.avatar :fallback="substr($user->name, 0, 2)" class="h-8 w-8 shrink-0" />
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-foreground truncate">{{ $user->name }}</p>
                                <p class="text-xs text-muted-foreground truncate capitalize">{{ $user->role }}</p>
                            </div>
                        </div>

                        <div role="separator" class="-mx-1 my-1 h-px bg-border"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-destructive hover:bg-destructive/10 transition-colors cursor-default">
                                <x-lucide-log-out class="size-4" /> Cerrar sesión
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </x-ui.sidebar.footer>

    </x-ui.sidebar>

    {{-- Main --}}
    <x-ui.sidebar.inset class="min-h-0 overflow-hidden flex flex-col">

        <header class="flex h-14 shrink-0 items-center gap-2 border-b border-border bg-background px-4">
            <x-ui.sidebar.trigger class="-ml-1 text-muted-foreground" />

            <x-ui.separator orientation="vertical" class="h-4" />
            @isset($breadcrumb)
                {{ $breadcrumb }}
            @else
                <x-ui.breadcrumb>
                    <x-ui.breadcrumb.item>
                        <x-ui.breadcrumb.page>{{ $title }}</x-ui.breadcrumb.page>
                    </x-ui.breadcrumb.item>
                </x-ui.breadcrumb>
            @endisset

            <div class="ml-auto flex items-center gap-1">
                @if($user->isOperador())
                    <span
                        x-data="{ time: '' }"
                        x-init="
                            const update = () => {
                                const now = new Date();
                                time = now.toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                            };
                            update();
                            setInterval(update, 1000);
                        "
                        x-text="time"
                        class="text-caption font-mono tabular-nums px-2"
                    ></span>
                @endif

                <x-ui.button size="icon" variant="ghost" @click="$store.theme.toggle()"
                    aria-label="Cambiar tema" class="size-8 text-muted-foreground">
                    <x-lucide-sun x-show="!$store.theme.dark" class="size-4" />
                    <x-lucide-moon x-show="$store.theme.dark" x-cloak class="size-4" />
                </x-ui.button>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-6">
            {{ $slot }}
        </div>

        @if(isset($footerTurno) || isset($footerUltimo))
            <footer class="shrink-0 border-t border-border bg-background">
                <div class="flex items-center justify-between h-11 px-4">
                    <div class="flex items-center gap-4 text-caption text-muted-foreground">
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

<x-ui.sonner />
</body>
</html>
