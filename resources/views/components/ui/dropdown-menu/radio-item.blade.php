@props([
    'value'    => '',
    'disabled' => false,
])

<div
    role="menuitemradio"
    tabindex="-1"
    :aria-checked="radioValue === @js($value) ? 'true' : 'false'"
    :data-state="radioValue === @js($value) ? 'checked' : 'unchecked'"
    @if($disabled)
        aria-disabled="true"
        data-disabled
    @else
        @click="radioValue = @js($value)"
        @keydown.enter.prevent="radioValue = @js($value)"
    @endif
    @focus="$el.setAttribute('data-highlighted', '')"
    @blur="$el.removeAttribute('data-highlighted')"
    {{ $attributes->twMerge(
        'relative flex cursor-pointer items-center gap-1.5 rounded-md py-1.5 pl-2 pr-8 text-sm outline-none select-none transition-colors',
        'hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground',
        $disabled ? 'pointer-events-none opacity-50' : ''
    ) }}
>
    {{ $slot }}
    <span class="pointer-events-none absolute right-2 flex size-4 items-center justify-center">
        <span
            x-show="radioValue === @js($value)"
            class="size-2 rounded-full bg-current"
        ></span>
    </span>
</div>
