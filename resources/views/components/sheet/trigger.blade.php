@props([])

<div @click.stop="open = true" {{ $attributes }}>
    {{ $slot }}
</div>
