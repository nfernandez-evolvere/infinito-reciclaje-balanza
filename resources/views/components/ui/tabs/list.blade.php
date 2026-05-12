<div role="tablist" :aria-orientation="orientation" :data-orientation="orientation" {{ $attributes->twMerge('inline-flex items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground') }}
    :class="orientation === 'vertical' ? 'flex-col h-auto w-fit' : 'flex-row h-9'">
    {{ $slot }}
</div>