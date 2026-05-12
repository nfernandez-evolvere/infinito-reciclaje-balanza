@props([])

<nav role="navigation" aria-label="pagination" {{ $attributes->merge(['class' => 'mx-auto flex w-full justify-center']) }}>
    {{ $slot }}
</nav>
