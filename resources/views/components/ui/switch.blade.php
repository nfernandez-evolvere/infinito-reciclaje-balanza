@props([
    'checked' => false,
    'name'    => null,
    'value'   => 'on',
    'size'    => 'md',   // sm | md
    'state'   => null,   // null | destructive | success | warning | info
])

@php
$trackSize = match($size) {
    'sm'    => 'h-5 w-9',
    default => 'h-6 w-11',
};
$thumbSize = match($size) {
    'sm'    => 'size-4 data-[state=checked]:translate-x-4',
    default => 'size-5 data-[state=checked]:translate-x-5',
};
$checkedBg = match($state) {
    'destructive' => 'bg-destructive',
    'success'     => 'bg-success',
    'warning'     => 'bg-warning',
    'info'        => 'bg-info',
    default       => 'bg-primary',
};
$ringClass = match($state) {
    'destructive' => 'focus-visible:ring-destructive',
    'success'     => 'focus-visible:ring-success',
    'warning'     => 'focus-visible:ring-warning',
    'info'        => 'focus-visible:ring-info',
    default       => 'focus-visible:ring-ring',
};
@endphp

<button
    type="button"
    role="switch"
    x-data="{ checked: @js((bool) $checked) }"
    :aria-checked="checked.toString()"
    :data-state="checked ? 'checked' : 'unchecked'"
    @click="checked = !checked"
    :class="checked ? '{{ $checkedBg }}' : 'bg-input'"
    {{ $attributes->twMerge('relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', $trackSize, $ringClass) }}
>
    <span
        :data-state="checked ? 'checked' : 'unchecked'"
        class="pointer-events-none inline-block rounded-full bg-background shadow-sm ring-0 transition-transform translate-x-0 {{ $thumbSize }}"
    ></span>
    @if($name)
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            :checked="checked"
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        />
    @endif
</button>
