@props([
    'orientation' => 'horizontal',   // horizontal | vertical
    'loop'        => false,
])

<div
    x-data="{
        orientation: @js($orientation),
        loop:        @js((bool) $loop),
        index:       0,
        total:       0,

        init() {
            this.$nextTick(() => {
                this.total = this.$refs.viewport?.children?.length ?? 0;
            });
        },

        onScroll() {
            const viewport = this.$refs.viewport;
            if (!viewport) return;
            if (this.orientation === 'horizontal') {
                const w = viewport.offsetWidth;
                this.index = w > 0 ? Math.round(viewport.scrollLeft / w) : 0;
            } else {
                const h = viewport.offsetHeight;
                this.index = h > 0 ? Math.round(viewport.scrollTop / h) : 0;
            }
        },

        get canPrev() {
            return this.loop ? this.total > 1 : this.index > 0;
        },

        get canNext() {
            return this.loop ? this.total > 1 : this.index < this.total - 1;
        },

        scrollTo(i) {
            const viewport = this.$refs.viewport;
            if (!viewport) return;
            if (this.loop) {
                i = ((i % this.total) + this.total) % this.total;
            } else {
                i = Math.max(0, Math.min(i, this.total - 1));
            }
            if (this.orientation === 'horizontal') {
                viewport.scrollTo({ left: i * viewport.offsetWidth, behavior: 'smooth' });
            } else {
                viewport.scrollTo({ top: i * viewport.offsetHeight, behavior: 'smooth' });
            }
        },

        prev() { this.scrollTo(this.index - 1); },
        next() { this.scrollTo(this.index + 1); },
    }"
    @keydown.arrow-left.prevent="orientation === 'horizontal' && prev()"
    @keydown.arrow-right.prevent="orientation === 'horizontal' && next()"
    @keydown.arrow-up.prevent="orientation === 'vertical' && prev()"
    @keydown.arrow-down.prevent="orientation === 'vertical' && next()"
    :aria-roledescription="'carousel'"
    {{ $attributes->twMerge('relative') }}
>
    {{ $slot }}
</div>
