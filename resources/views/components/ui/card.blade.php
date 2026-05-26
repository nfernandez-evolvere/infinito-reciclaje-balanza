@props([
    'as'          => 'div',
    'variant'     => 'default',
    'collapsible' => false,
    'startOpen'   => true,
])

@php
    $extra = $collapsible
        ? ['x-data' => '{ open: ' . ($startOpen ? 'true' : 'false') . ' }']
        : [];

    $variantClass = match($variant) {
        'elevated' => 'shadow-lg',
        default    => 'border border-border',
    };
@endphp

<{{ $as }} {{ $attributes->merge($extra)->twMerge('flex flex-col gap-2 rounded-xl bg-card text-card-foreground py-4 px-6 ' . $variantClass) }}>
    {{ $slot }}
</{{ $as }}>
