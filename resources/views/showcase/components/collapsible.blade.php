<x-layouts.showcase title="Collapsible — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1" class="text-3xl">Collapsible</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">Bloque de contenido que se expande y colapsa. Primitivo de un solo ítem — para múltiples ítems usar Accordion. Animación via CSS Grid.</x-ui.typography>
    </div>

    {{-- Default --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Default</x-ui.typography>
        <div class="max-w-sm space-y-2">
            <x-ui.collapsible>
                <div class="flex items-center justify-between">
                    <x-ui.typography as="p" class="font-medium">Repositorios con estrella</x-ui.typography>
                    <x-ui.collapsible.trigger>
                        <x-ui.button variant="ghost" size="icon" class="size-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15 12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"/>
                            </svg>
                        </x-ui.button>
                    </x-ui.collapsible.trigger>
                </div>
                <div class="rounded-md border border-border px-4 py-3 text-sm">
                    @nuxtjs/tailwindcss
                </div>
                <x-ui.collapsible.content>
                    <div class="space-y-2 mt-2">
                        <div class="rounded-md border border-border px-4 py-3 text-sm">@vitejs/plugin-react</div>
                        <div class="rounded-md border border-border px-4 py-3 text-sm">shadcn/ui</div>
                    </div>
                </x-ui.collapsible.content>
            </x-ui.collapsible>
        </div>
    </section>

    <x-ui.separator />

    {{-- Abierto por defecto --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Abierto por defecto</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.collapsible :open="true" class="border border-border rounded-lg">
                <x-ui.collapsible.trigger class="flex w-full items-center justify-between px-4 py-3 text-sm font-medium hover:bg-muted/50 transition-colors rounded-lg">
                    <span>Detalles del pedido</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-muted-foreground transition-transform [[data-state=open]_&]:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </x-ui.collapsible.trigger>
                <x-ui.collapsible.content class="border-t border-border">
                    <div class="px-4 py-3 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-muted-foreground">Producto</span><span>Plan Pro</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Período</span><span>Mensual</span></div>
                        <div class="flex justify-between"><span class="text-muted-foreground">Próximo cobro</span><span>15 jun 2026</span></div>
                        <div class="flex justify-between font-medium pt-2 border-t border-border"><span>Total</span><span>$99.00</span></div>
                    </div>
                </x-ui.collapsible.content>
            </x-ui.collapsible>
        </div>
    </section>

    <x-ui.separator />

    {{-- Deshabilitado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Deshabilitado</x-ui.typography>
        <div class="max-w-sm">
            <x-ui.collapsible disabled class="border border-border rounded-lg opacity-60">
                <x-ui.collapsible.trigger class="flex w-full items-center justify-between px-4 py-3 text-sm font-medium">
                    <span>Sección bloqueada</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                    </svg>
                </x-ui.collapsible.trigger>
                <x-ui.collapsible.content>
                    <x-ui.typography as="muted" class="px-4 pb-3">Contenido restringido.</x-ui.typography>
                </x-ui.collapsible.content>
            </x-ui.collapsible>
        </div>
    </section>

</div>
</x-layouts.showcase>
