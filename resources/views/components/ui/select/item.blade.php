@props([
    'value'    => '',
    'disabled' => false,
])

@php
// Label extraído del slot para registrarse con el parent via Alpine event
$itemLabel = trim(strip_tags($slot->toHtml()));
@endphp

<div
    role="option"
    :aria-selected="String(value) === @js($value)"
    x-data="{ itemValue: @js($value), itemLabel: @js($itemLabel), itemDisabled: @js((bool) $disabled) }"
    x-init="$dispatch('select-item-init', { value: itemValue, label: itemLabel, disabled: itemDisabled })"
    @click="!itemDisabled && select(itemValue)"
    @mouseenter="!itemDisabled && (focusIdx = items.findIndex(o => String(o.value) === itemValue))"
    :class="{
        'bg-accent text-accent-foreground': !itemDisabled && focusIdx === items.findIndex(o => String(o.value) === itemValue),
        'opacity-50 cursor-not-allowed pointer-events-none': itemDisabled,
        'cursor-pointer': !itemDisabled,
    }"
    {{ $attributes->twMerge('relative flex items-center select-none outline-none rounded-sm pl-8 pr-2 py-1.5 text-sm hover:bg-primary/10') }}
>
    {{-- Checkmark --}}
    <span
        class="absolute left-2 flex items-center justify-center size-4"
        x-show="String(value) === itemValue"
        aria-hidden="true"
    >
        <x-lucide-check class="size-3.5" stroke-width="2.5" />
    </span>

    {{ $slot }}
</div>
