@props(['placeholder' => null])

<span
    x-text="label ?? @js($placeholder)"
    :class="!label ? 'text-muted-foreground' : ''"
    class="truncate pointer-events-none"
    {{ $attributes }}
></span>
