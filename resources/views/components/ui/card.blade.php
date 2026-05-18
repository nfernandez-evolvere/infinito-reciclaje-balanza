@props([
    'as'          => 'div',
    'collapsible' => false,
    'startOpen'   => true,
])

@php
    $extra = $collapsible
        ? ['x-data' => '{ open: ' . ($startOpen ? 'true' : 'false') . ' }']
        : [];
@endphp

<{{ $as }} {{ $attributes->merge($extra)->twMerge('rounded-xl bg-card text-card-foreground shadow-lg') }}>
    {{ $slot }}
</{{ $as }}>
