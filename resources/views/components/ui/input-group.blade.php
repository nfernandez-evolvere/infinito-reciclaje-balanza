{{--
    Wrapper que fusiona un input con addons (íconos, texto, botones).
    El ring y el borde de foco se aplican al grupo completo, no al input.

    Uso:
    <x-ui.input-group>
        <x-ui.input-group.addon>
            <x-lucide-search />
        </x-ui.input-group.addon>
        <x-ui.input-group.input placeholder="Buscar..." />
    </x-ui.input-group>
--}}
<div
    role="group"
    {{ $attributes->twMerge(
        'flex h-10 w-full items-center rounded-lg border border-input bg-background transition-colors',
        'has-[[data-slot=input-group-control]:focus-visible]:border-ring has-[[data-slot=input-group-control]:focus-visible]:ring-2 has-[[data-slot=input-group-control]:focus-visible]:ring-ring/50',
        'has-[[data-slot=input-group-control][aria-invalid=true]]:border-destructive has-[[data-slot=input-group-control][aria-invalid=true]]:ring-2 has-[[data-slot=input-group-control][aria-invalid=true]]:ring-destructive/20',
        'has-[>[data-align=block-start]]:h-auto has-[>[data-align=block-start]]:flex-col',
        'has-[>[data-align=block-end]]:h-auto has-[>[data-align=block-end]]:flex-col',
        'has-[>textarea]:h-auto',
        'has-disabled:opacity-50'
    ) }}
>
    {{ $slot }}
</div>
