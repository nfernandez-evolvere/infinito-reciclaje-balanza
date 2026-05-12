@props([
    'value'   => 0,      // 0–100
    'size'    => 'md',   // sm | md | lg
    'state'   => null,   // null | success | warning | destructive | info
    'label'   => null,   // aria-label descriptivo
])

@php
$trackClass = match($size) {
    'sm'    => 'h-1.5',
    'lg'    => 'h-4',
    default => 'h-2.5',
};

$fillClass = match($state) {
    'success'     => 'bg-success',
    'warning'     => 'bg-warning',
    'destructive' => 'bg-destructive',
    'info'        => 'bg-info',
    default       => 'bg-primary',
};

$pct = max(0, min(100, (int) $value));
@endphp

<div
    {{ $attributes->twMerge('relative w-full overflow-hidden rounded-full bg-secondary', $trackClass) }}
    role="progressbar"
    aria-valuenow="{{ $pct }}"
    aria-valuemin="0"
    aria-valuemax="100"
    @if($label) aria-label="{{ $label }}" @endif
>
    <div
        class="h-full rounded-full transition-all duration-500 ease-out {{ $fillClass }}"
        style="width: {{ $pct }}%"
    ></div>
</div>
