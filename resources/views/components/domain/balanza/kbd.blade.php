@props(['inverted' => false])

<kbd {{ $attributes->twMerge(
    $inverted
        ? 'inline-block px-1.5 py-px bg-white/[0.18] border border-white/30 rounded font-mono text-[10px] text-white font-semibold leading-[1.4]'
        : 'inline-block px-1.5 py-px bg-muted border border-border border-b-2 rounded font-mono text-[10px] text-foreground font-semibold leading-[1.4]'
) }}>{{ $slot }}</kbd>
