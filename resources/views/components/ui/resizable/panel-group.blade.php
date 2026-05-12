@props([
    'direction' => 'horizontal',   // horizontal | vertical
])

<div
    x-data="{
        direction: @js($direction),
        dragging: false,
        handleIdx: null,
        startPos: 0,
        startSizes: [],

        get isHorizontal() { return this.direction === 'horizontal'; },

        panels() {
            return [...this.$el.querySelectorAll(':scope > [data-panel]')];
        },

        getSizes() {
            return this.panels().map(p => parseFloat(p.style.flexGrow) || 1);
        },

        dragStart(e, idx) {
            e.preventDefault();
            e.currentTarget.setPointerCapture(e.pointerId);
            this.dragging   = true;
            this.handleIdx  = idx;
            this.startPos   = this.isHorizontal ? e.clientX : e.clientY;
            this.startSizes = this.getSizes();
        },

        dragMove(e, idx) {
            if (!this.dragging || this.handleIdx !== idx) return;
            const panels = this.panels();
            const before = panels[idx];
            const after  = panels[idx + 1];
            if (!before || !after) return;

            const totalSize  = this.isHorizontal ? this.$el.offsetWidth : this.$el.offsetHeight;
            if (!totalSize) return;

            const delta      = (this.isHorizontal ? e.clientX : e.clientY) - this.startPos;
            const totalUnits = this.startSizes.reduce((a, b) => a + b, 0);
            const minRatio   = 0.05 * totalUnits;

            const newBefore  = Math.max(minRatio, this.startSizes[idx] + (delta / totalSize) * totalUnits);
            const newAfter   = Math.max(minRatio, this.startSizes[idx] + this.startSizes[idx + 1] - newBefore);
            const realBefore = this.startSizes[idx] + this.startSizes[idx + 1] - newAfter;

            before.style.flexGrow = realBefore;
            after.style.flexGrow  = newAfter;
        },

        dragEnd() {
            this.dragging  = false;
            this.handleIdx = null;
        },
    }"
    :aria-orientation="direction"
    :class="[direction === 'horizontal' ? 'flex flex-row' : 'flex flex-col', dragging ? 'select-none' : '']"
    {{ $attributes->twMerge('h-full w-full') }}
>
    {{ $slot }}
</div>
