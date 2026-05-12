<div
    x-data="{
        subOpen: false,
        _subItems() {
            return [...(this.$el.querySelector('[role=menu]') ?? this.$el)
                .querySelectorAll(
                    '[role=menuitem]:not([aria-disabled=true]),' +
                    '[role=menuitemcheckbox]:not([aria-disabled=true]),' +
                    '[role=menuitemradio]:not([aria-disabled=true])'
                )];
        },
        subFocusFirst() { this._subItems()[0]?.focus(); },
        subFocusNext()  {
            const items = this._subItems(), cur = items.indexOf(document.activeElement), next = cur + 1;
            if (next < items.length) items[next].focus();
        },
        subFocusPrev()  {
            const items = this._subItems(), cur = items.indexOf(document.activeElement), prev = cur - 1;
            if (prev >= 0) items[prev].focus();
            else { this.subOpen = false; this.$refs.subTrigger?.focus(); }
        },
    }"
    @mouseenter="subOpen = true"
    @mouseleave="subOpen = false"
    class="relative"
    {{ $attributes }}
>
    {{ $slot }}
</div>
