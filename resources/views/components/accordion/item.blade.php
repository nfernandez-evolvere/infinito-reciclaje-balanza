@props(['value'])

<div x-data="{ value: '{{ $value }}' }" {{ $attributes->merge(['class' => 'border-b']) }}>
    {{ $slot }}
</div>
