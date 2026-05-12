@props(['default' => null])

<div x-data="{ active: '{{ $default }}' }" {{ $attributes }}>
    {{ $slot }}
</div>
