@props(['open' => false, 'disabled' => false])

<div
    x-data="{
        open:     @js((bool) $open),
        disabled: @js((bool) $disabled),
        toggle()  { if (!this.disabled) this.open = !this.open; }
    }"
    :data-state="open ? 'open' : 'closed'"
    :data-disabled="disabled ? '' : undefined"
    {{ $attributes->twMerge('') }}
>
    {{ $slot }}
</div>
