@props(['class' => ''])

<div
    x-data="{
        search: '',
        activeItem: null,

        _items() {
            return [...this.$el.querySelectorAll('[role=option]:not([aria-disabled=true])')];
        },

        _updateActive(el) {
            this.activeItem = el?.dataset?.value ?? null;
            this.$el.querySelectorAll('[role=option]').forEach(i => i.removeAttribute('data-selected'));
            el?.setAttribute('data-selected', '');
        },

        focusNext() {
            const items = this._items();
            if (!items.length) return;
            const cur = items.findIndex(i => i.hasAttribute('data-selected'));
            const next = cur + 1 < items.length ? items[cur + 1] : items[0];
            this._updateActive(next);
            next.scrollIntoView({ block: 'nearest' });
        },

        focusPrev() {
            const items = this._items();
            if (!items.length) return;
            const cur = items.findIndex(i => i.hasAttribute('data-selected'));
            const prev = cur - 1 >= 0 ? items[cur - 1] : items[items.length - 1];
            this._updateActive(prev);
            prev.scrollIntoView({ block: 'nearest' });
        },

        selectActive() {
            const items = this._items();
            const active = items.find(i => i.hasAttribute('data-selected'));
            active?.click();
        },

        _resetActive() {
            const first = this._items()[0];
            this._updateActive(first ?? null);
        },
    }"
    @keydown.arrow-down.prevent="focusNext()"
    @keydown.arrow-up.prevent="focusPrev()"
    @keydown.enter.prevent="selectActive()"
    @keydown.escape="search = ''"
    x-effect="$nextTick(() => _resetActive())"
    {{ $attributes->twMerge('flex flex-col overflow-hidden rounded-xl border border-border bg-popover text-popover-foreground shadow-md', $class) }}
>
    {{ $slot }}
</div>
