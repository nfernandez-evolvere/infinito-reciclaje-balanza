<x-layouts.showcase title="Card — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Card</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Contenedor de superficie con sombra sutil. Compuesto por sub-elementos: header, title, description, content y footer.</x-ui.typography>
    </div>

    {{-- Anatomía completa --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Anatomía completa</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Título de la tarjeta</x-ui.card.title>
                    <x-ui.card.description>Descripción breve que contextualiza el contenido.</x-ui.card.description>
                </x-ui.card.header>
                <x-ui.card.content>
                    <x-ui.typography as="muted">El contenido principal va aquí. Puede incluir texto, formularios, imágenes u otros componentes.</x-ui.typography>
                </x-ui.card.content>
                <x-ui.card.footer class="gap-3">
                    <x-ui.button size="sm">Guardar</x-ui.button>
                    <x-ui.button size="sm" variant="outline">Cancelar</x-ui.button>
                </x-ui.card.footer>
            </x-ui.card>
        </div>
    </section>

    <x-ui.separator />

    {{-- Solo header + content --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Header + content</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.card>
                <x-ui.card.header>
                    <x-ui.card.title>Notificaciones</x-ui.card.title>
                    <x-ui.card.description>Configurá cómo querés recibir alertas.</x-ui.card.description>
                </x-ui.card.header>
                <x-ui.card.content class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Emails</span>
                        <x-ui.badge state="success" :subtle="true">Activo</x-ui.badge>
                    </div>
                    <x-ui.separator />
                    <div class="flex items-center justify-between">
                        <span class="text-sm">Push</span>
                        <x-ui.badge variant="outline">Inactivo</x-ui.badge>
                    </div>
                    <x-ui.separator />
                    <div class="flex items-center justify-between">
                        <span class="text-sm">SMS</span>
                        <x-ui.badge state="warning" :subtle="true">Pendiente</x-ui.badge>
                    </div>
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </section>

    <x-ui.separator />

    {{-- Grilla de cards --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Grilla</x-ui.typography>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                ['title' => 'Ingresos', 'value' => '$24,500', 'delta' => '+12%', 'state' => 'success'],
                ['title' => 'Usuarios activos', 'value' => '1,284', 'delta' => '+3%', 'state' => 'info'],
                ['title' => 'Tasa de rebote', 'value' => '42%', 'delta' => '-5%', 'state' => 'destructive'],
            ] as $metric)
                <x-ui.card>
                    <x-ui.card.header>
                        <x-ui.card.description>{{ $metric['title'] }}</x-ui.card.description>
                        <x-ui.card.title class="text-2xl tabular-nums">{{ $metric['value'] }}</x-ui.card.title>
                    </x-ui.card.header>
                    <x-ui.card.content>
                        <x-ui.badge state="{{ $metric['state'] }}" :subtle="true">{{ $metric['delta'] }} vs mes anterior</x-ui.badge>
                    </x-ui.card.content>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    <x-ui.separator />

    {{-- Card sin header --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Solo content</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.card>
                <x-ui.card.content class="pt-6">
                    <x-ui.typography as="muted">Una card simple sin header, solo contenido directo.</x-ui.typography>
                </x-ui.card.content>
            </x-ui.card>
        </div>
    </section>

</div>
</x-layouts.showcase>
