@props([
    'state' => null,  // null | destructive | success | warning | info
])

@php
$base = 'text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70';

$stateClass = match($state) {
    'success' => 'text-success',
    'warning' => 'text-warning',
    'info'    => 'text-info',
    'destructive' => 'text-destructive',
    default   => 'text-foreground',
};
@endphp

<label {{ $attributes->twMerge($base, $stateClass) }}>
    {{ $slot }}
</label>
