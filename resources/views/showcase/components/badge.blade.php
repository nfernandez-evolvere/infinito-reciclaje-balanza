<x-layouts.showcase title="Badge — Showcase">
<div class="mx-auto max-w-[--container-lg] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Badge</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Etiqueta compacta para estados, categorías y contadores. Soporta variantes de forma y estados semánticos en dos estilos: filled y subtle.</x-ui.typography>
    </div>

    {{-- Variantes base --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Variantes</x-ui.typography>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.badge>Default</x-ui.badge>
            <x-ui.badge variant="secondary">Secondary</x-ui.badge>
            <x-ui.badge variant="outline">Outline</x-ui.badge>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados filled --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados — filled</x-ui.typography>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.badge state="destructive">Error</x-ui.badge>
            <x-ui.badge state="success">Completado</x-ui.badge>
            <x-ui.badge state="warning">Pendiente</x-ui.badge>
            <x-ui.badge state="info">Información</x-ui.badge>
        </div>
    </section>

    <x-ui.separator />

    {{-- Estados subtle --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados — subtle</x-ui.typography>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.badge state="destructive" :subtle="true">Error</x-ui.badge>
            <x-ui.badge state="success"     :subtle="true">Completado</x-ui.badge>
            <x-ui.badge state="warning"     :subtle="true">Pendiente</x-ui.badge>
            <x-ui.badge state="info"        :subtle="true">Información</x-ui.badge>
        </div>
    </section>

    <x-ui.separator />

    {{-- En contexto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">En contexto</x-ui.typography>
        <div class="flex flex-wrap gap-4 items-center">
            <span class="flex items-center gap-2 text-sm font-medium">
                Pedido #1042
                <x-ui.badge state="success" :subtle="true">Enviado</x-ui.badge>
            </span>
            <span class="flex items-center gap-2 text-sm font-medium">
                Pago
                <x-ui.badge state="warning" :subtle="true">Pendiente</x-ui.badge>
            </span>
            <span class="flex items-center gap-2 text-sm font-medium">
                Cuenta
                <x-ui.badge state="destructive" :subtle="true">Suspendida</x-ui.badge>
            </span>
            <span class="flex items-center gap-2 text-sm font-medium">
                Plan
                <x-ui.badge variant="outline">Pro</x-ui.badge>
            </span>
        </div>
    </section>

</div>
</x-layouts.showcase>
