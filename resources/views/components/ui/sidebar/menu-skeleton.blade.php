@props(['showIcon' => false])

<div
    {{ $attributes->twMerge('flex h-8 items-center gap-2 rounded-md px-2') }}
>
    @if($showIcon)
        <x-ui.skeleton class="size-4 rounded-md" />
    @endif
    <x-ui.skeleton class="h-4 max-w-[--skeleton-width] flex-1" style="--skeleton-width: 140px" />
</div>
