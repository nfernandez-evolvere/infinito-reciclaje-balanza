@props(['orientation' => 'horizontal', 'decorative' => true])

<div
    role="{{ $decorative ? 'none' : 'separator' }}"
    @if(!$decorative) aria-orientation="{{ $orientation }}" @endif
    {{ $attributes->merge(['class' => $orientation === 'horizontal'
        ? 'shrink-0 bg-border h-px w-full'
        : 'shrink-0 bg-border w-px h-full']) }}
></div>
