@props([])

<div x-data="{ open: false }" class="relative inline-block" {{ $attributes }}>
    {{ $slot }}
</div>
