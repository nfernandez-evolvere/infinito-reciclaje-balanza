@props([
    'side'  => 'bottom',  // top | bottom | left | right
    'align' => 'center',  // start | center | end
])

<div
    {{ $attributes->twMerge('relative inline-block') }}
    x-data="{
        open:      false,
        preferred: '{{ $side }}',
        actual:    '{{ $side }}',
        align:     '{{ $align }}',
        top:  0,
        left: 0,
        uid:  null,
        _t:   null,

        init() {
            this.uid = 'hc-' + Math.random().toString(36).slice(2, 9);
        },

        _open() {
            clearTimeout(this._t);
            this._t = setTimeout(() => {
                this.open = true;
                this.$nextTick(() => this._place());
            }, 150);
        },

        _close() {
            clearTimeout(this._t);
            this._t = setTimeout(() => { this.open = false; }, 150);
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
        }
    }"
    @mouseenter="_open()"
    @mouseleave="_close()"
>
    {{ $slot }}
</div>
