@props([
    'size'     => 'md',   // sm | md | lg
    'state'    => null,   // null | destructive | success | warning | info
    'disabled' => false,
])

@php
$sizeClass = match($size) {
    'sm'    => 'h-8 px-3 text-[13px]',
    'lg'    => 'h-12 px-4 text-base',
    default => 'h-10 px-3 text-sm',
};

$stateClass = match($state) {
    'destructive' => 'border-destructive-border focus:ring-destructive',
    'success'     => 'border-success-border focus:ring-success',
    'warning'     => 'border-warning-border focus:ring-warning',
    'info'        => 'border-info-border focus:ring-info',
    default       => 'border-input focus:ring-ring',
};
@endphp

<select
    @if($disabled) disabled @endif
    {{ $attributes->twMerge(
        'w-full appearance-none rounded-md border bg-background text-foreground shadow-xs transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'pr-8 bg-[image:--chevron-icon] bg-no-repeat bg-[right_0.625rem_center] bg-[length:1rem_1rem]',
        $sizeClass,
        $stateClass
    ) }}
    style="--chevron-icon: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E\")"
>
    {{ $slot }}
</select>
