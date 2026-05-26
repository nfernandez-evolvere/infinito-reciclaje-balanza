@props(['actions' => null])

<div {{ $attributes->twMerge('flex items-start justify-between flex-1') }}>
    <div class="flex flex-col gap-1.5 flex-1">
        {{ $slot }}
    </div>
    @if($actions)
        <div class="flex items-center gap-1 shrink-0 -mt-1">
            {{ $actions }}
        </div>
    @endif
</div>
