@props(['open' => false])

<div x-data="{ open: {{ $open ? 'true' : 'false' }} }" {{ $attributes }}>
    {{ $slot }}
</div>
