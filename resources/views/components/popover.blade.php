@props(['align' => 'start'])

@php
    $aligns = [
        'start'  => 'left-0',
        'end'    => 'right-0',
        'center' => 'left-1/2 -translate-x-1/2',
    ];
@endphp

<div x-data="{ open: false }" class="relative inline-block" {{ $attributes }}>
    {{ $slot }}
</div>
