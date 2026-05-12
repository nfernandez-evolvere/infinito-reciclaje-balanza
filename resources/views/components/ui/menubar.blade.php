<div
    role="menubar"
    data-menubar
    x-data="{
        init() {
            // Shared reactive state for all menus within this menubar.
            // Stored on the DOM element so child menus can read it in their
            // own init() without relying on $parent (which is unreliable in
            // expression contexts in Alpine.js v3).
            this.$el._mbState = Alpine.reactive({ active: null });
        }
    }"
    @keydown.escape.window="$el._mbState && ($el._mbState.active = null)"
    {{ $attributes->twMerge('flex h-9 items-center gap-0.5 rounded-md border border-input bg-background px-1 shadow-xs') }}
>
    {{ $slot }}
</div>
