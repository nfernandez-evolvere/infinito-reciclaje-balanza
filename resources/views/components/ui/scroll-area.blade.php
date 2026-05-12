@props(['orientation' => 'vertical'])  {{-- vertical | horizontal | both --}}

<div {{ $attributes->twMerge(
    'relative overflow-hidden',
    match($orientation) {
        'horizontal' => 'overflow-x-auto overflow-y-hidden',
        'both'       => 'overflow-auto',
        default      => 'overflow-y-auto overflow-x-hidden',
    },
    '[&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar]:h-1.5',
    '[&::-webkit-scrollbar-track]:bg-transparent',
    '[&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-border',
    '[&::-webkit-scrollbar-thumb:hover]:bg-muted-foreground/40'
) }}>
    {{ $slot }}
</div>
