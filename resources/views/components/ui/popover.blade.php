@props([
    'side'  => 'bottom',  // top | bottom | left | right
    'align' => 'start',   // start | center | end
    'width' => 'w-72',
])

<div
    {{ $attributes->twMerge('relative inline-flex') }}
    x-data="{
        open:      false,
        preferred: '{{ $side }}',
        actual:    '{{ $side }}',
        align:     '{{ $align }}',
        top:  0,
        left: 0,
        uid:  null,
        _oc:  null,
        _sc:  null,

        init() {
            this.uid = 'pop-' + Math.random().toString(36).slice(2, 9);
        },

        _toggle() {
            this.open ? this._close() : this._open();
        },

        _open() {
            this.open = true;
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!this.$refs.trigger.contains(e.target) && !p?.contains(e.target)) this._close();
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        _close() {
            this.open = false;
            this.actual = this.preferred;
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
        },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const pw = p.offsetWidth, ph = p.offsetHeight;
            const g = 6, m = 8;
            let s = this.preferred;
            if      (s === 'bottom' && r.bottom + ph + m > innerHeight) s = 'top';
            else if (s === 'top'    && r.top    - ph - m < 0)           s = 'bottom';
            else if (s === 'right'  && r.right  + pw + m > innerWidth)  s = 'left';
            else if (s === 'left'   && r.left   - pw - m < 0)           s = 'right';
            this.actual = s;
            const a = this.align;
            if      (s === 'bottom') { this.top = r.bottom + g; this.left = a==='end' ? r.right-pw  : a==='center' ? r.left+r.width/2-pw/2  : r.left; }
            else if (s === 'top')    { this.top = r.top-ph-g;   this.left = a==='end' ? r.right-pw  : a==='center' ? r.left+r.width/2-pw/2  : r.left; }
            else if (s === 'right')  { this.left = r.right+g;   this.top  = a==='end' ? r.bottom-ph : a==='center' ? r.top+r.height/2-ph/2  : r.top;  }
            else                     { this.left = r.left-pw-g;  this.top  = a==='end' ? r.bottom-ph : a==='center' ? r.top+r.height/2-ph/2  : r.top;  }
        },

        _origin() {
            return { bottom: 'origin-top', top: 'origin-bottom', right: 'origin-left', left: 'origin-right' }[this.actual] ?? 'origin-top';
        },

        _arrow() {
            const s = this.actual, a = this.align;
            const edges = { bottom: 'top-0 -translate-y-1/2 border-t border-l', top: 'bottom-0 translate-y-1/2 border-b border-r', right: 'left-0 -translate-x-1/2 border-l border-b', left: 'right-0 translate-x-1/2 border-t border-r' };
            const posH  = { start: 'left-4', center: 'left-1/2 -translate-x-1/2', end: 'right-4' };
            const posV  = { start: 'top-4',  center: 'top-1/2 -translate-y-1/2',  end: 'bottom-4' };
            return edges[s] + ' ' + ((s === 'bottom' || s === 'top') ? posH[a] : posV[a]);
        }
    }"
    @keydown.escape.window="_close()"
>
    {{-- Trigger --}}
    <div x-ref="trigger" @click="_toggle()" class="inline-flex cursor-pointer">
        @isset($trigger){{ $trigger }}@endisset
    </div>

    {{-- Panel: teleportado a <body> para evitar problemas de stacking context --}}
    <template x-teleport="body">
        <div
            :id="uid"
            x-show="open"
            :style="{ top: top + 'px', left: left + 'px' }"
            :class="_origin()"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-cloak
            class="fixed z-(--z-popover) {{ $width }} rounded-lg border border-border bg-popover p-4 text-popover-foreground shadow-md"
        >
            {{ $slot }}
            <span :class="_arrow()" class="absolute size-2.5 rotate-45 bg-popover border-border"></span>
        </div>
    </template>
</div>
