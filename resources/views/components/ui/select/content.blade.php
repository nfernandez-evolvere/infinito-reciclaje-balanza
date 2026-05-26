@props(['emptyMessage' => 'Sin opciones disponibles'])

<div :id="uid" x-show="open" x-cloak :style="{ top: top + 'px', left: left + 'px', width: w + 'px' }"
    x-transition:enter="transition ease-out duration-100 origin-top" x-transition:enter-start="opacity-0 scale-y-95"
    x-transition:enter-end="opacity-100 scale-y-100" x-transition:leave="transition ease-in duration-75 origin-top"
    x-transition:leave-start="opacity-100 scale-y-100" x-transition:leave-end="opacity-0 scale-y-95" role="listbox"
    {{ $attributes->twMerge('fixed z-50 max-h-60 overflow-y-auto rounded-xl border border-border bg-popover text-popover-foreground shadow-lg') }}>

    <div class="flex flex-col gap-1 p-1">
        {{ $slot }}
    </div>

    {{-- Empty state — visible cuando items.length === 0 --}}
    <div x-show="items.length === 0"
         class="flex flex-col items-center justify-center gap-1.5 px-4 py-6 text-sm text-muted-foreground select-none">
        @isset($empty)
            {{ $empty }}
        @else
            <x-lucide-inbox class="size-5 opacity-40" />
            <span>{{ $emptyMessage }}</span>
        @endisset
    </div>
</div>