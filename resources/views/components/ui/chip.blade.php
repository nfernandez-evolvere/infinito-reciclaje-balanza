@props([
    'href'      => null,
    'removable' => true,
])

@php
$base = 'inline-flex items-center gap-1 rounded-md border border-border bg-muted px-2 py-1 text-xs font-medium text-foreground transition-colors';
$interactive = $href || $removable
    ? 'hover:border-destructive/40 hover:bg-destructive/10 hover:text-destructive [&_svg]:text-muted-foreground hover:[&_svg]:text-destructive'
    : '';
$tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }} {{ $attributes->twMerge($base, $interactive) }} @if($href) href="{{ $href }}" @endif>
    {{ $slot }}
    @if($removable)
        <x-lucide-x class="size-3 shrink-0 transition-colors" />
    @endif
</{{ $tag }}>
