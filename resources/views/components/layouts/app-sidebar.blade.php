@props(['title' => null])

@php
    $navMain = [
        ['href' => '/examples/dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard', 'ready' => true, 'badge' => null],
        ['href' => '/examples/members', 'label' => 'Miembros', 'icon' => 'users', 'ready' => true, 'badge' => null],
        ['href' => '/examples/notifications', 'label' => 'Notificaciones', 'icon' => 'bell', 'ready' => true, 'badge' => '3'],
        ['href' => '/examples/analytics', 'label' => 'Analytics', 'icon' => 'chart-area', 'ready' => true, 'badge' => null],
    ];

    $navSettings = [
        ['href' => '/examples/billing', 'label' => 'Facturación', 'icon' => 'credit-card', 'ready' => true],
        ['href' => '/examples/settings', 'label' => 'Configuración', 'icon' => 'settings-2', 'ready' => true],
    ];

    $isActive = fn(string $href) => request()->is(ltrim($href, '/'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' — ' . config('app.name') : config('app.name') }}</title>

    <script>
        (function () {
            const saved = localStorage.getItem('theme')
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark')
            }
        })()
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden bg-background text-foreground antialiased">

    <x-ui.sidebar.provider class="h-screen">

        {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
        <x-ui.sidebar collapsible="icon">

            {{-- Logo --}}
            <x-ui.sidebar.header class="h-14 flex-row items-center border-b border-sidebar-border p-0 px-4">
                <a href="/showcase" class="flex items-center gap-2 min-w-0">
                    <div class="size-6 shrink-0 rounded bg-primary flex items-center justify-center">
                        <span class="text-[10px] font-bold text-primary-foreground leading-none">DS</span>
                    </div>
                    <span class="text-sm font-semibold text-sidebar-foreground truncate">Design System</span>
                </a>
            </x-ui.sidebar.header>

            {{-- Nav --}}
            <x-ui.sidebar.content>

                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label>Aplicación</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($navMain as $item)
                                <x-ui.sidebar.menu-item class="relative">
                                    @if($item['ready'])
                                        <x-ui.sidebar.menu-button href="{{ $item['href'] }}" :active="$isActive($item['href'])"
                                            tooltip="{{ $item['label'] }}">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']"
                                                class="size-4 shrink-0" />
                                            <span>{{ $item['label'] }}</span>
                                        </x-ui.sidebar.menu-button>
                                    @else
                                        <x-ui.sidebar.menu-button tooltip="{{ $item['label'] }}" disabled
                                            class="opacity-40 cursor-not-allowed">
                                            <x-dynamic-component :component="'lucide-' . $item['icon']"
                                                class="size-4 shrink-0" />
                                            <span>{{ $item['label'] }}</span>
                                        </x-ui.sidebar.menu-button>
                                    @endif
                                    @if($item['badge'])
                                        <x-ui.sidebar.menu-badge>{{ $item['badge'] }}</x-ui.sidebar.menu-badge>
                                    @endif
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

                <x-ui.sidebar.separator />

                <x-ui.sidebar.group>
                    <x-ui.sidebar.group-label>Configuración</x-ui.sidebar.group-label>
                    <x-ui.sidebar.group-content>
                        <x-ui.sidebar.menu>
                            @foreach($navSettings as $item)
                                <x-ui.sidebar.menu-item>
                                    <x-ui.sidebar.menu-button tooltip="{{ $item['label'] }}" disabled
                                        class="opacity-40 cursor-not-allowed">
                                        <x-dynamic-component :component="'lucide-' . $item['icon']"
                                            class="size-4 shrink-0" />
                                        <span>{{ $item['label'] }}</span>
                                    </x-ui.sidebar.menu-button>
                                </x-ui.sidebar.menu-item>
                            @endforeach
                        </x-ui.sidebar.menu>
                    </x-ui.sidebar.group-content>
                </x-ui.sidebar.group>

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
                    {{-- Trigger --}}
                    <x-ui.button type="button" x-ref="trigger" @click="toggle()" x-bind:aria-expanded="open.toString()"
                        variant="ghost" class="flex w-full items-center gap-2.5 rounded-md px-2 py-2 text-left">
                        <x-ui.avatar alt="Nicolás Fernández" size="sm" class="shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-sidebar-foreground truncate leading-tight">Nicolás
                                Fernández</p>
                            <p class="text-xs text-sidebar-foreground/60 truncate">nico@ejemplo.com</p>
                        </div>
                        <x-lucide-chevrons-up-down class="size-3.5 shrink-0 text-sidebar-foreground/60" />
                    </x-ui.button>

                    {{-- Menú teleportado a body para evitar el overflow:hidden del sidebar --}}
                    <template x-teleport="body">
                        <div x-show="open" x-cloak @click.outside="open = false"
                            :style="`position:fixed;left:${px}px;top:${py}px;width:${pw}px;transform:translateY(-100%);z-index:var(--z-popover)`"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="origin-bottom-left rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10">
                            {{-- User info (no clickeable) --}}
                            <div class="flex items-center gap-3 px-3 py-2.5">
                                <x-ui.avatar alt="Nicolás Fernández" size="sm" class="shrink-0" />
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">Nicolás Fernández</p>
                                    <p class="text-xs text-muted-foreground truncate">nico@ejemplo.com</p>
                                </div>
                            </div>

                            <div role="separator" class="-mx-1 my-1 h-px bg-border"></div>

                            <button type="button" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors cursor-default">
                                <x-lucide-user class="size-4" /> Mi perfil
                            </button>
                            <button type="button" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors cursor-default">
                                <x-lucide-settings class="size-4" /> Configuración
                            </button>
                            <button type="button" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground transition-colors cursor-default">
                                <x-lucide-bell class="size-4" /> Notificaciones
                            </button>

                            <div role="separator" class="-mx-1 my-1 h-px bg-border"></div>

                            <button type="button" @click="open = false"
                                class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm text-destructive hover:bg-destructive/10 transition-colors cursor-default">
                                <x-lucide-log-out class="size-4" /> Cerrar sesión
                            </button>
                        </div>
                    </template>
                </div>
            </x-ui.sidebar.footer>

        </x-ui.sidebar>

        {{-- ── Inset (contenido principal) ─────────────────────────────────── --}}
        <x-ui.sidebar.inset class="min-h-0 overflow-hidden flex flex-col">

            {{-- Topbar --}}
            <header class="flex h-14 shrink-0 items-center gap-2 border-b border-border bg-background px-4">

                <x-ui.sidebar.trigger class="-ml-1 text-muted-foreground" />

                @isset($breadcrumb)
                    <x-ui.separator orientation="vertical" class="h-4" />
                    {{ $breadcrumb }}
                @endisset

                <div class="ml-auto flex items-center gap-1">
                    <x-ui.button size="icon" variant="ghost" @click="$store.theme.toggle()" aria-label="Cambiar tema"
                        class="size-8 text-muted-foreground">
                        <x-lucide-sun x-show="!$store.theme.dark" class="size-4" />
                        <x-lucide-moon x-show="$store.theme.dark" x-cloak class="size-4" />
                    </x-ui.button>
                </div>

            </header>

            {{-- Scroll area del contenido --}}
            <div class="flex-1 overflow-y-auto">
                {{ $slot }}
            </div>

        </x-ui.sidebar.inset>

    </x-ui.sidebar.provider>

    <x-ui.sonner />

</body>

</html>