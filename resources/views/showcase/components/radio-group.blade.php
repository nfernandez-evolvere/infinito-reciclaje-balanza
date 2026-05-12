<x-layouts.showcase title="Radio Group — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Radio Group</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Grupo de opciones mutuamente excluyentes. El estado se gestiona con Alpine.js y los inputs ocultos garantizan compatibilidad con formularios HTML.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <x-ui.radio-group name="plan" value="starter">
            <x-ui.radio-group.item value="starter" id="plan-starter">Starter</x-ui.radio-group.item>
            <x-ui.radio-group.item value="pro"     id="plan-pro">Pro</x-ui.radio-group.item>
            <x-ui.radio-group.item value="team"    id="plan-team">Team</x-ui.radio-group.item>
        </x-ui.radio-group>
    </section>

    <x-ui.separator />

    {{-- Sin selección inicial --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Sin selección inicial</x-ui.typography>
        <x-ui.radio-group name="color">
            <x-ui.radio-group.item value="red"   id="color-red">Rojo</x-ui.radio-group.item>
            <x-ui.radio-group.item value="green" id="color-green">Verde</x-ui.radio-group.item>
            <x-ui.radio-group.item value="blue"  id="color-blue">Azul</x-ui.radio-group.item>
        </x-ui.radio-group>
    </section>

    <x-ui.separator />

    {{-- Horizontal --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Horizontal</x-ui.typography>
        <x-ui.radio-group name="size" value="md" class="flex flex-row flex-wrap gap-6">
            <x-ui.radio-group.item value="sm" id="size-sm">Small</x-ui.radio-group.item>
            <x-ui.radio-group.item value="md" id="size-md">Medium</x-ui.radio-group.item>
            <x-ui.radio-group.item value="lg" id="size-lg">Large</x-ui.radio-group.item>
        </x-ui.radio-group>
    </section>

    <x-ui.separator />

    {{-- Semantic states --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Semantic States</x-ui.typography>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">

            {{-- Destructive --}}
            <div class="space-y-2">
                <x-ui.typography as="p" class="font-medium">Destructive</x-ui.typography>
                <x-ui.radio-group name="state-destructive" value="a" state="destructive">
                    <x-ui.radio-group.item value="a" id="sd-a">Opción A</x-ui.radio-group.item>
                    <x-ui.radio-group.item value="b" id="sd-b">Opción B</x-ui.radio-group.item>
                </x-ui.radio-group>
                <x-ui.typography as="muted" class="text-xs text-destructive">Seleccioná una opción válida para continuar.</x-ui.typography>
            </div>

            {{-- Success --}}
            <div class="space-y-2">
                <x-ui.typography as="p" class="font-medium">Success</x-ui.typography>
                <x-ui.radio-group name="state-success" value="a" state="success">
                    <x-ui.radio-group.item value="a" id="ss-a">Opción A</x-ui.radio-group.item>
                    <x-ui.radio-group.item value="b" id="ss-b">Opción B</x-ui.radio-group.item>
                </x-ui.radio-group>
                <x-ui.typography as="muted" class="text-xs text-success">Selección confirmada correctamente.</x-ui.typography>
            </div>

            {{-- Warning --}}
            <div class="space-y-2">
                <x-ui.typography as="p" class="font-medium">Warning</x-ui.typography>
                <x-ui.radio-group name="state-warning" value="b" state="warning">
                    <x-ui.radio-group.item value="a" id="sw-a">Opción A</x-ui.radio-group.item>
                    <x-ui.radio-group.item value="b" id="sw-b">Opción B</x-ui.radio-group.item>
                </x-ui.radio-group>
                <x-ui.typography as="muted" class="text-xs text-warning">Esta opción requiere confirmación adicional.</x-ui.typography>
            </div>

            {{-- Info --}}
            <div class="space-y-2">
                <x-ui.typography as="p" class="font-medium">Info</x-ui.typography>
                <x-ui.radio-group name="state-info" value="a" state="info">
                    <x-ui.radio-group.item value="a" id="si-a">Opción A</x-ui.radio-group.item>
                    <x-ui.radio-group.item value="b" id="si-b">Opción B</x-ui.radio-group.item>
                </x-ui.radio-group>
                <x-ui.typography as="muted" class="text-xs text-info">El plan seleccionado incluye período de prueba.</x-ui.typography>
            </div>

        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto — formulario --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto — selección de plan</x-ui.typography>
        <x-ui.card class="max-w-sm">
            <x-ui.card.header>
                <x-ui.card.title>Elegí tu plan</x-ui.card.title>
                <x-ui.card.description>Podés cambiar tu plan en cualquier momento.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-ui.radio-group name="billing_plan" value="pro" class="gap-3">
                    @foreach([
                        ['value' => 'free',  'id' => 'bp-free',  'label' => 'Free',       'desc' => 'Para proyectos personales'],
                        ['value' => 'pro',   'id' => 'bp-pro',   'label' => 'Pro',         'desc' => '$12 / mes · Todo lo de Free + más'],
                        ['value' => 'team',  'id' => 'bp-team',  'label' => 'Team',        'desc' => '$49 / mes · Colaboración ilimitada'],
                    ] as $plan)
                        <label
                            for="{{ $plan['id'] }}"
                            class="flex items-start gap-3 rounded-lg border border-border p-3 cursor-pointer transition-colors hover:bg-muted/50"
                        >
                            <x-ui.radio-group.item
                                value="{{ $plan['value'] }}"
                                id="{{ $plan['id'] }}"
                                class="mt-0.5"
                            />
                            <div class="space-y-0.5">
                                <x-ui.typography as="small">{{ $plan['label'] }}</x-ui.typography>
                                <x-ui.typography as="muted" class="text-xs">{{ $plan['desc'] }}</x-ui.typography>
                            </div>
                        </label>
                    @endforeach
                </x-ui.radio-group>
            </x-ui.card.content>
            <x-ui.card.footer>
                <x-ui.button size="sm">Confirmar plan</x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </section>

</div>
</x-layouts.showcase>
