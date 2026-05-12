<x-layouts.showcase title="Design System — Showcase">

    <div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-10">

        {{-- Header --}}
        <div class="mb-10">
            <x-ui.typography as="h1" class="text-3xl sm:text-4xl">
                Design System
            </x-ui.typography>
            <x-ui.typography as="muted" class="mt-2 max-w-prose">
                Componentes Blade basados en shadcn/ui. Todos los tokens de color, tipografía
                y espaciado se controlan desde <x-ui.typography as="code">design-tokens.css</x-ui.typography>.
            </x-ui.typography>
        </div>

        {{-- Tokens de color --}}
        <section class="mb-16">
            <x-ui.typography as="h2" class="text-xl mb-6">Tokens de color</x-ui.typography>

            <div class="space-y-8">

                {{-- Semánticos --}}
                <div>
                    <x-ui.typography as="section-label" class="mb-3">Semánticos</x-ui.typography>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach([
                            ['background',  'Background',  'text-foreground'],
                            ['card',        'Card',        'text-card-foreground'],
                            ['primary',     'Primary',     'text-primary-foreground'],
                            ['secondary',   'Secondary',   'text-secondary-foreground'],
                            ['muted',       'Muted',       'text-muted-foreground'],
                            ['accent',      'Accent',      'text-accent-foreground'],
                        ] as [$bg, $label, $text])
                            <div class="rounded-lg border border-border overflow-hidden">
                                <div class="h-12 bg-{{ $bg }}"></div>
                                <div class="px-3 py-2 bg-card">
                                    <x-ui.typography as="p" class="text-xs font-medium">{{ $label }}</x-ui.typography>
                                    <x-ui.typography as="muted" class="text-xs font-mono">--{{ $bg }}</x-ui.typography>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Estados de feedback --}}
                <div>
                    <x-ui.typography as="section-label" class="mb-3">Estados de feedback</x-ui.typography>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @foreach([
                            ['destructive', 'Destructive', 'red'],
                            ['success',     'Success',     'green'],
                            ['warning',     'Warning',     'yellow'],
                            ['info',        'Info',        'blue'],
                        ] as [$state, $label, $_])
                            <div class="rounded-lg border border-border overflow-hidden">
                                <div class="h-8 bg-{{ $state }}"></div>
                                <div class="h-8 bg-{{ $state }}-subtle"></div>
                                <div class="px-3 py-2 bg-card space-y-1">
                                    <x-ui.typography as="p" class="text-xs font-medium">{{ $label }}</x-ui.typography>
                                    <div class="flex gap-2 flex-wrap">
                                        <span class="text-[10px] text-{{ $state }}-foreground bg-{{ $state }} px-1.5 py-0.5 rounded">filled</span>
                                        <span class="text-[10px] text-{{ $state }}-subtle-foreground bg-{{ $state }}-subtle border border-{{ $state }}-border px-1.5 py-0.5 rounded">subtle</span>
                                        <span class="text-[10px] text-{{ $state }} px-1.5 py-0.5">text</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </section>

        {{-- Tipografía --}}
        <section class="mb-16">
            <x-ui.typography as="h2" class="text-xl mb-6">Tipografía</x-ui.typography>
            <div class="space-y-4 bg-card border border-border rounded-xl p-6">
                <div class="space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">Display · 60px · bold</x-ui.typography>
                    <x-ui.typography as="p" class="text-5xl font-bold leading-tight tracking-tight">Sistema de diseño</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">H1 · 40px · semibold</x-ui.typography>
                    <x-ui.typography as="h1" class="font-semibold leading-tight">Componentes accesibles</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">H2 · 30px · semibold</x-ui.typography>
                    <x-ui.typography as="h2" class="text-3xl leading-snug">Tailwind + Blade</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">H3 · 22px · medium</x-ui.typography>
                    <x-ui.typography as="h3" class="text-xl font-medium leading-snug">Tokens de diseño</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">Body · 16px · normal · max 65ch</x-ui.typography>
                    <x-ui.typography as="p" class="text-md max-w-prose text-foreground">
                        El sistema de diseño utiliza CSS custom properties para todos los tokens visuales.
                        Un cambio en <x-ui.typography as="code">design-tokens.css</x-ui.typography>
                        propaga el cambio a todos los componentes en ambos temas.
                    </x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">Small · 13px</x-ui.typography>
                    <x-ui.typography as="muted">Texto secundario y descripciones de ayuda</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">Caption · 12px</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs">Metadata, timestamps, labels de formulario</x-ui.typography>
                </div>
                <div class="border-t border-border pt-4 space-y-1">
                    <x-ui.typography as="muted" class="text-xs font-mono">Code · mono</x-ui.typography>
                    <x-ui.typography as="code">design-tokens.css</x-ui.typography>
                </div>
            </div>
        </section>

        {{-- Sombras --}}
        <section class="mb-16">
            <x-ui.typography as="h2" class="text-xl mb-6">Elevación</x-ui.typography>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach(['xs', 'sm', 'md', 'lg', 'xl', '2xl'] as $size)
                    <div class="bg-card rounded-lg p-4 shadow-{{ $size }} flex flex-col items-center gap-2">
                        <span class="text-xs font-mono text-muted-foreground">shadow-{{ $size }}</span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Icons --}}
        <section class="mb-16">
            <x-ui.typography as="h2" class="text-xl mb-6">Iconografía</x-ui.typography>
            <a
                href="/showcase/components/icons"
                class="group flex items-center justify-between rounded-xl border border-border bg-card px-5 py-4 transition-all hover:border-primary/50 hover:bg-accent/30 max-w-sm"
            >
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-1.5">
                        @foreach(['home', 'settings', 'search', 'bell', 'user'] as $i)
                            <span class="inline-flex size-8 items-center justify-center rounded-full border border-background bg-muted text-foreground [&>svg]:size-4 [&>svg]:stroke-current">
                                {!! file_get_contents(base_path("vendor/mallardduck/blade-lucide-icons/resources/svg/{$i}.svg")) !!}
                            </span>
                        @endforeach
                    </div>
                    <div>
                        <x-ui.typography as="p" class="font-medium">Lucide Icons</x-ui.typography>
                        <x-ui.typography as="muted" class="text-xs">1942 iconos · búsqueda en tiempo real</x-ui.typography>
                    </div>
                </div>
                <span class="text-muted-foreground text-xs group-hover:text-primary transition-colors">→</span>
            </a>
        </section>

        {{-- Componentes (próximamente) --}}
        <section>
            <x-ui.typography as="h2" class="text-xl mb-6">Componentes</x-ui.typography>
            @php
            $components = [
                'Fase 3 — Primitivos' => [
                    ['button',    'Button',    true],
                    ['input',     'Input',     true],
                    ['textarea',  'Textarea',  false],
                    ['badge',     'Badge',     true],
                    ['label',     'Label',     false],
                    ['separator', 'Separator', false],
                    ['avatar',    'Avatar',    false],
                    ['progress',  'Progress',  false],
                    ['skeleton',  'Skeleton',  false],
                    ['spinner',   'Spinner',   false],
                ],
                'Fase 4 — Superficies' => [
                    ['card',         'Card',         false],
                    ['alert',        'Alert',        false],
                    ['dialog',       'Dialog',       false],
                    ['sheet',        'Sheet',        false],
                    ['drawer',       'Drawer',       false],
                    ['tooltip',      'Tooltip',      false],
                    ['popover',      'Popover',      false],
                ],
                'Fase 5 — Formularios' => [
                    ['select',       'Select',       false],
                    ['checkbox',     'Checkbox',     false],
                    ['radio-group',  'Radio Group',  false],
                    ['switch',       'Switch',       false],
                    ['slider',       'Slider',       false],
                ],
                'Fase 6 — Navegación' => [
                    ['tabs',         'Tabs',         false],
                    ['accordion',    'Accordion',    false],
                    ['breadcrumb',   'Breadcrumb',   false],
                    ['pagination',   'Pagination',   false],
                ],
                'Fase 7 — Data' => [
                    ['table',        'Table',        false],
                    ['chart',        'Chart',        false],
                ],
                'Fase 8 — Feedback' => [
                    ['toast',        'Toast',        false],
                ],
            ];
            @endphp

            <div class="space-y-8">
                @foreach($components as $phase => $items)
                    <div>
                        <x-ui.typography as="section-label" class="mb-3">{{ $phase }}</x-ui.typography>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                            @foreach($items as [$slug, $name, $ready])
                                <a
                                    href="{{ $ready ? "/showcase/components/{$slug}" : '#' }}"
                                    @class([
                                        'group flex items-center justify-between rounded-lg border px-4 py-3 text-sm font-medium transition-all',
                                        'border-border bg-card hover:border-primary/50 hover:bg-accent/30 cursor-pointer' => $ready,
                                        'border-dashed border-border bg-transparent opacity-50 cursor-not-allowed' => !$ready,
                                    ])
                                >
                                    <span @class(['text-foreground group-hover:text-primary transition-colors' => $ready, 'text-muted-foreground' => !$ready])>
                                        {{ $name }}
                                    </span>
                                    @if($ready)
                                        <span class="text-muted-foreground text-xs">→</span>
                                    @else
                                        <span class="text-[10px] text-muted-foreground">pronto</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

    </div>

</x-layouts.showcase>
