@props([
    'searchable' => true,
])

{{--
    Teleported dropdown for composition mode.
    Slot receives <x-ui.combobox.empty> and <x-ui.combobox.list>.
--}}
<template x-teleport="body">
    <div
        :id="uid"
        x-show="open"
        x-cloak
        :style="`top:${top}px;left:${left}px;width:${w}px`"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-y-95"
        x-transition:enter-end="opacity-100 scale-y-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-y-100"
        x-transition:leave-end="opacity-0 scale-y-95"
        :class="placement === 'top' ? 'origin-bottom' : 'origin-top'"
        {{ $attributes->twMerge('fixed z-50 rounded-md border border-border bg-popover shadow-md overflow-hidden') }}
    >
        @if($searchable)
        <div class="border-b border-border p-1">
            <div class="relative flex items-center">
                <x-lucide-search class="pointer-events-none absolute left-2.5 size-3.5 text-muted-foreground" />
                <input
                    type="text"
                    x-ref="searchInput"
                    x-model="search"
                    autocomplete="off"
                    :placeholder="searchPlaceholder"
                    :class="_searchCls()"
                    class="w-full pl-8 bg-transparent text-foreground placeholder:text-muted-foreground focus-visible:outline-none"
                />
            </div>
        </div>
        @endif

        {{ $slot }}
    </div>
</template>
