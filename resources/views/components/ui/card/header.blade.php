@props(['actions' => null])

<div {{ $attributes->twMerge('flex items-start justify-between') }}>
    <div class="flex flex-col gap-1.5">
        {{ $slot }}
    </div>
    @if($actions)
        <div class="flex items-center gap-1 shrink-0 -mt-1">
            {{ $actions }}
        </div>
    @endif
</div>
