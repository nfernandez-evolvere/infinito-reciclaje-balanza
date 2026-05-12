@props([])

<button type="button" @click="open = !open" {{ $attributes }}>
    {{ $slot }}
</button>
