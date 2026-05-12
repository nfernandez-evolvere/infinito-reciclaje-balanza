@props([
    'value' => null,
    'name'  => null,
    'state' => null,  // null | destructive | success | warning | info
])

<div
    role="radiogroup"
    x-data="{ value: @js($value), name: @js($name), state: @js($state) }"
    {{ $attributes->twMerge('grid gap-2') }}
>
    {{ $slot }}
</div>
