@props(['title', 'description' => null])

<div {{ $attributes->twMerge('flex items-start justify-between gap-4') }}>
    <div class="min-w-0">
        <x-ui.typography as="h3" element="h1" class="truncate">{{ $title }}</x-ui.typography>
        @if($description)
            <x-ui.typography as="muted" class="mt-1">{{ $description }}</x-ui.typography>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
