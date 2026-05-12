@props(['open' => false])

<div x-data="{ open: @js((bool) $open) }">
    {{ $slot }}
</div>
