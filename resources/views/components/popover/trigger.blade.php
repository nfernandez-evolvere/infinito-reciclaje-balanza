@props([])

<div @click.stop="open = !open" {{ $attributes }}>
    {{ $slot }}
</div>
