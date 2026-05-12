@props(['defaultSize' => 1])

<div
    data-panel
    style="flex-grow: {{ (float) $defaultSize }}; flex-shrink: 1; flex-basis: 0; overflow: hidden; min-width: 0; min-height: 0;"
    {{ $attributes }}
>
    {{ $slot }}
</div>
