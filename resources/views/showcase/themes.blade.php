<x-layouts.showcase title="Themes — Design System">
<div class="mx-auto max-w-[--container-xl] px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="mb-10 space-y-1">
        <x-ui.typography as="h1" class="text-3xl sm:text-4xl">Themes</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            5 temas mínimos. Cada uno define color primario y border-radius.
            Usar el selector del topbar para aplicar el tema activo a todo el showcase.
        </x-ui.typography>
    </div>

    {{-- Theme cards — cada una aislada con su propio data-theme --}}
    @php
    $themes = [
        ['default',  'Zinc',     'oklch(0.145 0 0)',       'Neutral sin color de marca. Base del sistema.',                   '8px',  'B2B · Dashboard'],
        ['sapphire', 'Sapphire', 'oklch(0.546 0.197 250)', 'Azul profesional. SaaS, herramientas, enterprise.',              '6px',  'SaaS · Enterprise'],
        ['emerald',  'Emerald',  'oklch(0.519 0.181 145)', 'Verde amigable. Salud, naturaleza, productividad.',               '12px', 'Consumer · Salud'],
        ['rose',     'Rose',     'oklch(0.568 0.268 27)',   'Rojo assertivo. Urgencia, acción directa, finanzas.',            '4px',  'Fintech · Urgente'],
        ['violet',   'Violet',   'oklch(0.553 0.252 300)', 'Violeta creativo. Startups, diseño, comunidades.',               '16px', 'Creativo · Social'],
    ];
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
        @foreach($themes as [$name, $label, $color, $description, $radius, $archetype])
        <div data-theme="{{ $name }}" class="flex flex-col rounded-xl border border-border bg-card overflow-hidden">

            {{-- Strip de color primario --}}
            <div class="h-1.5 bg-primary"></div>

            {{-- Contenido --}}
            <div class="flex flex-col gap-4 p-5 flex-1">

                {{-- Identidad --}}
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <div class="size-3.5 rounded-full shrink-0" style="background-color: {{ $color }}"></div>
                        <x-ui.typography as="p" class="text-sm font-semibold">{{ $label }}</x-ui.typography>
                    </div>
                    <x-ui.typography as="muted" class="text-xs leading-relaxed">{{ $description }}</x-ui.typography>
                    <div class="flex flex-wrap gap-1.5 pt-0.5">
                        <x-ui.badge variant="secondary" class="text-[10px] px-1.5 py-0">r={{ $radius }}</x-ui.badge>
                        <x-ui.badge variant="outline" class="text-[10px] px-1.5 py-0">{{ $archetype }}</x-ui.badge>
                    </div>
                </div>

                {{-- Mini UI --}}
                <div class="space-y-2.5">

                    {{-- Botones --}}
                    <div class="flex flex-wrap gap-1.5">
                        <x-ui.button size="sm">Guardar</x-ui.button>
                        <x-ui.button size="sm" variant="outline">Cancelar</x-ui.button>
                    </div>

                    {{-- Input --}}
                    <x-ui.input size="sm" placeholder="correo@ejemplo.com" />

                    {{-- Badges de estado --}}
                    <div class="flex flex-wrap gap-1">
                        <x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>
                        <x-ui.badge state="warning" :subtle="true">Pendiente</x-ui.badge>
                        <x-ui.badge>Nuevo</x-ui.badge>
                    </div>

                </div>
            </div>

            {{-- Footer: aplicar tema --}}
            <div class="border-t border-border px-5 py-3">
                <button
                    type="button"
                    x-data
                    @click="$store.brandTheme.apply('{{ $name }}')"
                    class="w-full text-center text-xs font-medium transition-colors"
                    :class="$store.brandTheme.current === '{{ $name }}'
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'"
                >
                    <span x-show="$store.brandTheme.current === '{{ $name }}'" x-cloak>✓ Tema activo</span>
                    <span x-show="$store.brandTheme.current !== '{{ $name }}'">Aplicar tema →</span>
                </button>
            </div>

        </div>
        @endforeach
    </div>

    {{-- Comparación de radius --}}
    <section class="mt-16">
        <x-ui.typography as="h2" class="text-xl mb-2">Comparación de border-radius</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose mb-6">
            El mismo componente (<x-ui.typography as="code">rounded-md</x-ui.typography>) con el radius de cada tema.
        </x-ui.typography>
        <div class="grid grid-cols-5 gap-3">
            @foreach($themes as [$name, $label, $color, , $radius])
            <div data-theme="{{ $name }}" class="space-y-2">
                <div class="bg-primary rounded-md h-14 flex items-center justify-center">
                    <span class="text-primary-foreground text-xs font-medium font-mono">{{ $radius }}</span>
                </div>
                <x-ui.typography as="muted" class="text-xs text-center">{{ $label }}</x-ui.typography>
            </div>
            @endforeach
        </div>
    </section>

    {{-- Comparación de botones --}}
    <section class="mt-12">
        <x-ui.typography as="h2" class="text-xl mb-2">Botones por tema</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose mb-6">
            Color primario + radius combinados en el mismo componente.
        </x-ui.typography>
        <div class="space-y-3">
            @foreach($themes as [$name, $label])
            <div data-theme="{{ $name }}" class="flex items-center gap-4">
                <span class="w-20 shrink-0 text-xs text-muted-foreground font-medium">{{ $label }}</span>
                <div class="flex gap-2 flex-wrap">
                    <x-ui.button size="sm">Primary</x-ui.button>
                    <x-ui.button size="sm" variant="outline">Outline</x-ui.button>
                    <x-ui.button size="sm" variant="ghost">Ghost</x-ui.button>
                    <x-ui.button size="sm" variant="secondary">Secondary</x-ui.button>
                </div>
            </div>
            @endforeach
        </div>
    </section>

</div>
</x-layouts.showcase>
