@props(['align' => 'center'])   {{-- start | center | end --}}

<nav
    data-navmenu
    x-data="{ active: null }"
    @keydown.escape.window="active && (active = null)"
    :aria-label="'Main'"
    {{ $attributes->twMerge('relative') }}
>
    {{ $slot }}
</nav>
