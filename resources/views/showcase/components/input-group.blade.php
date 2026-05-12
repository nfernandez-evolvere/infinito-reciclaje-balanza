<x-layouts.showcase title="Input Group — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Input Group</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Wrapper que fusiona un input con addons: íconos, texto, botones. El ring de foco se aplica al grupo completo.</x-ui.typography>
    </div>

    {{-- ── Addons inline ────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Addons inline (start / end)</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-sm">
            {{-- Ícono start --}}
            <x-ui.input-group>
                <x-ui.input-group.addon>
                    <x-lucide-search />
                </x-ui.input-group.addon>
                <x-ui.input-group.input placeholder="Buscar..." />
            </x-ui.input-group>

            {{-- Ícono end --}}
            <x-ui.input-group>
                <x-ui.input-group.input placeholder="usuario@ejemplo.com" type="email" />
                <x-ui.input-group.addon align="inline-end">
                    <x-lucide-circle-check class="text-success" />
                </x-ui.input-group.addon>
            </x-ui.input-group>

            {{-- Texto start --}}
            <x-ui.input-group>
                <x-ui.input-group.text>https://</x-ui.input-group.text>
                <x-ui.input-group.input placeholder="mi-sitio.com" />
            </x-ui.input-group>

            {{-- Texto end --}}
            <x-ui.input-group>
                <x-ui.input-group.input placeholder="0.00" type="number" />
                <x-ui.input-group.text>USD</x-ui.input-group.text>
            </x-ui.input-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Botones ───────────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con botones</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-sm">
            {{-- Botón buscar --}}
            <x-ui.input-group>
                <x-ui.input-group.input placeholder="Buscar productos..." />
                <x-ui.input-group.addon align="inline-end">
                    <x-ui.input-group.button>
                        <x-lucide-search />
                    </x-ui.input-group.button>
                </x-ui.input-group.addon>
            </x-ui.input-group>

            {{-- Copiar al portapapeles --}}
            <x-ui.input-group>
                <x-ui.input-group.input value="https://mi-app.com/invite/abc123" readonly />
                <x-ui.input-group.addon align="inline-end">
                    <x-ui.input-group.button>
                        <x-lucide-copy />
                    </x-ui.input-group.button>
                </x-ui.input-group.addon>
            </x-ui.input-group>

            {{-- Password toggle --}}
            <x-ui.input-group x-data="{ show: false }">
                <x-ui.input-group.input
                    x-bind:type="show ? 'text' : 'password'"
                    placeholder="Contraseña"
                />
                <x-ui.input-group.addon align="inline-end">
                    <x-ui.input-group.button @click="show = !show">
                        <x-lucide-eye x-show="!show" />
                        <x-lucide-eye-off x-show="show" />
                    </x-ui.input-group.button>
                </x-ui.input-group.addon>
            </x-ui.input-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Addons block (label flotante) ───────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Addons block (label integrado)</x-ui.typography>
        <div class="flex flex-col gap-3 max-w-sm">
            <x-ui.input-group>
                <x-ui.input-group.addon align="block-start" class="border-b border-border">
                    Nombre completo
                </x-ui.input-group.addon>
                <x-ui.input-group.input placeholder="Juan Pérez" />
            </x-ui.input-group>

            <x-ui.input-group>
                <x-ui.input-group.input placeholder="Escribe tu mensaje..." />
                <x-ui.input-group.addon align="block-end" class="border-t border-border justify-end">
                    <x-ui.input-group.button>Enviar</x-ui.input-group.button>
                </x-ui.input-group.addon>
            </x-ui.input-group>
        </div>
    </section>

    <x-ui.separator />

    {{-- ── Con textarea ──────────────────────────────────────────────────── --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con textarea</x-ui.typography>
        <x-ui.input-group class="max-w-sm">
            <x-ui.input-group.textarea placeholder="Escribe tu comentario..." rows="3" />
            <x-ui.input-group.addon align="block-end" class="border-t border-border justify-end">
                <x-ui.input-group.button>Comentar</x-ui.input-group.button>
            </x-ui.input-group.addon>
        </x-ui.input-group>
    </section>

</div>
</x-layouts.showcase>
