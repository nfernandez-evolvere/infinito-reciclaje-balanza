<div :id="uid" x-show="open" x-cloak :style="{ top: top + 'px', left: left + 'px', width: w + 'px' }"
    x-transition:enter="transition ease-out duration-100 origin-top" x-transition:enter-start="opacity-0 scale-y-95"
    x-transition:enter-end="opacity-100 scale-y-100" x-transition:leave="transition ease-in duration-75 origin-top"
    x-transition:leave-start="opacity-100 scale-y-100" x-transition:leave-end="opacity-0 scale-y-95" role="listbox"
    {{ $attributes->twMerge('fixed z-50 max-h-60 overflow-y-auto rounded-md border border-border bg-popover text-popover-foreground shadow-md') }}>
    <div class="flex flex-col gap-1 p-1">
        {{ $slot }}
    </div>
</div>