@props([
    'inset'    => false,
    'disabled' => false,
])

<div
    role="menuitem"
    tabindex="-1"
    aria-haspopup="menu"
    :aria-expanded="subOpen.toString()"
    :data-state="subOpen ? 'open' : 'closed'"
    @if($disabled)
        aria-disabled="true"
        data-disabled
    @endif
    @focus="$el.setAttribute('data-highlighted', '')"
    @blur="$el.removeAttribute('data-highlighted')"
    {{ $attributes->twMerge(
        'flex cursor-pointer items-center gap-1.5 rounded-md px-2 py-1.5 text-sm outline-none select-none transition-colors',
        'hover:bg-accent hover:text-accent-foreground focus:bg-accent focus:text-accent-foreground',
        'data-[state=open]:bg-accent data-[state=open]:text-accent-foreground',
        'data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground',
        '[&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4',
        $disabled ? 'pointer-events-none opacity-50' : '',
        $inset ? 'pl-8' : ''
    ) }}
>
    {{ $slot }}
    <x-lucide-chevron-right class="ml-auto size-4" />
</div>
