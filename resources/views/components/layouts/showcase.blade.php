@php
$navItems = [
    'Fase 3 — Primitivos' => [
        ['button',       'Button'],
        ['badge',        'Badge'],
        ['input',        'Input'],
        ['textarea',     'Textarea'],
        ['label',        'Label'],
        ['separator',    'Separator'],
        ['avatar',       'Avatar'],
        ['progress',     'Progress'],
        ['skeleton',     'Skeleton'],
        ['spinner',      'Spinner'],
        ['kbd',          'Kbd'],
        ['aspect-ratio', 'Aspect Ratio'],
        ['scroll-area',  'Scroll Area'],
    ],
    'Fase 4 — Superficies' => [
        ['card',          'Card'],
        ['alert',         'Alert'],
        ['alert-dialog',  'Alert Dialog'],
        ['dialog',        'Dialog'],
        ['sheet',         'Sheet'],
        ['drawer',        'Drawer'],
        ['hover-card',    'Hover Card'],
        ['tooltip',       'Tooltip'],
        ['popover',       'Popover'],
    ],
    'Fase 5 — Formularios' => [
        ['form-field',    'Form Field'],
        ['select',        'Select'],
        ['native-select', 'Native Select'],
        ['input-group',   'Input Group'],
        ['input-otp',     'Input OTP'],
        ['checkbox',      'Checkbox'],
        ['radio-group',   'Radio Group'],
        ['switch',        'Switch'],
        ['slider',        'Slider'],
        ['toggle',        'Toggle'],
        ['combobox',      'Combobox'],
        ['date-picker',   'Date Picker'],
    ],
    'Fase 6 — Navegación' => [
        ['tabs',              'Tabs'],
        ['accordion',         'Accordion'],
        ['collapsible',       'Collapsible'],
        ['breadcrumb',        'Breadcrumb'],
        ['pagination',        'Pagination'],
        ['dropdown-menu',     'Dropdown Menu'],
        ['context-menu',      'Context Menu'],
        ['menubar',           'Menubar'],
        ['navigation-menu',   'Navigation Menu'],
    ],
    'Fase 7 — Data' => [
        ['table',         'Table'],
        ['data-table',    'Data Table'],
        ['chart',         'Chart'],
    ],
    'Fase 8 — Feedback' => [
        ['toast',   'Toast'],
        ['sonner',  'Sonner'],
    ],
    'Fase 9 — Layout' => [
        ['button-group',  'Button Group'],
        ['empty-state',   'Empty State'],
        ['typography',    'Typography'],
        ['carousel',      'Carousel'],
        ['calendar',      'Calendar'],
        ['command',       'Command'],
        ['resizable',     'Resizable'],
        ['sidebar',       'Sidebar'],
    ],
];

$ready = ['button', 'badge', 'input', 'textarea', 'label', 'separator', 'avatar', 'progress', 'skeleton', 'spinner', 'card', 'alert', 'alert-dialog', 'tooltip', 'dialog', 'sheet', 'drawer', 'hover-card', 'popover', 'form-field', 'select', 'native-select', 'input-group', 'input-otp', 'checkbox', 'radio-group', 'switch', 'slider', 'toggle', 'combobox', 'date-picker', 'tabs', 'accordion', 'collapsible', 'breadcrumb', 'pagination', 'table', 'data-table', 'chart', 'toast', 'sonner', 'dropdown-menu', 'context-menu', 'menubar', 'navigation-menu', 'kbd', 'aspect-ratio', 'scroll-area', 'button-group', 'empty-state', 'typography', 'carousel', 'calendar', 'command', 'resizable', 'sidebar'];

// [icon, label corto, tooltip colapsado]
$phaseIcons = [
    'Fase 3 — Primitivos' => ['plus',      'Primitivos'],
    'Fase 4 — Superficies'=> ['copy',      'Superficies'],
    'Fase 5 — Formularios'=> ['filter',    'Formularios'],
    'Fase 6 — Navegación' => ['menu',      'Navegación'],
    'Fase 7 — Data'       => ['chart-bar',  'Data'],
    'Fase 8 — Feedback'   => ['bell',      'Feedback'],
    'Fase 9 — Layout'     => ['columns',   'Layout'],
];

$currentComponent = request()->segment(3);
$isIndex  = request()->is('showcase');
$isDocs   = request()->is('showcase/docs');
$isThemes = request()->is('showcase/themes');

$examplePages = [
    ['auth/login',    'Login',         'log-in'],
    ['auth/register', 'Registro',      'user-plus'],
    ['auth/verify',   'Verificación',  'shield-check'],
    ['dashboard',     'Dashboard',     'layout-dashboard'],
    ['members',       'Miembros',      'users'],
    ['settings',      'Configuración', 'settings'],
    ['onboarding',    'Onboarding',    'sparkles'],
    ['notifications', 'Notificaciones','bell'],
    ['users/show',    'Perfil',        'circle-user'],
    ['billing',       'Facturación',   'credit-card'],
    ['posts/create',  'Editor',        'file-pen'],
    ['analytics',     'Analytics',     'chart-area'],
    ['search',        'Búsqueda',      'search'],
    ['products/create','Producto',     'package'],
];

$readyExamples  = ['auth/login', 'auth/register', 'auth/verify', 'dashboard', 'members', 'settings', 'billing', 'users/show', 'notifications', 'onboarding', 'posts/create', 'analytics', 'search', 'products/create'];
$currentExample = ltrim(request()->path(), '/');
$currentExample = str_starts_with($currentExample, 'examples/')
    ? substr($currentExample, strlen('examples/'))
    : null;
$examplesHasActive = $currentExample !== null;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Design System' }} — Showcase</title>

    {{-- Evita flash de tema y brand theme --}}
    <script>
        (function() {
            const saved = localStorage.getItem('theme')
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark')
            }
            const brandTheme = localStorage.getItem('brandTheme') || 'default'
            if (brandTheme !== 'default') {
                document.documentElement.setAttribute('data-theme', brandTheme)
            }
        })()
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-background text-foreground antialiased">

<x-ui.sidebar.provider class="h-screen">

    {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
    <x-ui.sidebar collapsible="icon">

        {{-- Logo --}}
        <x-ui.sidebar.header class="h-14 flex-row items-center justify-between border-b border-sidebar-border p-0 px-4 gap-0">
            <a href="/showcase" class="flex items-center gap-2">
                <div class="size-6 shrink-0 rounded bg-primary flex items-center justify-center">
                    <span class="text-[10px] font-bold text-primary-foreground">DS</span>
                </div>
                <span class="text-sm font-semibold text-sidebar-foreground">Design System</span>
            </a>
            <button
                @click="closeMobile()"
                class="lg:hidden -mr-1 inline-flex size-8 items-center justify-center rounded-md text-muted-foreground hover:bg-sidebar-accent hover:text-sidebar-accent-foreground transition-colors"
                aria-label="Cerrar sidebar"
            >
                <x-lucide-x class="size-4" />
            </button>
        </x-ui.sidebar.header>

        {{-- Nav --}}
        <x-ui.sidebar.content>
            <x-ui.sidebar.group>
                <x-ui.sidebar.group-content>
                    <x-ui.sidebar.menu>

                        {{-- Inicio --}}
                        <x-ui.sidebar.menu-item>
                            <x-ui.sidebar.menu-button href="/showcase" :active="$isIndex" tooltip="Inicio">
                                <x-lucide-home class="size-4 shrink-0" />
                                <span>Inicio</span>
                            </x-ui.sidebar.menu-button>
                        </x-ui.sidebar.menu-item>

                        {{-- Guía de uso --}}
                        <x-ui.sidebar.menu-item>
                            <x-ui.sidebar.menu-button href="/showcase/docs" :active="$isDocs" tooltip="Guía de uso">
                                <x-lucide-book-open class="size-4 shrink-0" />
                                <span>Guía de uso</span>
                            </x-ui.sidebar.menu-button>
                        </x-ui.sidebar.menu-item>

                        {{-- Icons --}}
                        <x-ui.sidebar.menu-item>
                            <x-ui.sidebar.menu-button href="/showcase/components/icons" :active="$currentComponent === 'icons'" tooltip="Icons">
                                <x-lucide-paintbrush class="size-4 shrink-0" />
                                <span>Icons</span>
                            </x-ui.sidebar.menu-button>
                        </x-ui.sidebar.menu-item>

                        {{-- Themes --}}
                        <x-ui.sidebar.menu-item>
                            <x-ui.sidebar.menu-button href="/showcase/themes" :active="$isThemes" tooltip="Themes">
                                <x-lucide-swatch-book class="size-4 shrink-0" />
                                <span>Themes</span>
                            </x-ui.sidebar.menu-button>
                        </x-ui.sidebar.menu-item>

                        {{-- Fases colapsables --}}
                        @foreach($navItems as $phase => $items)
                            @php
                                [$phaseIcon, $phaseLabel] = $phaseIcons[$phase];
                                $phaseHasActive = collect($items)->contains(fn($item) => $item[0] === $currentComponent);
                            @endphp
                            <x-ui.sidebar.menu-item x-data="{ subOpen: {{ $phaseHasActive ? 'true' : 'false' }} }">
                                <x-ui.sidebar.menu-button
                                    @click="subOpen = !subOpen"
                                    :active="$phaseHasActive"
                                    tooltip="{{ $phaseLabel }}"
                                >
                                    <x-dynamic-component :component="'lucide-' . $phaseIcon" class="size-4 shrink-0" />
                                    <span>{{ $phaseLabel }}</span>
                                    <span class="ml-auto">
                                        <x-lucide-chevron-right class="size-4 transition-transform" x-bind:class="subOpen ? 'rotate-90' : ''" />
                                    </span>
                                </x-ui.sidebar.menu-button>
                                <x-ui.sidebar.menu-sub x-show="subOpen" x-cloak>
                                    @foreach($items as [$slug, $name])
                                        @php $isReady = in_array($slug, $ready); @endphp
                                        <x-ui.sidebar.menu-sub-item>
                                            @if($isReady)
                                                <x-ui.sidebar.menu-sub-button
                                                    href="/showcase/components/{{ $slug }}"
                                                    :active="$currentComponent === $slug"
                                                >
                                                    {{ $name }}
                                                </x-ui.sidebar.menu-sub-button>
                                            @else
                                                <span class="flex h-7 items-center px-2 text-sm text-muted-foreground/40 cursor-not-allowed select-none">
                                                    {{ $name }}
                                                </span>
                                            @endif
                                        </x-ui.sidebar.menu-sub-item>
                                    @endforeach
                                </x-ui.sidebar.menu-sub>
                            </x-ui.sidebar.menu-item>
                        @endforeach

                        {{-- Separador antes de ejemplos --}}
                        <x-ui.sidebar.separator />

                        {{-- Páginas de ejemplo --}}
                        <x-ui.sidebar.menu-item x-data="{ subOpen: {{ $examplesHasActive ? 'true' : 'false' }} }">
                            <x-ui.sidebar.menu-button
                                @click="subOpen = !subOpen"
                                :active="$examplesHasActive"
                                tooltip="Ejemplos"
                            >
                                <x-lucide-panels-top-left class="size-4 shrink-0" />
                                <span>Páginas</span>
                                <span class="ml-auto">
                                    <x-lucide-chevron-right class="size-4 transition-transform" x-bind:class="subOpen ? 'rotate-90' : ''" />
                                </span>
                            </x-ui.sidebar.menu-button>
                            <x-ui.sidebar.menu-sub x-show="subOpen" x-cloak>
                                @foreach($examplePages as [$slug, $name, $icon])
                                    @php $isReady = in_array($slug, $readyExamples); @endphp
                                    <x-ui.sidebar.menu-sub-item>
                                        @if($isReady)
                                            <x-ui.sidebar.menu-sub-button
                                                href="/examples/{{ $slug }}"
                                                target="_blank"
                                                :active="$currentExample === $slug"
                                            >
                                                <x-dynamic-component :component="'lucide-' . $icon" class="size-3 shrink-0" />
                                                {{ $name }}
                                            </x-ui.sidebar.menu-sub-button>
                                        @else
                                            <span class="flex h-7 items-center gap-2 px-2 text-sm text-muted-foreground/40 cursor-not-allowed select-none">
                                                <x-dynamic-component :component="'lucide-' . $icon" class="size-3 shrink-0" />
                                                {{ $name }}
                                            </span>
                                        @endif
                                    </x-ui.sidebar.menu-sub-item>
                                @endforeach
                            </x-ui.sidebar.menu-sub>
                        </x-ui.sidebar.menu-item>

                    </x-ui.sidebar.menu>
                </x-ui.sidebar.group-content>
            </x-ui.sidebar.group>
        </x-ui.sidebar.content>

        {{-- Footer --}}
        <x-ui.sidebar.footer class="border-t border-sidebar-border">
            <x-ui.typography as="muted" class="px-2 text-[10px] font-mono">shadcn/ui → Laravel Blade</x-ui.typography>
        </x-ui.sidebar.footer>

    </x-ui.sidebar>

    {{-- ── Contenido principal ──────────────────────────────────────────── --}}
    <x-ui.sidebar.inset class="min-h-0 overflow-hidden">

        {{-- Topbar --}}
        <header class="flex h-14 shrink-0 items-center justify-between border-b border-border bg-background px-4 lg:px-6">

            {{-- Trigger + breadcrumb --}}
            <div class="flex items-center gap-2">
                <x-ui.sidebar.trigger class="-ml-1 text-muted-foreground" />
                <div class="hidden lg:flex items-center gap-2 text-sm text-muted-foreground">
                    <x-ui.separator orientation="vertical" class="h-4" />
                    <a href="/showcase" class="hover:text-foreground transition-colors">Showcase</a>
                    @if($isDocs)
                        <x-ui.separator orientation="vertical" class="h-4" />
                        <span class="text-foreground font-medium">Guía de uso</span>
                    @elseif($isThemes)
                        <x-ui.separator orientation="vertical" class="h-4" />
                        <span class="text-foreground font-medium">Themes</span>
                    @elseif($currentComponent)
                        <x-ui.separator orientation="vertical" class="h-4" />
                        <span class="text-foreground font-medium capitalize">{{ str_replace('-', ' ', $currentComponent) }}</span>
                    @endif
                </div>
            </div>

            {{-- Controles de tema --}}
            <div class="flex items-center gap-3">

                {{-- Brand theme switcher --}}
                <x-ui.theme-switcher />

                <x-ui.separator orientation="vertical" class="h-4" />

                {{-- Dark mode toggle --}}
                <x-ui.tooltip content="Claro / Oscuro" side="bottom">
                    <x-ui.button
                        size="icon"
                        variant="ghost"
                        @click="$store.theme.toggle()"
                        aria-label="Cambiar tema"
                        class="text-muted-foreground"
                    >
                        <x-lucide-sun  x-show="!$store.theme.dark" class="size-4" />
                        <x-lucide-moon x-show="$store.theme.dark"  class="size-4" />
                    </x-ui.button>
                </x-ui.tooltip>

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
