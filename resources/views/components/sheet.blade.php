@props(['open' => false])

<div x-data="{ open: {{ $open ? 'true' : 'false' }}, previousFocus: null }" {{ $attributes }}>
    {{ $slot }}
</div>
