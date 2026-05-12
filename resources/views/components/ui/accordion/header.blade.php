{{--
    Wrapper semántico para el trigger. En shadcn el trigger lo incluye automáticamente,
    pero se expone como sub-componente para seguir el API de Radix.
--}}
<h3
    :data-state="isOpen(itemValue) ? 'open' : 'closed'"
    :data-disabled="(itemDisabled || accordionDisabled) ? '' : undefined"
    :data-orientation="orientation"
    {{ $attributes->twMerge('flex') }}
>
    {{ $slot }}
</h3>
