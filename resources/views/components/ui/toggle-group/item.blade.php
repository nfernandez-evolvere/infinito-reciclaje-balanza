@props(['value' => '', 'disabled' => false])

@php
// Estos son los mismos defaults que toggle.blade.php — el grupo los sobreescribe via Alpine
$baseSizeMap = [
    'sm'   => 'h-8 px-2.5 text-[13px] gap-1.5 [&_svg]:size-3.5',
    'lg'   => 'h-12 px-5 text-base gap-2.5 [&_svg]:size-[18px]',
    'icon' => 'h-10 w-10 [&_svg]:size-4',
    'md'   => 'h-10 px-3 text-sm gap-2 [&_svg]:size-4',
];
@endphp

<button
    type="button"
    role="radio"
    :aria-pressed="isOn(@js($value)).toString()"
    :data-state="isOn(@js($value)) ? 'on' : 'off'"
    @click="toggle(@js($value))"
    @if($disabled) disabled @endif
    :class="{
        'bg-transparent hover:bg-muted hover:text-muted-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground': variant === 'default',
        'border border-input bg-transparent hover:bg-accent hover:text-accent-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground data-[state=on]:border-accent': variant === 'outline',
        'h-8 px-2.5 text-[13px] gap-1.5': size === 'sm',
        'h-10 px-3 text-sm gap-2':        size === 'md',
        'h-12 px-5 text-base gap-2.5':    size === 'lg',
        'h-10 w-10':                       size === 'icon',
    }"
    {{ $attributes->twMerge('inline-flex items-center justify-center rounded-md font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0') }}
>
    {{ $slot }}
</button>
