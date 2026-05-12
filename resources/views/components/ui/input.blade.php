@props([
    'size'  => 'md',   // sm | md | lg
    'state' => null,   // null | destructive | success | warning | info
])

@php
$hasLeading  = isset($leading)  && $leading->isNotEmpty();
$hasTrailing = isset($trailing) && $trailing->isNotEmpty();
$hasIcons    = $hasLeading || $hasTrailing;

$base = 'flex w-full rounded-md border bg-background text-foreground shadow-xs transition-colors file:border-0 file:bg-transparent file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50';

// Padding se ajusta cuando hay íconos para no solapar el contenido
$sizeClass = match($size) {
    'sm'    => 'h-8 text-[13px] '  . ($hasLeading ? 'pl-8'  : 'pl-3') . ' ' . ($hasTrailing ? 'pr-8'  : 'pr-3'),
    'lg'    => 'h-12 text-base '   . ($hasLeading ? 'pl-11' : 'pl-4') . ' ' . ($hasTrailing ? 'pr-11' : 'pr-4'),
    default => 'h-10 text-sm '     . ($hasLeading ? 'pl-9'  : 'pl-3') . ' ' . ($hasTrailing ? 'pr-9'  : 'pr-3'),
};

$stateClass = match($state) {
    'success'     => 'border-success-border focus-visible:ring-success',
    'warning'     => 'border-warning-border focus-visible:ring-warning',
    'info'        => 'border-info-border focus-visible:ring-info',
    'destructive' => 'border-destructive-border focus-visible:ring-destructive',
    default       => 'border-input focus-visible:ring-ring',
};

$ariaInvalid = $state === 'destructive' ? 'true' : null;

// Tamaño del ícono y su posición según size
$iconSize = match($size) {
    'sm'    => 'size-3.5',
    'lg'    => 'size-[18px]',
    default => 'size-4',
};
$iconPos = match($size) {
    'sm'    => ['left' => 'left-2.5',  'right' => 'right-2.5'],
    'lg'    => ['left' => 'left-3.5',  'right' => 'right-3.5'],
    default => ['left' => 'left-3',    'right' => 'right-3'],
};

// El ícono adopta el color semántico del estado
$iconColor = match($state) {
    'destructive' => 'text-destructive',
    'success'     => 'text-success',
    'warning'     => 'text-warning',
    'info'        => 'text-info',
    default       => 'text-muted-foreground',
};
@endphp

@if($hasIcons)
    <div class="relative flex items-center w-full">

        @if($hasLeading)
            <span class="pointer-events-none absolute {{ $iconPos['left'] }} flex items-center {{ $iconColor }} {{ $iconSize }}">
                {{ $leading }}
            </span>
        @endif

        <input
            {{ $attributes->twMerge($base, $sizeClass, $stateClass)->merge(['aria-invalid' => $ariaInvalid]) }}
        />

        @if($hasTrailing)
            <span class="pointer-events-none absolute {{ $iconPos['right'] }} flex items-center {{ $iconColor }} {{ $iconSize }}">
                {{ $trailing }}
            </span>
        @endif

    </div>
@else
    <input
        {{ $attributes->twMerge($base, $sizeClass, $stateClass)->merge(['aria-invalid' => $ariaInvalid]) }}
    />
@endif
