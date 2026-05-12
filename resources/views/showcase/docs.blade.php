<x-layouts.showcase title="Guía de uso — Design System">

<div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-10">
<div class="lg:grid lg:grid-cols-[1fr_220px] lg:gap-16 items-start">

{{-- ── Contenido principal ──────────────────────────────────────────────── --}}
<div class="min-w-0 space-y-20">

    {{-- Hero ----------------------------------------------------------------}}
    <div class="space-y-4">
        <x-ui.typography as="h1">Guía de uso</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Cómo construir interfaces con este design system: arquitectura, tokens, principios de pantallas y formularios, y cómo usarlo con Claude.
        </x-ui.typography>
        <div class="flex flex-wrap gap-2 pt-1">
            @foreach(['Laravel 12', 'Tailwind v4', 'Alpine.js', 'Blade', 'shadcn/ui', 'OKLCH'] as $tag)
                <x-ui.badge variant="secondary">{{ $tag }}</x-ui.badge>
            @endforeach
        </div>
    </div>

    {{-- ── 1. Atomic Design ──────────────────────────────────────────────── --}}
    <section id="atomic" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Arquitectura</x-ui.typography>
            <x-ui.typography as="h2">Atomic Design</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                El sistema sigue Atomic Design adaptado a Blade. Cada nivel construye sobre el anterior usando los mismos tokens.
            </x-ui.typography>
        </div>

        {{-- Diagrama de capas --}}
        <div class="rounded-xl border border-border bg-card overflow-hidden">
            <div class="grid grid-cols-1 divide-y divide-border sm:grid-cols-5 sm:divide-y-0 sm:divide-x">
                @foreach([
                    ['Tokens',      'design-tokens.css',    'Fuente de verdad',          'circle-dot'],
                    ['Átomos',      'badge, icon, skeleton', 'Unidad mínima',             'atom'],
                    ['Moléculas',   'button, input, form-field', 'Combinación de átomos', 'layers'],
                    ['Organismos',  'card, dialog, sidebar', 'Bloques de UI completos',   'layout-panel-left'],
                    ['Plantillas',  'layouts/app.blade.php', 'Estructura de página',      'panel-top'],
                ] as [$nivel, $ejemplos, $desc, $icon])
                <div class="flex flex-col items-center gap-2 p-5 text-center">
                    <div class="flex size-10 items-center justify-center rounded-full bg-primary/10">
                        <x-dynamic-component :component="'lucide-' . $icon" class="size-5 text-primary" />
                    </div>
                    <x-ui.typography as="small" element="p">{{ $nivel }}</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs leading-relaxed">{{ $desc }}</x-ui.typography>
                    <x-ui.typography as="code" class="text-[10px] text-center leading-relaxed">{{ $ejemplos }}</x-ui.typography>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Ejemplos de cada nivel --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Átomo --}}
            <x-ui.card>
                <x-ui.card.header class="pb-3">
                    <x-ui.card.title class="text-sm">Átomo</x-ui.card.title>
                    <x-ui.card.description>Unidad mínima indivisible</x-ui.card.description>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-3 pb-3">
                    <div class="flex flex-wrap gap-2">
                        <x-ui.badge>Default</x-ui.badge>
                        <x-ui.badge variant="secondary">Secondary</x-ui.badge>
                        <x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>
                    </div>
                    <x-ui.separator />
                    <x-ui.skeleton class="h-8 w-full rounded-md" />
                </x-ui.card.content>
                <x-ui.card.footer class="pt-0">
                    <pre class="w-full overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-ui.badge variant="secondary">Nuevo</x-ui.badge>
<x-ui.separator />
<x-ui.skeleton class="h-10 w-full" />@endverbatim</pre>
                </x-ui.card.footer>
            </x-ui.card>

            {{-- Molécula --}}
            <x-ui.card>
                <x-ui.card.header class="pb-3">
                    <x-ui.card.title class="text-sm">Molécula</x-ui.card.title>
                    <x-ui.card.description>Combinación con propósito</x-ui.card.description>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-3 pb-3">
                    <x-ui.button variant="outline" size="sm">
                        <x-lucide-download class="size-3.5" /> Descargar
                    </x-ui.button>
                    <x-ui.form-field>
                        <x-ui.label>Email</x-ui.label>
                        <x-ui.input type="email" placeholder="nombre@ejemplo.com" size="sm" />
                    </x-ui.form-field>
                </x-ui.card.content>
                <x-ui.card.footer class="pt-0">
                    <pre class="w-full overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-ui.button variant="outline">Guardar</x-ui.button>
<x-ui.form-field>
    <x-ui.label>Email</x-ui.label>
    <x-ui.input type="email" />
</x-ui.form-field>@endverbatim</pre>
                </x-ui.card.footer>
            </x-ui.card>

            {{-- Organismo --}}
            <x-ui.card>
                <x-ui.card.header class="pb-3">
                    <x-ui.card.title class="text-sm">Organismo</x-ui.card.title>
                    <x-ui.card.description>Bloque de UI completo</x-ui.card.description>
                </x-ui.card.header>
                <x-ui.card.content class="pb-3">
                    <x-ui.card class="border-dashed">
                        <x-ui.card.header class="py-3">
                            <x-ui.card.title class="text-xs">Card anidada</x-ui.card.title>
                        </x-ui.card.header>
                        <x-ui.card.content class="py-2 pb-3">
                            <x-ui.typography as="muted">Contenido</x-ui.typography>
                        </x-ui.card.content>
                        <x-ui.card.footer class="py-2">
                            <x-ui.button size="sm">Acción</x-ui.button>
                        </x-ui.card.footer>
                    </x-ui.card>
                </x-ui.card.content>
                <x-ui.card.footer class="pt-0">
                    <pre class="w-full overflow-x-auto rounded-md bg-muted px-3 py-2 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-ui.card>
    <x-ui.card.header>
        <x-ui.card.title>Título</x-ui.card.title>
    </x-ui.card.header>
    <x-ui.card.content>...</x-ui.card.content>
    <x-ui.card.footer>
        <x-ui.button>Acción</x-ui.button>
    </x-ui.card.footer>
</x-ui.card>@endverbatim</pre>
                </x-ui.card.footer>
            </x-ui.card>

        </div>

        <x-ui.alert>
            <x-ui.alert.title>Convención de naming</x-ui.alert.title>
            <x-ui.alert.description>
                Todos los componentes usan el prefijo <x-ui.typography as="code">x-ui.</x-ui.typography>.
                Los organismos compuestos tienen sub-componentes en carpeta homónima:
                <x-ui.typography as="code">x-ui.card</x-ui.typography>,
                <x-ui.typography as="code">x-ui.card.header</x-ui.typography>,
                <x-ui.typography as="code">x-ui.card.content</x-ui.typography>, etc.
            </x-ui.alert.description>
        </x-ui.alert>

    </section>

    <x-ui.separator />

    {{-- ── 2. Arquitectura de carpetas ──────────────────────────────────── --}}
    <section id="arquitectura" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Estructura del proyecto</x-ui.typography>
            <x-ui.typography as="h2">Arquitectura de carpetas</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Las vistas y componentes se organizan en tres niveles independientes que escalan sin fricción.
            </x-ui.typography>
        </div>

        {{-- Los tres niveles --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach([
                [
                    'prefix'  => 'x-ui.*',
                    'folder'  => 'components/ui/',
                    'label'   => 'Design system',
                    'desc'    => 'Primitivos genéricos. Sin lógica de negocio. No modificar.',
                    'icon'    => 'atom',
                    'state'   => null,
                    'items'   => ['button', 'input', 'card', 'badge', 'typography'],
                ],
                [
                    'prefix'  => 'x-shared.*',
                    'folder'  => 'components/shared/',
                    'label'   => 'Compartidos',
                    'desc'    => 'Usados en 2 o más features. Se sub-agrupan cuando hay 3+ relacionados.',
                    'icon'    => 'layers',
                    'state'   => null,
                    'items'   => ['page-header', 'empty-card', 'stats/metric-card'],
                ],
                [
                    'prefix'  => 'x-[feature].*',
                    'folder'  => 'components/[feature]/',
                    'label'   => 'Por feature',
                    'desc'    => 'Específicos de una sola feature. Nacen aquí, suben a shared cuando se reusan.',
                    'icon'    => 'puzzle',
                    'state'   => null,
                    'items'   => ['members/row', 'plans/price-card', 'gyms/access-badge'],
                ],
            ] as $level)
            <x-ui.card>
                <x-ui.card.content class="pt-5 pb-5 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-primary/10">
                            <x-dynamic-component :component="'lucide-' . $level['icon']" class="size-5 text-primary" />
                        </div>
                        <div class="space-y-0.5">
                            <x-ui.typography as="small" element="p">{{ $level['label'] }}</x-ui.typography>
                            <x-ui.typography as="code" class="text-[11px]">{{ $level['prefix'] }}</x-ui.typography>
                        </div>
                    </div>
                    <x-ui.typography as="muted">{{ $level['desc'] }}</x-ui.typography>
                    <x-ui.separator />
                    <div class="space-y-1">
                        @foreach($level['items'] as $item)
                        <div class="flex items-center gap-2">
                            <x-lucide-file class="size-3 text-muted-foreground shrink-0" />
                            <x-ui.typography as="code" class="text-[11px]">{{ $item }}.blade.php</x-ui.typography>
                        </div>
                        @endforeach
                    </div>
                </x-ui.card.content>
            </x-ui.card>
            @endforeach
        </div>

        {{-- Árbol de carpetas completo --}}
        <div class="space-y-3">
            <x-ui.typography as="section-label">Estructura completa</x-ui.typography>
            <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-5 py-4 text-xs font-mono text-foreground leading-relaxed">resources/views/
│
├── components/
│   ├── ui/                    ← design system (no tocar)
│   ├── layouts/               ← app.blade.php, auth.blade.php
│   ├── shared/                ← usados en 2+ features
│   │   ├── page-header.blade.php
│   │   └── stats/             ← sub-carpeta cuando hay 3+ del mismo dominio
│   │       └── metric-card.blade.php
│   ├── members/               ← componentes de una sola feature
│   │   └── row.blade.php
│   └── plans/
│       └── price-card.blade.php
│
├── members/                   ← páginas (espeja la URL)
│   ├── index.blade.php
│   ├── show.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── _activity-row.blade.php  ← partial interno (prefijo _)
│
├── plans/
│   └── index.blade.php
│
├── auth/                      ← login, register
├── errors/                    ← 404, 500
└── emails/                    ← plantillas de mail</pre>
        </div>

        {{-- Reglas --}}
        <div class="space-y-3">
            <x-ui.typography as="section-label">Reglas de ciclo de vida</x-ui.typography>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Promoción --}}
                <x-ui.card>
                    <x-ui.card.content class="pt-5 pb-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-lucide-arrow-up class="size-4 text-primary shrink-0" />
                            <x-ui.typography as="small" element="p">Ciclo de vida de un componente</x-ui.typography>
                        </div>
                        <div class="space-y-3">
                            @foreach([
                                ['1', 'Nace en', 'components/[feature]/', 'específico de una página'],
                                ['2', 'Sube a',  'components/shared/',    'cuando otra feature lo necesita'],
                                ['3', 'Agrupa en','components/shared/[grupo]/','cuando hay 3+ del mismo dominio'],
                            ] as [$num, $accion, $destino, $cuando])
                            <div class="flex items-start gap-3">
                                <div class="flex size-5 shrink-0 items-center justify-center rounded-full bg-muted text-[10px] font-bold text-muted-foreground mt-0.5">{{ $num }}</div>
                                <div>
                                    <span class="text-sm text-foreground">{{ $accion }} </span>
                                    <x-ui.typography as="code" class="text-xs">{{ $destino }}</x-ui.typography>
                                    <x-ui.typography as="muted" class="text-xs mt-0.5">{{ $cuando }}</x-ui.typography>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <x-ui.alert state="info">
                            <x-ui.alert.description>Nunca al revés. Un componente no baja de <x-ui.typography as="code">shared/</x-ui.typography> a <x-ui.typography as="code">[feature]/</x-ui.typography>.</x-ui.alert.description>
                        </x-ui.alert>
                    </x-ui.card.content>
                </x-ui.card>

                {{-- Componente vs partial --}}
                <x-ui.card>
                    <x-ui.card.content class="pt-5 pb-5 space-y-4">
                        <div class="flex items-center gap-2">
                            <x-lucide-git-branch class="size-4 text-primary shrink-0" />
                            <x-ui.typography as="small" element="p">Componente vs. partial</x-ui.typography>
                        </div>
                        <div class="rounded-xl border border-border overflow-hidden">
                            <x-ui.table>
                                <x-ui.table.header>
                                    <x-ui.table.row>
                                        <x-ui.table.head></x-ui.table.head>
                                        <x-ui.table.head>Componente</x-ui.table.head>
                                        <x-ui.table.head>Partial</x-ui.table.head>
                                    </x-ui.table.row>
                                </x-ui.table.header>
                                <x-ui.table.body>
                                    @foreach([
                                        ['Necesita props', 'Sí', 'No'],
                                        ['Se reutiliza',   'Sí', 'Solo en esa vista'],
                                        ['Se invoca con',  '<x-*>', '@include'],
                                        ['Prefijo nombre', 'kebab-case', '_ guión bajo'],
                                    ] as [$prop, $comp, $part])
                                    <x-ui.table.row>
                                        <x-ui.table.cell class="text-xs text-muted-foreground">{{ $prop }}</x-ui.table.cell>
                                        <x-ui.table.cell class="text-xs font-mono">{{ $comp }}</x-ui.table.cell>
                                        <x-ui.table.cell class="text-xs font-mono">{{ $part }}</x-ui.table.cell>
                                    </x-ui.table.row>
                                    @endforeach
                                </x-ui.table.body>
                            </x-ui.table>
                        </div>
                    </x-ui.card.content>
                </x-ui.card>

            </div>
        </div>

        {{-- Ejemplo --}}
        <div class="space-y-3">
            <x-ui.typography as="section-label">Ejemplo completo</x-ui.typography>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <x-ui.typography as="small" element="p" class="text-muted-foreground">components/members/row.blade.php</x-ui.typography>
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim@props(['member'])

<x-ui.table.row>
    <x-ui.table.cell>
        {{ $member->name }}
    </x-ui.table.cell>
    <x-ui.table.cell>
        <x-ui.badge
            :state="$member->active
                ? 'success' : 'destructive'"
            :subtle="true"
        >
            {{ $member->active ? 'Activo' : 'Inactivo' }}
        </x-ui.badge>
    </x-ui.table.cell>
</x-ui.table.row>@endverbatim</pre>
                </div>
                <div class="space-y-2">
                    <x-ui.typography as="small" element="p" class="text-muted-foreground">members/index.blade.php</x-ui.typography>
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-layouts.app>
    <x-shared.page-header
        title="Socios"
        description="Gestioná los socios del gimnasio."
    >
        <x-ui.button>Agregar socio</x-ui.button>
    </x-shared.page-header>

    <x-ui.table>
        <x-ui.table.body>
            @foreach($members as $member)
                <x-members.row :member="$member" />
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
</x-layouts.app>@endverbatim</pre>
                </div>
            </div>
        </div>

    </section>

    <x-ui.separator />

    {{-- ── 3. Tokens ─────────────────────────────────────────────────────── --}}
    <section id="tokens" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Sistema visual</x-ui.typography>
            <x-ui.typography as="h2">Tokens semánticos</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                <x-ui.typography as="code">design-tokens.css</x-ui.typography> es la fuente de verdad. Un cambio ahí se propaga a todos los componentes en ambos temas.
            </x-ui.typography>
        </div>

        {{-- Regla principal: bien vs mal --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="rounded-xl border border-success-border bg-success-subtle p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <x-lucide-check class="size-4 text-success shrink-0" />
                    <x-ui.typography as="small" element="p" class="text-success-subtle-foreground">Correcto — tokens semánticos</x-ui.typography>
                </div>
                <pre class="overflow-x-auto rounded-md bg-background/60 px-3 py-2.5 text-xs font-mono text-foreground leading-relaxed">@verbatim{{-- ✅ Tokens semánticos —siempre --}}
<div class="bg-background text-foreground">
<div class="bg-card border border-border">
<p class="text-muted-foreground">
<div class="bg-primary text-primary-foreground">@endverbatim</pre>
            </div>
            <div class="rounded-xl border border-destructive-border bg-destructive-subtle p-4 space-y-3">
                <div class="flex items-center gap-2">
                    <x-lucide-x class="size-4 text-destructive shrink-0" />
                    <x-ui.typography as="small" element="p" class="text-destructive-subtle-foreground">Incorrecto — colores hardcodeados</x-ui.typography>
                </div>
                <pre class="overflow-x-auto rounded-md bg-background/60 px-3 py-2.5 text-xs font-mono text-foreground leading-relaxed">@verbatim{{-- ❌ Valores hardcodeados —nunca --}}
<div class="bg-white text-gray-900">
<div class="bg-zinc-100">
<p class="text-gray-500">@endverbatim</pre>
            </div>
        </div>

        {{-- Tabla de tokens --}}
        <div class="space-y-4">
            <x-ui.typography as="section-label">Tokens principales</x-ui.typography>
            <div class="rounded-xl border border-border overflow-hidden">
                <x-ui.table>
                    <x-ui.table.header>
                        <x-ui.table.row>
                            <x-ui.table.head>Token</x-ui.table.head>
                            <x-ui.table.head>Muestra</x-ui.table.head>
                            <x-ui.table.head>Uso</x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        @foreach([
                            ['bg-background / text-foreground',     'background', 'foreground',           'Fondo de página + texto principal'],
                            ['bg-card / text-card-foreground',      'card',       'card-foreground',       'Fondo de cards y paneles'],
                            ['bg-muted / text-muted-foreground',    'muted',      'muted-foreground',      'Fondos sutiles, texto secundario'],
                            ['bg-primary / text-primary-foreground','primary',    'primary-foreground',    'Botón principal, CTAs'],
                            ['bg-accent / text-accent-foreground',  'accent',     'accent-foreground',     'Hover de items de menú y nav'],
                        ] as [$token, $bg, $text, $uso])
                        <x-ui.table.row>
                            <x-ui.table.cell class="font-mono text-xs">{{ $token }}</x-ui.table.cell>
                            <x-ui.table.cell>
                                <div class="flex h-6 w-14 rounded border border-border overflow-hidden">
                                    <div class="flex-1 bg-{{ $bg }}"></div>
                                    <div class="flex-1 flex items-center justify-center bg-{{ $bg }}">
                                        <span class="text-[8px] font-bold text-{{ $text }}">Aa</span>
                                    </div>
                                </div>
                            </x-ui.table.cell>
                            <x-ui.table.cell class="text-muted-foreground text-sm">{{ $uso }}</x-ui.table.cell>
                        </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            </div>
        </div>

        {{-- Estados semánticos --}}
        <div class="space-y-4">
            <x-ui.typography as="section-label">Estados semánticos — 6 tokens por estado</x-ui.typography>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach([
                    ['destructive', 'Error / Peligro'],
                    ['success',     'Éxito'],
                    ['warning',     'Advertencia'],
                    ['info',        'Información'],
                ] as [$state, $label])
                <div class="rounded-xl border border-border overflow-hidden">
                    <div class="h-8 bg-{{ $state }}"></div>
                    <div class="h-8 bg-{{ $state }}-subtle"></div>
                    <div class="p-3 bg-card space-y-2">
                        <x-ui.typography as="small" element="p">{{ $label }}</x-ui.typography>
                        <div class="flex gap-1.5 flex-wrap">
                            <x-ui.badge state="{{ $state }}">filled</x-ui.badge>
                            <x-ui.badge state="{{ $state }}" :subtle="true">subtle</x-ui.badge>
                        </div>
                        <x-ui.helper-text :state="$state" message="Helper text" />
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </section>

    <x-ui.separator />

    {{-- ── 3. Tipografía ──────────────────────────────────────────────────── --}}
    <section id="tipografia" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Sistema tipográfico</x-ui.typography>
            <x-ui.typography as="h2">Tipografía</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Nunca usar tags HTML crudos. Siempre
                <x-ui.typography as="code">x-ui.typography</x-ui.typography>
                con el prop <x-ui.typography as="code">as</x-ui.typography> correcto.
            </x-ui.typography>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Demo --}}
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title class="text-sm">Demo en vivo</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-4 pt-0">
                    <x-ui.typography as="h1" class="text-2xl">H1 — Título de página</x-ui.typography>
                    <x-ui.typography as="h2" class="text-xl">H2 — Sección principal</x-ui.typography>
                    <x-ui.typography as="h3" class="text-lg">H3 — Sub-sección</x-ui.typography>
                    <x-ui.typography as="lead" class="max-w-prose">Lead — subtítulo prominente con color muted</x-ui.typography>
                    <x-ui.typography as="p" class="max-w-prose">P — cuerpo de texto con color foreground normal y leading-relaxed para buena legibilidad.</x-ui.typography>
                    <x-ui.typography as="muted" class="max-w-prose">Muted — texto secundario, helper text, descripciones.</x-ui.typography>
                    <div class="flex flex-wrap gap-3 items-center">
                        <x-ui.typography as="section-label" element="span">SECTION LABEL</x-ui.typography>
                        <x-ui.typography as="small">Small — label</x-ui.typography>
                        <x-ui.typography as="code">code inline</x-ui.typography>
                    </div>
                </x-ui.card.content>
            </x-ui.card>

            {{-- Código --}}
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title class="text-sm">Cómo usarlo</x-ui.card.title>
                </x-ui.card.header>
                <x-ui.card.content class="pt-0">
                    <pre class="overflow-x-auto rounded-md bg-muted px-3 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-ui.typography as="h1">Título de página</x-ui.typography>
<x-ui.typography as="h2">Título de sección</x-ui.typography>
<x-ui.typography as="lead" class="max-w-prose">Subtítulo</x-ui.typography>
<x-ui.typography as="p" class="max-w-prose">Párrafo</x-ui.typography>
<x-ui.typography as="muted" class="max-w-prose">Secundario</x-ui.typography>
<x-ui.typography as="section-label">CATEGORÍA</x-ui.typography>
<x-ui.typography as="small">Label</x-ui.typography>
<x-ui.typography as="code">$variable</x-ui.typography>@endverbatim</pre>
                </x-ui.card.content>
            </x-ui.card>

        </div>

        {{-- Tabla de variantes --}}
        <div class="rounded-xl border border-border overflow-hidden">
            <x-ui.table>
                <x-ui.table.header>
                    <x-ui.table.row>
                        <x-ui.table.head><x-ui.typography as="code" class="text-xs">as=</x-ui.typography></x-ui.table.head>
                        <x-ui.table.head>Tag</x-ui.table.head>
                        <x-ui.table.head>Cuándo usar</x-ui.table.head>
                        <x-ui.table.head class="hidden sm:table-cell">Nota</x-ui.table.head>
                    </x-ui.table.row>
                </x-ui.table.header>
                <x-ui.table.body>
                    @foreach([
                        ['h1',            'h1',         'Título único de página',               '1 por página'],
                        ['h2',            'h2',         'Título de sección principal',           ''],
                        ['h3',            'h3',         'Sub-sección',                           ''],
                        ['h4',            'h4',         'Nivel más bajo de jerarquía',           ''],
                        ['lead',          'p',          'Subtítulo/intro prominente',            'color muted'],
                        ['large',         'div',        'Énfasis visual sin jerarquía',          ''],
                        ['p',             'p',          'Cuerpo — color foreground',             'max-w-prose'],
                        ['muted',         'p',          'Helper text, info secundaria',          'max-w-prose'],
                        ['small',         'small',      'Label compacto, metadata',              'font-medium'],
                        ['section-label', 'h2',         'Etiqueta de sección (xs, uppercase)',   ''],
                        ['code',          'code',       'Código inline, props, tokens',          ''],
                        ['blockquote',    'blockquote', 'Citas, fragmentos destacados',          ''],
                    ] as [$as, $tag, $cuando, $nota])
                    <x-ui.table.row>
                        <x-ui.table.cell><x-ui.typography as="code" class="text-xs">{{ $as }}</x-ui.typography></x-ui.table.cell>
                        <x-ui.table.cell class="font-mono text-xs text-muted-foreground">&lt;{{ $tag }}&gt;</x-ui.table.cell>
                        <x-ui.table.cell class="text-sm">{{ $cuando }}</x-ui.table.cell>
                        <x-ui.table.cell class="hidden sm:table-cell text-xs text-muted-foreground">{{ $nota }}</x-ui.table.cell>
                    </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </div>

        <x-ui.alert state="info">
            <x-ui.alert.title>max-w-prose en párrafos</x-ui.alert.title>
            <x-ui.alert.description>
                Todo <x-ui.typography as="code">p</x-ui.typography> y <x-ui.typography as="code">muted</x-ui.typography> de párrafo llevan <x-ui.typography as="code">class="max-w-prose"</x-ui.typography> — sin esto el texto se vuelve ilegible en pantallas anchas (líneas de más de 80 caracteres).
            </x-ui.alert.description>
        </x-ui.alert>

    </section>

    <x-ui.separator />

    {{-- ── 4. Principios de pantallas ────────────────────────────────────── --}}
    <section id="pantallas" class="space-y-10 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Principios</x-ui.typography>
            <x-ui.typography as="h2">Pantallas</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Reglas que aplican a toda pantalla generada con este design system.
            </x-ui.typography>
        </div>

        {{-- Regla 1: Grilla 4px --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">1</div>
                <x-ui.typography as="h3">Grilla de 4px — la regla más importante</x-ui.typography>
            </div>
            <x-ui.typography as="muted" class="max-w-prose pl-10">
                Todo valor de espaciado es múltiplo de 4px. Nunca usar valores arbitrarios con corchetes.
            </x-ui.typography>
            <div class="pl-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-xl border border-success-border bg-success-subtle p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <x-lucide-check class="size-4 text-success shrink-0" />
                        <x-ui.typography as="small" element="p" class="text-success-subtle-foreground">Correcto</x-ui.typography>
                    </div>
                    <pre class="overflow-x-auto rounded-md bg-background/60 px-3 py-2 text-xs font-mono text-foreground leading-relaxed">@verbatim{{-- ✅ Múltiplos de 4px --}}
&lt;div class="flex gap-2"&gt;        {{-- 8px --}}
&lt;div class="space-y-4"&gt;        {{-- 16px --}}
&lt;section class="py-12"&gt;        {{-- 48px --}}@endverbatim</pre>
                </div>
                <div class="rounded-xl border border-destructive-border bg-destructive-subtle p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <x-lucide-x class="size-4 text-destructive shrink-0" />
                        <x-ui.typography as="small" element="p" class="text-destructive-subtle-foreground">Incorrecto</x-ui.typography>
                    </div>
                    <pre class="overflow-x-auto rounded-md bg-background/60 px-3 py-2 text-xs font-mono text-foreground leading-relaxed">@verbatim{{-- ❌ Valores arbitrarios --}}
&lt;div class="flex gap-[7px]"&gt;
&lt;div class="space-y-[13px]"&gt;
&lt;section class="py-[50px]"&gt;@endverbatim</pre>
                </div>
            </div>
            <div class="pl-10 grid grid-cols-4 sm:grid-cols-8 gap-2">
                @foreach([
                    ['gap-1','4px'],['gap-2','8px'],['gap-3','12px'],['gap-4','16px'],
                    ['gap-6','24px'],['gap-8','32px'],['gap-12','48px'],['gap-16','64px'],
                ] as [$class, $px])
                <div class="flex flex-col items-center gap-1">
                    <div class="h-8 w-full rounded-md bg-primary/20 flex items-end justify-center pb-1">
                        @php $sz = min(28, intval($px) / 2); @endphp
                        <div class="rounded bg-primary" style="width:{{ $sz }}px;height:{{ $sz }}px;"></div>
                    </div>
                    <x-ui.typography as="code" class="text-[9px]">{{ $class }}</x-ui.typography>
                    <x-ui.typography as="muted" class="text-[9px]">{{ $px }}</x-ui.typography>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Regla 2: Responsive --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">2</div>
                <x-ui.typography as="h3">Responsive — patrones estándar</x-ui.typography>
            </div>
            <div class="pl-10">
                <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim{{-- Padding de página --}}
<div class="px-4 sm:px-6 lg:px-8">

{{-- Formulario: 1 col → 2 col --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

{{-- Stack → Row --}}
<div class="flex flex-col sm:flex-row gap-3">

{{-- Acciones al pie --}}
<div class="flex flex-col sm:flex-row-reverse gap-2">
    <x-ui.button class="w-full sm:w-auto">Guardar</x-ui.button>
    <x-ui.button variant="outline" class="w-full sm:w-auto">Cancelar</x-ui.button>
</div>@endverbatim</pre>
            </div>
        </div>

        {{-- Regla 3: Tres estados --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">3</div>
                <x-ui.typography as="h3">Tres estados de UI — siempre</x-ui.typography>
            </div>
            <x-ui.typography as="muted" class="max-w-prose pl-10">
                Toda sección que muestra datos necesita los tres estados. Sin excepción.
            </x-ui.typography>
            <div class="pl-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-ui.card>
                    <x-ui.card.header class="pb-3">
                        <x-ui.card.title class="text-sm">Cargando</x-ui.card.title>
                    </x-ui.card.header>
                    <x-ui.card.content class="space-y-2 pb-4">
                        <x-ui.skeleton class="h-10 w-full rounded-md" />
                        <x-ui.skeleton class="h-10 w-full rounded-md" />
                        <x-ui.skeleton class="h-10 w-4/5 rounded-md" />
                    </x-ui.card.content>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card.header class="pb-3">
                        <x-ui.card.title class="text-sm">Vacío</x-ui.card.title>
                    </x-ui.card.header>
                    <x-ui.card.content class="pb-4">
                        <x-ui.empty-state title="Sin resultados" description="Agregá el primero." icon="inbox" />
                    </x-ui.card.content>
                </x-ui.card>
                <x-ui.card>
                    <x-ui.card.header class="pb-3">
                        <x-ui.card.title class="text-sm">Con datos</x-ui.card.title>
                    </x-ui.card.header>
                    <x-ui.card.content class="space-y-2 pb-4">
                        @foreach(['Item 1', 'Item 2', 'Item 3'] as $item)
                        <div class="flex items-center justify-between rounded-md border border-border px-3 py-2">
                            <x-ui.typography as="small" element="p">{{ $item }}</x-ui.typography>
                            <x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>
                        </div>
                        @endforeach
                    </x-ui.card.content>
                </x-ui.card>
            </div>
            <div class="pl-10">
                <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim@if($loading)
    <x-ui.skeleton class="h-32 w-full rounded-lg" />

@elseif($items->isEmpty())
    <x-ui.empty-state
        title="Sin resultados"
        description="Agregá el primero para comenzar."
        icon="inbox"
    >
        <x-ui.button size="sm">Agregar</x-ui.button>
    </x-ui.empty-state>

@else
    @foreach($items as $item)
        {{-- contenido --}}
    @endforeach
@endif@endverbatim</pre>
            </div>
        </div>

        {{-- Regla 4: Estructura estándar --}}
        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">4</div>
                <x-ui.typography as="h3">Estructura de página estándar</x-ui.typography>
            </div>

            {{-- Demo visual anotada --}}
            <div class="pl-10 space-y-3">
                <x-ui.typography as="section-label">Anatomía y espaciado</x-ui.typography>

                <div class="rounded-xl border border-border bg-card overflow-hidden">
                    {{-- PAGE HEADER --}}
                    <div class="relative border-b border-dashed border-primary/40 bg-primary/5 px-6 py-4">
                        <div class="absolute -top-2.5 left-4">
                            <x-ui.badge variant="secondary" class="text-[10px] font-mono">PAGE HEADER · space-y-1 interno</x-ui.badge>
                        </div>
                        <div class="flex items-start justify-between gap-4 mt-1">
                            <div class="space-y-1">
                                <x-ui.typography as="h2" class="text-xl">Título de página</x-ui.typography>
                                <x-ui.typography as="muted" class="max-w-prose">Descripción breve de la sección.</x-ui.typography>
                            </div>
                            <div class="flex items-center gap-2">
                                <x-ui.badge variant="outline" class="text-[10px] font-mono hidden sm:flex">top-right</x-ui.badge>
                                <x-ui.button size="sm">Acción principal</x-ui.button>
                            </div>
                        </div>
                    </div>

                    {{-- Spacer entre zonas --}}
                    <div class="flex items-center gap-3 px-6 py-2 bg-muted/40 border-b border-dashed border-border">
                        <x-lucide-arrow-down class="size-3 text-muted-foreground shrink-0" />
                        <x-ui.typography as="code" class="text-[10px]">space-y-8 entre zonas principales</x-ui.typography>
                    </div>

                    {{-- CONTENT --}}
                    <div class="relative border-b border-dashed border-success/40 bg-success/5 px-6 py-4">
                        <div class="absolute -top-2.5 left-4">
                            <x-ui.badge state="success" :subtle="true" class="text-[10px] font-mono">CONTENT</x-ui.badge>
                        </div>
                        <div class="mt-1 rounded-lg border border-border bg-card p-4 space-y-3">
                            <div class="flex items-center justify-between">
                                <x-ui.typography as="section-label" element="span">Sub-sección</x-ui.typography>
                                <x-ui.badge variant="outline" class="text-[10px] font-mono">section-label</x-ui.badge>
                            </div>
                            <div class="space-y-2">
                                <x-ui.skeleton class="h-8 w-full rounded-md" />
                                <x-ui.skeleton class="h-8 w-4/5 rounded-md" />
                            </div>
                        </div>
                    </div>

                    {{-- Spacer acciones --}}
                    <div class="flex items-center gap-3 px-6 py-2 bg-muted/40 border-b border-dashed border-border">
                        <x-lucide-arrow-down class="size-3 text-muted-foreground shrink-0" />
                        <x-ui.typography as="code" class="text-[10px]">ACTIONS al pie — siempre al final del flujo</x-ui.typography>
                    </div>

                    {{-- ACTIONS --}}
                    <div class="relative px-6 py-4 bg-warning/5">
                        <div class="absolute -top-2.5 left-4">
                            <x-ui.badge state="warning" :subtle="true" class="text-[10px] font-mono">ACTIONS</x-ui.badge>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-4">
                            <x-ui.button variant="ghost" state="destructive" size="sm">Eliminar</x-ui.button>
                            <div class="flex gap-2">
                                <x-ui.button variant="ghost" size="sm">Cancelar</x-ui.button>
                                <x-ui.button size="sm">Guardar</x-ui.button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tipografía por zona --}}
            <div class="pl-10 space-y-3">
                <x-ui.typography as="section-label">Tipografía por zona</x-ui.typography>
                <div class="rounded-xl border border-border overflow-hidden">
                    <x-ui.table>
                        <x-ui.table.header>
                            <x-ui.table.row>
                                <x-ui.table.head>Zona</x-ui.table.head>
                                <x-ui.table.head>Componente</x-ui.table.head>
                                <x-ui.table.head class="hidden sm:table-cell">Variante / clase</x-ui.table.head>
                            </x-ui.table.row>
                        </x-ui.table.header>
                        <x-ui.table.body>
                            @foreach([
                                ['Título de página',         'x-ui.typography',    'as="h1"'],
                                ['Descripción de página',    'x-ui.typography',    'as="muted" class="max-w-prose"'],
                                ['Título de card',           'x-ui.card.title',    '— (o as="h2")'],
                                ['Descripción de card',      'x-ui.card.description', '— (o as="muted")'],
                                ['Etiqueta de sub-sección',  'x-ui.typography',    'as="section-label"'],
                                ['Texto de cuerpo',          'x-ui.typography',    'as="p" class="max-w-prose"'],
                            ] as [$zona, $comp, $variante])
                            <x-ui.table.row>
                                <x-ui.table.cell class="text-sm">{{ $zona }}</x-ui.table.cell>
                                <x-ui.table.cell><x-ui.typography as="code" class="text-xs">{{ $comp }}</x-ui.typography></x-ui.table.cell>
                                <x-ui.table.cell class="hidden sm:table-cell font-mono text-xs text-muted-foreground">{{ $variante }}</x-ui.table.cell>
                            </x-ui.table.row>
                            @endforeach
                        </x-ui.table.body>
                    </x-ui.table>
                </div>
            </div>

            {{-- Patrones de acciones --}}
            <div class="pl-10 space-y-3">
                <x-ui.typography as="section-label">Tres patrones de acciones</x-ui.typography>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <x-ui.card>
                        <x-ui.card.content class="pt-4 pb-4 space-y-3">
                            <x-ui.typography as="small" element="p">1. Acción de página</x-ui.typography>
                            <x-ui.typography as="muted">Top-right del page header. Crea, importa o lanza un flujo.</x-ui.typography>
                            <pre class="overflow-x-auto rounded-md bg-muted px-3 py-2 text-[11px] font-mono text-foreground leading-relaxed">@verbatim<div class="flex items-start
     justify-between gap-4">
    <div class="space-y-1">
        <x-ui.typography as="h1">
            ...
        </x-ui.typography>
    </div>
    <x-ui.button>
        Nueva entrada
    </x-ui.button>
</div>@endverbatim</pre>
                        </x-ui.card.content>
                    </x-ui.card>

                    <x-ui.card>
                        <x-ui.card.content class="pt-4 pb-4 space-y-3">
                            <x-ui.typography as="small" element="p">2. Acciones de formulario</x-ui.typography>
                            <x-ui.typography as="muted">Pie de card. Primario último (más a la derecha).</x-ui.typography>
                            <pre class="overflow-x-auto rounded-md bg-muted px-3 py-2 text-[11px] font-mono text-foreground leading-relaxed">@verbatim<x-ui.card.footer
    class="justify-end gap-2">
    <x-ui.button variant="ghost">
        Cancelar
    </x-ui.button>
    <x-ui.button>
        Guardar
    </x-ui.button>
</x-ui.card.footer>@endverbatim</pre>
                        </x-ui.card.content>
                    </x-ui.card>

                    <x-ui.card>
                        <x-ui.card.content class="pt-4 pb-4 space-y-3">
                            <x-ui.typography as="small" element="p">3. Acción destructiva</x-ui.typography>
                            <x-ui.typography as="muted">Separada del grupo principal, izquierda del footer.</x-ui.typography>
                            <pre class="overflow-x-auto rounded-md bg-muted px-3 py-2 text-[11px] font-mono text-foreground leading-relaxed">@verbatim<x-ui.card.footer
    class="justify-between">
    <x-ui.button
        variant="ghost"
        state="destructive">
        Eliminar
    </x-ui.button>
    <div class="flex gap-2">
        <x-ui.button variant="ghost">
            Cancelar
        </x-ui.button>
        <x-ui.button>Guardar</x-ui.button>
    </div>
</x-ui.card.footer>@endverbatim</pre>
                        </x-ui.card.content>
                    </x-ui.card>

                </div>
            </div>

            {{-- Código completo --}}
            <div class="pl-10 space-y-2">
                <x-ui.typography as="section-label">Código completo</x-ui.typography>
                <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim<x-layouts.app>
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- PAGE HEADER --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-1">
                <x-ui.typography as="h1">Título de página</x-ui.typography>
                <x-ui.typography as="muted" class="max-w-prose">
                    Descripción breve.
                </x-ui.typography>
            </div>
            <x-ui.button>Acción principal</x-ui.button>
        </div>

        {{-- CONTENT --}}
        <x-ui.card>
            <x-ui.card.content class="p-6 space-y-6">
                <x-ui.typography as="section-label">Sub-sección</x-ui.typography>
                {{-- contenido --}}
            </x-ui.card.content>

            {{-- ACTIONS --}}
            <x-ui.card.footer class="justify-end gap-2">
                <x-ui.button variant="ghost">Cancelar</x-ui.button>
                <x-ui.button>Guardar</x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>

    </div>
</x-layouts.app>@endverbatim</pre>
            </div>
        </div>

        {{-- Checklist --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">5</div>
                <x-ui.typography as="h3">Checklist antes de entregar</x-ui.typography>
            </div>
            <div class="pl-10 rounded-xl border border-border bg-card p-5">
                <div class="space-y-2.5">
                    @foreach([
                        'Un solo H1 en la página',
                        'Máximo 3 niveles de anidación visual',
                        'Texto de párrafo con max-w-prose',
                        'Tokens de color semánticos — sin bg-white / text-gray-*',
                        'Espaciados múltiplos de 4px — sin valores arbitrarios',
                        'Tres estados definidos: cargando, vacío, con datos',
                        'Acciones al final del flujo (bottom-right desktop / full-width mobile)',
                        'Verificado en mobile (< 640px) y desktop (≥ 1280px)',
                    ] as $item)
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex size-4 shrink-0 items-center justify-center rounded border-2 border-muted-foreground/30 bg-background"></div>
                        <x-ui.typography as="muted">{{ $item }}</x-ui.typography>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </section>

    <x-ui.separator />

    {{-- ── 5. Formularios ─────────────────────────────────────────────────── --}}
    <section id="formularios" class="space-y-10 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Principios</x-ui.typography>
            <x-ui.typography as="h2">Formularios</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                El componente central es <x-ui.typography as="code">form-field</x-ui.typography>. Coordina label, control y helper-text bajo un estado semántico único.
            </x-ui.typography>
        </div>

        {{-- form-field pattern --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">1</div>
                <x-ui.typography as="h3">Patrón form-field con validación Laravel</x-ui.typography>
            </div>
            <div class="pl-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <x-ui.form-field state="destructive" message="El correo ya está registrado." for="doc-email-err">
                        <x-ui.label for="doc-email-err">Correo electrónico</x-ui.label>
                        <x-ui.input id="doc-email-err" type="email" state="destructive" value="ocupado@ejemplo.com" />
                    </x-ui.form-field>
                    <x-ui.form-field state="success" message="Correo disponible." for="doc-email-ok">
                        <x-ui.label for="doc-email-ok">Correo electrónico</x-ui.label>
                        <x-ui.input id="doc-email-ok" type="email" state="success" value="libre@ejemplo.com" />
                    </x-ui.form-field>
                </div>
                <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed self-start">@verbatim<x-ui.form-field
    for="email"
    :state="$errors->has('email')
        ? 'destructive' : null"
    :message="$errors->first('email')"
>
    <x-ui.label for="email">Email</x-ui.label>
    <x-ui.input
        id="email"
        name="email"
        type="email"
        :value="old('email')"
        :state="$errors->has('email')
            ? 'destructive' : null"
    />
</x-ui.form-field>@endverbatim</pre>
            </div>
        </div>

        {{-- Tamaños --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">2</div>
                <x-ui.typography as="h3">Un solo size por formulario — nunca mezclar</x-ui.typography>
            </div>
            <div class="pl-10 rounded-xl border border-border bg-card overflow-hidden">
                <x-ui.table>
                    <x-ui.table.header>
                        <x-ui.table.row>
                            <x-ui.table.head>Prop</x-ui.table.head>
                            <x-ui.table.head><x-ui.typography as="code" class="text-xs">sm</x-ui.typography></x-ui.table.head>
                            <x-ui.table.head><x-ui.typography as="code" class="text-xs">md</x-ui.typography> (default)</x-ui.table.head>
                            <x-ui.table.head><x-ui.typography as="code" class="text-xs">lg</x-ui.typography></x-ui.table.head>
                        </x-ui.table.row>
                    </x-ui.table.header>
                    <x-ui.table.body>
                        @foreach([
                            ['Altura',    '32px (h-8)',     '40px (h-10)',    '48px (h-12)'],
                            ['Padding X', '12px (px-3)',    '16px (px-4)',    '20px (px-5)'],
                            ['Font',      '13px',           '14px (text-sm)', '16px (text-base)'],
                            ['Ícono',     'size-3.5',       'size-4',         'size-[18px]'],
                        ] as [$prop, $sm, $md, $lg])
                        <x-ui.table.row>
                            <x-ui.table.cell class="text-sm font-medium">{{ $prop }}</x-ui.table.cell>
                            <x-ui.table.cell class="font-mono text-xs text-muted-foreground">{{ $sm }}</x-ui.table.cell>
                            <x-ui.table.cell class="font-mono text-xs text-muted-foreground">{{ $md }}</x-ui.table.cell>
                            <x-ui.table.cell class="font-mono text-xs text-muted-foreground">{{ $lg }}</x-ui.table.cell>
                        </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            </div>
            <div class="pl-10 grid grid-cols-3 gap-3">
                @foreach(['sm', 'md', 'lg'] as $size)
                <div class="space-y-2">
                    <x-ui.typography as="section-label" element="p">{{ $size }}</x-ui.typography>
                    <x-ui.input size="{{ $size }}" placeholder="{{ $size }}" />
                    <x-ui.button size="{{ $size }}" class="w-full">Guardar</x-ui.button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Acciones --}}
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <div class="flex size-7 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs font-bold shrink-0">3</div>
                <x-ui.typography as="h3">Acciones al final del formulario</x-ui.typography>
            </div>
            <div class="pl-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-xl border border-border bg-card p-5 space-y-3">
                    <div class="flex flex-col sm:flex-row-reverse gap-2">
                        <x-ui.button class="w-full sm:w-auto">Guardar</x-ui.button>
                        <x-ui.button variant="outline" class="w-full sm:w-auto">Cancelar</x-ui.button>
                    </div>
                    <x-ui.typography as="muted" class="text-xs">↑ Desktop: primario a la derecha. Mobile: stack full-width.</x-ui.typography>
                </div>
                <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed self-start">@verbatim<div class="flex flex-col sm:flex-row-reverse gap-2 pt-2">
    <x-ui.button type="submit" class="w-full sm:w-auto">
        Guardar
    </x-ui.button>
    <x-ui.button
        as="a"
        href="/anterior"
        variant="outline"
        class="w-full sm:w-auto"
    >
        Cancelar
    </x-ui.button>
</div>@endverbatim</pre>
            </div>
        </div>

    </section>

    <x-ui.separator />

    {{-- ── 6. Usar con Claude ─────────────────────────────────────────────── --}}
    <section id="claude" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">IA</x-ui.typography>
            <x-ui.typography as="h2">Usar con Claude</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                El <x-ui.typography as="code">CLAUDE.md</x-ui.typography> del proyecto se carga automáticamente en cada sesión. Claude ya conoce todas las reglas — no hace falta recordárselas en cada prompt.
            </x-ui.typography>
        </div>

        <x-ui.alert state="success">
            <x-ui.alert.title>CLAUDE.md cargado automáticamente</x-ui.alert.title>
            <x-ui.alert.description>
                Las reglas de diseño, tokens, sizes y principios están en <x-ui.typography as="code">CLAUDE.md</x-ui.typography>. Claude las aplica en cada respuesta sin que el usuario las repita.
            </x-ui.alert.description>
        </x-ui.alert>

        {{-- Las 7 reglas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach([
                ['grid-2x2',        'Grilla de 4px',       'Nunca valores arbitrarios en espaciados'],
                ['palette',         'Tokens semánticos',   'Nunca bg-white, text-gray-* ni colores hardcodeados'],
                ['type',            'x-ui.typography',     'Nunca tags HTML crudos para texto'],
                ['ruler',           'Mismo size',          'No mezclar sm, md, lg en el mismo formulario'],
                ['layers',          'Tres estados',        'Cargando, vacío y con datos — siempre'],
                ['align-justify',   'max-w-prose',         'En todo párrafo p y muted'],
                ['move-down-right', 'Acciones al final',   'Bottom-right desktop / full-width mobile'],
            ] as [$icon, $titulo, $desc])
            <div class="rounded-xl border border-border bg-card p-4 space-y-2">
                <div class="flex items-center gap-2">
                    <x-dynamic-component :component="'lucide-' . $icon" class="size-4 text-primary shrink-0" />
                    <x-ui.typography as="small" element="p">{{ $titulo }}</x-ui.typography>
                </div>
                <x-ui.typography as="muted">{{ $desc }}</x-ui.typography>
            </div>
            @endforeach
        </div>

    </section>

    <x-ui.separator />

    {{-- ── 7. Setup ────────────────────────────────────────────────────────── --}}
    <section id="setup" class="space-y-8 scroll-mt-20">
        <div class="space-y-2">
            <x-ui.typography as="section-label">Nuevo proyecto</x-ui.typography>
            <x-ui.typography as="h2">Setup</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">
                Pasos para copiar el design system a un proyecto Laravel nuevo.
            </x-ui.typography>
        </div>

        <div class="space-y-6">

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-primary text-primary text-xs font-bold">1</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Instalar dependencias PHP</x-ui.typography>
                </div>
                <div class="pl-10">
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">composer require \
  gehrisandro/tailwind-merge-laravel \
  mallardduck/blade-lucide-icons</pre>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-primary text-primary text-xs font-bold">2</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Instalar dependencias Node</x-ui.typography>
                </div>
                <div class="pl-10">
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim
npm install tailwindcss @tailwindcss/vite alpinejs
@endverbatim</pre>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-primary text-primary text-xs font-bold">3</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Copiar los archivos</x-ui.typography>
                </div>
                <div class="pl-10">
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed"># Tokens y estilos
cp -r laravel-shadcn/laravel-app/resources/css/design-tokens.css \
      mi-proyecto/resources/css/

# Componentes Blade
cp -r laravel-shadcn/laravel-app/resources/views/components/ui/ \
      mi-proyecto/resources/views/components/

# JS + config íconos
cp laravel-shadcn/laravel-app/resources/js/app.js mi-proyecto/resources/js/
cp laravel-shadcn/laravel-app/config/icons.php     mi-proyecto/config/

# Reglas para Claude (se carga automáticamente en cada sesión)
cp laravel-shadcn/CLAUDE.md mi-proyecto/

# Documentación del design system
cp -r laravel-shadcn/docs/ mi-proyecto/docs/</pre>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-primary text-primary text-xs font-bold">4</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Configurar app.css</x-ui.typography>
                </div>
                <div class="pl-10">
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim
/* resources/css/app.css */
@import "tailwindcss";
@import "./design-tokens.css";

/* Habilitar modo dark con clase en html */
@variant dark (&:where(.dark, .dark *));
@endverbatim</pre>
                </div>
            </div>

            {{-- Verificación --}}
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-success text-success-foreground text-xs font-bold">✓</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Verificación rápida</x-ui.typography>
                </div>
                <div class="pl-10">
                    <x-ui.card class="border-success-border bg-success-subtle/30">
                        <x-ui.card.content class="pt-4 pb-4 space-y-4">
                            <x-ui.typography as="muted">Si ves esto correctamente, el design system está funcionando:</x-ui.typography>
                            <div class="flex flex-wrap gap-3 items-center">
                                <x-ui.button>Botón primario</x-ui.button>
                                <x-ui.button variant="outline">Outline</x-ui.button>
                                <x-ui.badge state="success" :subtle="true">Design System OK</x-ui.badge>
                                <x-ui.typography as="code">token semántico</x-ui.typography>
                            </div>
                        </x-ui.card.content>
                    </x-ui.card>
                </div>
            </div>

            {{-- Componente nuevo --}}
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full border-2 border-muted-foreground/30 text-muted-foreground text-xs font-bold">+</div>
                    <x-ui.typography as="large" element="p" class="text-base font-medium">Agregar un componente nuevo</x-ui.typography>
                </div>
                <div class="pl-10 space-y-3">
                    <x-ui.typography as="muted" class="max-w-prose">
                        Seguir este patrón: <x-ui.typography as="code">{{'@'}}props</x-ui.typography> con defaults, <x-ui.typography as="code">$attributes->twMerge()</x-ui.typography> para override externo, y solo tokens semánticos.
                    </x-ui.typography>
                    <pre class="overflow-x-auto rounded-lg border border-border bg-muted px-4 py-3 text-xs font-mono text-foreground leading-relaxed">@verbatim@props([
    'variant' => 'default',  // default | secondary
])

@php
$class = match($variant) {
    'secondary' => 'bg-secondary text-secondary-foreground',
    default     => 'bg-primary text-primary-foreground',
};
@endphp

<div {{ $attributes->twMerge('rounded-md px-4 py-2', $class) }}>
    {{ $slot }}
</div>@endverbatim</pre>
                </div>
            </div>

        </div>
    </section>

</div>

{{-- ── TOC sticky ─────────────────────────────────────────────────────────── --}}
<aside class="hidden lg:block">
    <nav class="sticky top-20 space-y-1" aria-label="Tabla de contenidos">
        <x-ui.typography as="section-label" class="mb-3 px-3">En esta página</x-ui.typography>
        @foreach([
            ['#atomic',        'Atomic Design'],
            ['#arquitectura',  'Arquitectura de carpetas'],
            ['#tokens',        'Tokens semánticos'],
            ['#tipografia',    'Tipografía'],
            ['#pantallas',     'Principios de pantallas'],
            ['#formularios',   'Formularios'],
            ['#claude',        'Usar con Claude'],
            ['#setup',         'Setup'],
        ] as [$href, $label])
        <a
            href="{{ $href }}"
            class="block rounded-md px-3 py-1.5 text-sm text-muted-foreground hover:text-foreground hover:bg-accent transition-colors"
        >
            {{ $label }}
        </a>
        @endforeach

        <div class="pt-4 px-3">
            <x-ui.separator />
        </div>
        <a
            href="https://github.com"
            class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-xs text-muted-foreground hover:text-foreground transition-colors"
        >
            <x-lucide-file-text class="size-3.5" />
            docs/design-system.md
        </a>
    </nav>
</aside>

</div>
</div>

</x-layouts.showcase>
