{{-- Scroll viewport: overflow-hidden outer + snapping inner flex --}}
<div {{ $attributes->twMerge('overflow-hidden') }} :class="orientation === 'vertical' ? 'h-full' : ''">
    <div
        x-ref="viewport"
        @scroll.passive="onScroll()"
        :class="orientation === 'horizontal'
            ? 'flex flex-row overflow-x-auto scrollbar-none'
            : 'flex flex-col overflow-y-auto scrollbar-none h-full'"
        :style="orientation === 'horizontal'
            ? 'scroll-snap-type:x mandatory'
            : 'scroll-snap-type:y mandatory'"
    >
        {{ $slot }}
    </div>
</div>
