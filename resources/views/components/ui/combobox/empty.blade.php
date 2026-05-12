@props([
    'message' => null,   // overrides root emptyMessage
])

{{--
    Shown when no items match the current search.
    matchCount is updated by the root after every search keystroke via $watch + $nextTick.
--}}
<div
    x-show="matchCount === 0"
    {{ $attributes->twMerge('py-6 text-center text-sm text-muted-foreground select-none') }}
>
    @if(!$slot->isEmpty())
        {{ $slot }}
    @else
        <span x-text="@js($message) ?? emptyMessage"></span>
    @endif
</div>
