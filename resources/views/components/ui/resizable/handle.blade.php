@props([
    'withHandle' => false,   // shows the grip icon
    'index'      => 0,       // which handle position (0 = between panel 0 and 1)
])

<div
    data-panel-handle
    data-index="{{ $index }}"
    @pointerdown="dragStart($event, @js((int) $index))"
    @pointermove="dragMove($event, @js((int) $index))"
    @pointerup="dragEnd()"
    @pointercancel="dragEnd()"
    :data-resizing="dragging && handleIdx === @js((int) $index) ? '' : undefined"
    :class="direction === 'horizontal'
        ? 'w-px cursor-col-resize flex items-center justify-center'
        : 'h-px cursor-row-resize flex items-center justify-center'"
    {{ $attributes->twMerge(
        'relative z-10 shrink-0 bg-border transition-colors touch-none',
        'focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring',
        'data-[resizing]:bg-ring'
    ) }}
    tabindex="0"
    role="separator"
    :aria-orientation="direction"
>
    @if($withHandle)
        <div
            :class="direction === 'horizontal'
                ? 'z-10 flex h-6 w-3.5 items-center justify-center rounded-sm border bg-border'
                : 'z-10 flex w-6 h-3.5 items-center justify-center rounded-sm border bg-border rotate-90'"
        >
            <x-lucide-grip-vertical class="size-2.5" />
        </div>
    @endif
</div>
