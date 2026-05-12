@props([
    'size'  => 'md',   // sm | md | lg
    'state' => null,   // null | destructive | success | warning | info
])

@php
$base = 'flex w-full rounded-md border bg-background text-foreground shadow-xs transition-colors placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-y';

$sizeClass = match($size) {
    'sm'    => 'min-h-[80px] px-3 py-2 text-[13px]',
    'lg'    => 'min-h-[160px] px-4 py-3 text-base',
    default => 'min-h-[120px] px-3 py-2 text-sm',
};

$stateClass = match($state) {
    'success'     => 'border-success-border focus-visible:ring-success',
    'warning'     => 'border-warning-border focus-visible:ring-warning',
    'info'        => 'border-info-border focus-visible:ring-info',
    'destructive' => 'border-destructive-border focus-visible:ring-destructive',
    default       => 'border-input focus-visible:ring-ring',
};
@endphp

<textarea {{ $attributes->twMerge($base, $sizeClass, $stateClass) }}>{{ $slot }}</textarea>
