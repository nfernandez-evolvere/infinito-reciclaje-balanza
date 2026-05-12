@props(['loop' => false])

<template x-teleport="body">
    <div
        :id="uid"
        x-show="open"
        x-cloak
        x-data="{
            loop: @js((bool) $loop),
            _items() {
                return [...this.$el.querySelectorAll(
                    '[role=menuitem]:not([aria-disabled=true]),' +
                    '[role=menuitemcheckbox]:not([aria-disabled=true]),' +
                    '[role=menuitemradio]:not([aria-disabled=true])'
                )];
            },
            focusFirst() { this._items()[0]?.focus(); },
            focusLast()  { const i = this._items(); i[i.length - 1]?.focus(); },
            focusNext()  {
                const items = this._items(), cur = items.indexOf(document.activeElement);
                const next = cur + 1;
                if (next < items.length) items[next].focus();
                else if (this.loop) items[0].focus();
            },
            focusPrev()  {
                const items = this._items(), cur = items.indexOf(document.activeElement);
                const prev = cur - 1;
                if (prev >= 0) items[prev].focus();
                else if (this.loop) items[items.length - 1].focus();
            },
        }"
        x-effect="open && $nextTick(() => focusFirst())"
        :style="{ top: top + 'px', left: left + 'px' }"
        :class="_origin()"
        @keydown.arrow-down.prevent="focusNext()"
        @keydown.arrow-up.prevent="focusPrev()"
        @keydown.home.prevent="focusFirst()"
        @keydown.end.prevent="focusLast()"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="menu"
        aria-orientation="vertical"
        :data-state="open ? 'open' : 'closed'"
        {{ $attributes->twMerge(
            'fixed z-50 min-w-32 w-max max-h-[80vh] overflow-y-auto',
            'rounded-lg bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10'
        ) }}
    >
        {{ $slot }}
    </div>
</template>
