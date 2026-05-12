@props([
    'side'        => 'left',        // left | right
    'variant'     => 'sidebar',     // sidebar | floating | inset
    'collapsible' => 'offcanvas',   // offcanvas | icon | none
])

{{-- Mobile overlay --}}
<div
    x-show="isMobile && openMobile"
    x-cloak
    @click="closeMobile()"
    x-transition:enter="transition-opacity duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/50 lg:hidden"
    style="z-index: var(--z-overlay);"
></div>

{{-- Sidebar wrapper --}}
<aside
    x-init="$el.dataset.initialized = 'true'"
    :data-collapsed="isCollapsed.toString()"
    :class="{
        @if($side === 'left')
        'translate-x-0': isOpen,
        '-translate-x-full': !isOpen,
        @else
        'translate-x-0': isOpen,
        'translate-x-full': !isOpen,
        @endif
    }"
    @click="if (isMobile && $event.target.closest('a[href]')) closeMobile()"
    data-sidebar
    data-side="{{ $side }}"
    data-variant="{{ $variant }}"
    data-collapsible="{{ $collapsible }}"
    {{ $attributes->twMerge(
        'group/sidebar relative flex flex-col shrink-0 overflow-hidden transition-all duration-200 ease-in-out',
        // Mobile: fixed panel
        'fixed inset-y-0 lg:relative lg:inset-auto',
        $side === 'left' ? 'left-0' : 'right-0',
        // Variant styling
        $variant === 'floating'
            ? 'm-2 rounded-xl border border-sidebar-border bg-sidebar shadow-md'
            : 'border-sidebar-border bg-sidebar',
        $variant === 'sidebar' ? 'border-r' : '',
        $variant === 'inset'   ? 'border-r' : '',
    ) }}
    style="--sidebar-width: 16rem; --sidebar-width-icon: 3rem; z-index: var(--z-sidebar);"
>
    <div class="flex h-full w-full flex-col overflow-hidden">
        {{ $slot }}
    </div>
</aside>
