<div
    x-data="{ subOpen: false }"
    @mouseenter="subOpen = true"
    @mouseleave="subOpen = false"
    class="relative"
    {{ $attributes }}
>
    {{ $slot }}
</div>
