@props(['forceMount' => false])

{{--
    Animación de altura via CSS Grid trick (grid-template-rows: 0fr → 1fr).
    El contenido SIEMPRE está en el DOM (equivale a forceMount=true en Radix),
    solo se oculta visualmente colapsando la altura a cero.
    x-cloak previene el flash antes de que Alpine inicialice.
--}}
<div
    x-cloak
    :data-state="isOpen(itemValue) ? 'open' : 'closed'"
    :data-disabled="(itemDisabled || accordionDisabled) ? '' : undefined"
    :data-orientation="orientation"
    class="grid transition-[grid-template-rows] duration-300 ease-[cubic-bezier(0.87,0,0.13,1)] overflow-hidden"
    :style="`grid-template-rows: ${isOpen(itemValue) ? '1fr' : '0fr'}`"
    role="region"
>
    <div class="overflow-hidden">
        <div {{ $attributes->twMerge('pb-4 text-sm') }}>
            {{ $slot }}
        </div>
    </div>
</div>
