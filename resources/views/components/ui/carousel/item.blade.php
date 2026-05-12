{{-- Each item takes full width/height of the viewport and snap-aligns --}}
<div
    role="group"
    aria-roledescription="slide"
    :class="orientation === 'horizontal'
        ? 'min-w-0 shrink-0 grow-0 basis-full'
        : 'min-h-0 shrink-0 grow-0 basis-full'"
    style="scroll-snap-align: start"
    {{ $attributes }}
>
    {{ $slot }}
</div>
