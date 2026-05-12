<x-layouts.showcase title="Switch — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Switch</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Toggle accesible. El estado se gestiona con Alpine.js y el input oculto garantiza compatibilidad con formularios HTML.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <div class="flex items-center gap-3">
            <x-ui.switch id="airplane-mode" name="airplane_mode" />
            <x-ui.label for="airplane-mode">Airplane Mode</x-ui.label>
        </div>
    </section>

    <x-ui.separator />

    {{-- On / Off / Disabled --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-2">
                <x-ui.switch />
                <span class="text-sm">Off</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch :checked="true" />
                <span class="text-sm">On</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch disabled />
                <span class="text-sm text-muted-foreground">Disabled</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch :checked="true" disabled />
                <span class="text-sm text-muted-foreground">On + disabled</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-2">
                <x-ui.switch state="destructive" :checked="true" />
                <span class="text-sm text-destructive">Destructive</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch state="success" :checked="true" />
                <span class="text-sm text-success">Success</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch state="warning" :checked="true" />
                <span class="text-sm text-warning">Warning</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch state="info" :checked="true" />
                <span class="text-sm text-info">Info</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Tamaños --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Tamaños</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-2">
                <x-ui.switch size="sm" :checked="true" />
                <span class="text-sm">sm</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.switch size="md" :checked="true" />
                <span class="text-sm">md (default)</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — configuración</x-ui.typography>
        <x-ui.card class="max-w-sm">
            <x-ui.card.header>
                <x-ui.card.title>Preferencias</x-ui.card.title>
                <x-ui.card.description>Configurá tu experiencia.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content class="space-y-4">
                @foreach([
                    ['id' => 'sw-dark',     'label' => 'Modo oscuro',            'desc' => 'Usar tema oscuro en la interfaz.',    'on' => false],
                    ['id' => 'sw-notifs',   'label' => 'Notificaciones',          'desc' => 'Recibir alertas en tiempo real.',     'on' => true],
                    ['id' => 'sw-2fa',      'label' => 'Autenticación 2FA',       'desc' => 'Requerir código al iniciar sesión.',  'on' => false],
                    ['id' => 'sw-public',   'label' => 'Perfil público',          'desc' => 'Visible para todos los usuarios.',    'on' => true],
                ] as $item)
                    <div class="flex items-center justify-between gap-4">
                        <div class="space-y-0.5 min-w-0">
                            <x-ui.label for="{{ $item['id'] }}" class="cursor-pointer">{{ $item['label'] }}</x-ui.label>
                            <x-ui.typography as="muted" class="text-xs">{{ $item['desc'] }}</x-ui.typography>
                        </div>
                        <x-ui.switch id="{{ $item['id'] }}" :checked="$item['on']" size="sm" class="shrink-0" />
                    </div>
                @endforeach
            </x-ui.card.content>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
