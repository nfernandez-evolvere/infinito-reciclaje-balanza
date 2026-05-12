@props([
    'value'    => '',
    'disabled' => false,
])

<div
    x-data="{ itemValue: @js($value), itemDisabled: @js((bool) $disabled) }"
    :data-state="isOpen(itemValue) ? 'open' : 'closed'"
    :data-disabled="(itemDisabled || accordionDisabled) ? '' : undefined"
    :data-orientation="orientation"
    {{ $attributes->twMerge('border-b border-border') }}
>
    {{ $slot }}
</div>
