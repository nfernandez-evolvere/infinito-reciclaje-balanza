@props([
    'pressed'  => false,
    'variant'  => 'default',  // default | outline
    'size'     => 'md',       // sm | md | lg
    'name'     => null,
    'value'    => 'on',
    'disabled' => false,
])

@php
$sizeClass = match($size) {
    'sm'    => 'h-8 px-2.5 text-[13px] gap-1.5 [&_svg]:size-3.5',
    'lg'    => 'h-12 px-5 text-base gap-2.5 [&_svg]:size-[18px]',
    'icon'  => 'h-10 w-10 [&_svg]:size-4',
    default => 'h-10 px-3 text-sm gap-2 [&_svg]:size-4',
};

$variantClass = match($variant) {
    'outline' => 'border border-input bg-transparent hover:bg-accent hover:text-accent-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground data-[state=on]:border-accent',
    default   => 'bg-transparent hover:bg-muted hover:text-muted-foreground data-[state=on]:bg-accent data-[state=on]:text-accent-foreground',
};
@endphp

<button
    type="button"
    role="switch"
    x-data="{ pressed: @js((bool) $pressed) }"
    :aria-pressed="pressed.toString()"
    :data-state="pressed ? 'on' : 'off'"
    @click="pressed = !pressed"
    @if($disabled) disabled @endif
    {{ $attributes->twMerge(
        'inline-flex items-center justify-center rounded-md font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
        'disabled:pointer-events-none disabled:opacity-50',
        '[&_svg]:pointer-events-none [&_svg]:shrink-0',
        $sizeClass,
        $variantClass
    ) }}
>
    {{ $slot }}
    @if($name)
        <input type="hidden" :name="@js($name)" :value="pressed ? @js($value) : ''" />
    @endif
</button>
