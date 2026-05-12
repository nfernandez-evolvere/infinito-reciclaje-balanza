{{-- Scrollable center area of the sidebar --}}
<div
    {{ $attributes->twMerge('flex min-h-0 flex-1 flex-col gap-2 overflow-auto py-2') }}
>
    {{ $slot }}
</div>
