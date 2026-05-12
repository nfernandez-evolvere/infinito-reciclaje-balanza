<x-ui.button
    variant="ghost"
    size="icon"
    @click="toggle()"
    x-bind:aria-expanded="(!isCollapsed).toString()"
    aria-label="Toggle sidebar"
    {{ $attributes->class('text-muted-foreground') }}
>
    <x-lucide-panel-left class="size-4" />
    {{ $slot }}
</x-ui.button>
