@props([])

<li role="presentation" aria-hidden="true" class="[&>svg]:w-3.5 [&>svg]:h-3.5">
    {{ $slot->isEmpty() ? '/' : $slot }}
</li>
