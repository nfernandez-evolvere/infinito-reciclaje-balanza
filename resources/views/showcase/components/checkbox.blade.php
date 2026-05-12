<x-layouts.showcase title="Checkbox — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Checkbox</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Checkbox accesible con estado gestionado por Alpine.js. El input oculto garantiza compatibilidad con formularios HTML.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <div class="flex items-center gap-2">
            <x-ui.checkbox id="terms" name="terms" />
            <x-ui.label for="terms">Accept terms and conditions</x-ui.label>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Checked / Unchecked</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-2">
                <x-ui.checkbox />
                <span class="text-sm">Unchecked</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox :checked="true" />
                <span class="text-sm">Checked</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox disabled />
                <span class="text-sm text-muted-foreground">Disabled</span>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox :checked="true" disabled />
                <span class="text-sm text-muted-foreground">Checked + disabled</span>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <div class="flex flex-wrap gap-6 items-center">
            <div class="flex items-center gap-2">
                <x-ui.checkbox state="destructive" />
                <x-ui.label state="destructive">Destructive</x-ui.label>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox state="success" :checked="true" />
                <x-ui.label state="success">Success</x-ui.label>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox state="warning" />
                <x-ui.label state="warning">Warning</x-ui.label>
            </div>
            <div class="flex items-center gap-2">
                <x-ui.checkbox state="info" />
                <x-ui.label state="info">Info</x-ui.label>
            </div>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto — lista de opciones --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — preferencias</x-ui.typography>
        <x-ui.card class="max-w-sm">
            <x-ui.card.header>
                <x-ui.card.title>Notificaciones</x-ui.card.title>
                <x-ui.card.description>Elegí qué alertas querés recibir.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content class="space-y-3">
                @foreach([
                    ['id' => 'notif-email',   'label' => 'Emails de actividad',   'checked' => true],
                    ['id' => 'notif-push',    'label' => 'Notificaciones push',   'checked' => false],
                    ['id' => 'notif-weekly',  'label' => 'Resumen semanal',       'checked' => true],
                    ['id' => 'notif-updates', 'label' => 'Novedades del producto','checked' => false],
                ] as $item)
                    <div class="flex items-center gap-2">
                        <x-ui.checkbox
                            id="{{ $item['id'] }}"
                            name="{{ $item['id'] }}"
                            :checked="$item['checked']"
                        />
                        <x-ui.label for="{{ $item['id'] }}">{{ $item['label'] }}</x-ui.label>
                    </div>
                @endforeach
            </x-ui.card.content>
            <x-ui.card.footer>
                <x-ui.button size="sm">Guardar preferencias</x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
