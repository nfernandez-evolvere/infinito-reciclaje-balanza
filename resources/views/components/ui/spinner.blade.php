@props([
    'size'  => 'md',    // sm | md | lg
    'state' => null,    // null | destructive | success | warning | info
])

@php
$sizeClass = match($size) {
    'sm'    => 'size-4',
    'lg'    => 'size-8',
    default => 'size-5',
};

$colorClass = match($state) {
    'success'     => 'text-success',
    'warning'     => 'text-warning',
    'info'        => 'text-info',
    'destructive' => 'text-destructive',
    default       => 'text-muted-foreground',
};
@endphp

<svg
    {{ $attributes->twMerge('animate-spin', $sizeClass, $colorClass) }}
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    viewBox="0 0 24 24"
    aria-hidden="true"
>
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
</svg>
