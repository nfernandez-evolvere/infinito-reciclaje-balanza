@props([
    'content' => '',
    'side'    => 'top',  // top | bottom | left | right
])

<span
    {{ $attributes->twMerge('inline-flex') }}
    x-data="{
        visible:   false,
        preferred: '{{ $side }}',
        actual:    '{{ $side }}',
        top: 0, left: 0, arrowPos: 0, uid: null,

        init() {
            this.uid = 'tip-' + Math.random().toString(36).slice(2, 9);
        },

        _place() {
            const tip = document.getElementById(this.uid);
            if (!tip) return;
            const r  = this.$el.getBoundingClientRect();
            const tw = tip.offsetWidth, th = tip.offsetHeight;
            const g = 8, m = 8;
            let s = this.preferred;
            if      (s === 'top'    && r.top    - th - g < m)               s = 'bottom';
            else if (s === 'bottom' && r.bottom + th + g > innerHeight - m) s = 'top';
            else if (s === 'left'   && r.left   - tw - g < m)               s = 'right';
            else if (s === 'right'  && r.right  + tw + g > innerWidth  - m) s = 'left';
            this.actual = s;
            const cx = r.left + r.width  / 2;
            const cy = r.top  + r.height / 2;
            let tipLeft, tipTop;
            if      (s === 'top')    { tipTop = r.top    - th - g; tipLeft = cx - tw / 2; }
            else if (s === 'bottom') { tipTop = r.bottom + g;      tipLeft = cx - tw / 2; }
            else if (s === 'left')   { tipTop = cy - th / 2;       tipLeft = r.left - tw - g; }
            else                     { tipTop = cy - th / 2;       tipLeft = r.right + g; }
            const cl = Math.max(m, Math.min(tipLeft, innerWidth  - tw - m));
            const ct = Math.max(m, Math.min(tipTop,  innerHeight - th - m));
            this.left = cl;
            this.top  = ct;
            // after clamping, recalculate arrow to point at trigger center
            this.arrowPos = (s === 'top' || s === 'bottom') ? cx - cl : cy - ct;
        },

        _origin() {
            return { bottom: 'origin-top', top: 'origin-bottom', right: 'origin-left', left: 'origin-right' }[this.actual] ?? 'origin-top';
        },

        _arrow() {
            return {
                top:    'bottom-0 translate-y-1/2',
                bottom: 'top-0 -translate-y-1/2',
                left:   'right-0 translate-x-1/2',
                right:  'left-0 -translate-x-1/2',
            }[this.actual] ?? '';
        },

        _arrowStyle() {
            const s = this.actual, half = 5;
            return (s === 'top' || s === 'bottom')
                ? 'left:' + (this.arrowPos - half) + 'px'
                : 'top:'  + (this.arrowPos - half) + 'px';
        }
    }"
    @mouseenter="visible = true; $nextTick(() => _place())"
    @mouseleave="visible = false; actual = preferred"
    @focusin="visible  = true; $nextTick(() => _place())"
    @focusout="visible = false; actual = preferred"
>
    {{ $slot }}

    <template x-teleport="body">
        <span
            :id="uid"
            x-show="visible"
            x-cloak
            :style="{ top: top + 'px', left: left + 'px' }"
            :class="_origin()"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="tooltip"
            class="pointer-events-none fixed z-(--z-tooltip) w-max max-w-[200px] rounded-md bg-foreground px-2.5 py-1.5 text-xs font-medium text-background shadow-md"
        >
            {{ $content }}
            <span :class="_arrow()" :style="_arrowStyle()" class="absolute size-2.5 rotate-45 bg-foreground"></span>
        </span>
    </template>
</span>
