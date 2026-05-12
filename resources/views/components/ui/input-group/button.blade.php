@props([
    'variant' => 'ghost',
    'size'    => 'sm',
])

<x-ui.button
    :variant="$variant"
    :size="$size"
    {{ $attributes->twMerge('shadow-none rounded-[calc(var(--radius-md)-2px)] my-0.5 mx-0.5') }}
>
    {{ $slot }}
</x-ui.button>
