@props([
    'variant' => 'default',   // default | outline
    'size'    => 'default',   // default | sm | lg
    'active'  => false,
    'href'    => null,
    'tooltip' => null,        // label shown as tooltip when sidebar is icon-collapsed
])

@php
$sizeClass = match($size) {
    'sm'    => 'h-7 text-xs',
    'lg'    => 'h-12 text-sm',
    default => 'h-10 text-sm',
};

$variantClass = match($variant) {
    'outline' => 'bg-background shadow-[0_0_0_1px_hsl(var(--sidebar-border))] hover:bg-muted/60 hover:text-foreground hover:shadow-[0_0_0_1px_hsl(var(--sidebar-accent))]',
    default   => 'hover:bg-muted/60 hover:text-foreground',
};

$activeClass = $active ? 'font-medium text-primary' : '';

$base = 'group peer/menu-button flex w-full items-center gap-2 overflow-hidden rounded-full p-1 outline-none transition-[width,height,padding,color,background-color] ring-sidebar-ring focus-visible:ring-2 [&>span:last-child]:truncate [&_svg]:size-6 [&_svg]:shrink-0 disabled:pointer-events-none disabled:opacity-50 hover:bg-primary/10 data-[active=true]:bg-primary/10 hover:text-primary';

@endphp

@if($href)
    <a
        href="{{ $href }}"
        data-active="{{ $active ? 'true' : 'false' }}"
        data-sidebar-menu-button
        @if($tooltip)
        x-data="{ _t: false, _y: 0, _x: 0 }"
        @mouseenter="if (isCollapsed) { _t = true; const r = $el.getBoundingClientRect(); _y = r.top + r.height / 2; _x = r.right + 8; }"
        @mouseleave="_t = false"
        @endif
        {{ $attributes->twMerge($base, $sizeClass, $variantClass, $activeClass) }}
    >
        {{ $slot }}
        @if($tooltip)
        <template x-teleport="body">
            <div
                x-show="_t"
                x-cloak
                :style="`top:${_y}px;left:${_x}px;transform:translateY(-50%);z-index:var(--z-tooltip)`"
                class="pointer-events-none fixed flex items-center rounded-md bg-foreground px-2.5 py-1.5 text-xs font-medium text-background shadow-md whitespace-nowrap"
            >
                <span class="absolute left-0 -translate-x-1/2 top-1/2 -translate-y-1/2 size-2.5 rotate-45 bg-foreground"></span>
                {{ $tooltip }}
            </div>
        </template>
        @endif
    </a>
@else
    <button
        type="button"
        data-active="{{ $active ? 'true' : 'false' }}"
        data-sidebar-menu-button
        @if($tooltip)
        x-data="{ _t: false, _y: 0, _x: 0 }"
        @mouseenter="if (isCollapsed) { _t = true; const r = $el.getBoundingClientRect(); _y = r.top + r.height / 2; _x = r.right + 8; }"
        @mouseleave="_t = false"
        @endif
        {{ $attributes->twMerge($base, $sizeClass, $variantClass, $activeClass) }}
    >
        {{ $slot }}
        @if($tooltip)
        <template x-teleport="body">
            <div
                x-show="_t"
                x-cloak
                :style="`top:${_y}px;left:${_x}px;transform:translateY(-50%);z-index:var(--z-tooltip)`"
                class="pointer-events-none fixed flex items-center rounded-md bg-foreground px-2.5 py-1.5 text-xs font-medium text-background shadow-md whitespace-nowrap"
            >
                <span class="absolute left-0 -translate-x-1/2 top-1/2 -translate-y-1/2 size-2.5 rotate-45 bg-foreground"></span>
                {{ $tooltip }}
            </div>
        </template>
        @endif
    </button>
@endif
