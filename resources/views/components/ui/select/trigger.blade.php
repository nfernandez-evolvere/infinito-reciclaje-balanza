@props([
    'size'     => 'md',   // sm | md | lg
    'state'    => null,   // null | destructive | success | warning | info
    'disabled' => false,
])

@php
$sizeClass = match($size) {
    'sm'    => 'h-8 pl-3 pr-2 text-[13px] gap-1.5',
    'lg'    => 'h-12 pl-4 pr-3 text-base gap-2.5',
    default => 'h-10 pl-3 pr-2.5 text-sm gap-2',
};

$stateClass = match($state) {
    'destructive' => 'border-destructive-border focus-visible:ring-destructive',
    'success'     => 'border-success-border focus-visible:ring-success',
    'warning'     => 'border-warning-border focus-visible:ring-warning',
    'info'        => 'border-info-border focus-visible:ring-info',
    default       => 'border-input focus-visible:ring-ring',
};

$chevronSize = match($size) {
    'sm'    => 'size-3.5',
    'lg'    => 'size-5',
    default => 'size-4',
};
@endphp

<button
    type="button"
    role="combobox"
    x-ref="trigger"
    :aria-expanded="open.toString()"
    aria-haspopup="listbox"
    @click="toggle()"
    @if($disabled) disabled @endif
    {{ $attributes->twMerge(
        'w-full flex items-center justify-between whitespace-nowrap rounded-md border bg-background text-foreground shadow-xs transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer',
        $sizeClass,
        $stateClass
    ) }}
>
    {{ $slot }}
    <x-lucide-chevron-down class="shrink-0 text-muted-foreground transition-transform duration-200 {{ $chevronSize }}"
        x-bind:class="open ? 'rotate-180' : ''" />
</button>
