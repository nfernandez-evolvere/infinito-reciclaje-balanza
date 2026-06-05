@props([
    'open'  => false,
    'modal' => false,   // true bloquea scroll del body
    'side'  => 'bottom',  // top | bottom | right | left  (flip automático vertical/horizontal)
    'align' => 'start',   // start | center | end
])

<div
    x-data="{
        open: @js((bool) $open),
        modal: @js((bool) $modal),
        preferred: '{{ $side }}',
        align: '{{ $align }}',
        uid: null, top: 0, left: 0, _oc: null, _sc: null, _ht: null,

        init() {
            this.uid = 'dm-' + Math.random().toString(36).slice(2, 9);
        },

        openHover()  { clearTimeout(this._ht); this._open(); },
        closeHover() { this._ht = setTimeout(() => this._close(), 80); },

        _open() {
            this.open = true;
            if (this.modal) document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!this.$refs.anchor?.contains(e.target) && !p?.contains(e.target)) this._close();
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        _close() {
            this.open = false;
            if (this.modal) document.body.style.overflow = '';
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
        },

        toggle() { this.open ? this._close() : this._open(); },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.anchor;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const pw = p.offsetWidth, ph = p.offsetHeight;
            const g  = 4, m = 8;

            if (this.preferred === 'right' || this.preferred === 'left') {
                // Horizontal flip
                const side = (this.preferred === 'right' && r.right  + pw + g > innerWidth  - m) ? 'left'
                           : (this.preferred === 'left'  && r.left   - pw - g < m)               ? 'right'
                           : this.preferred;
                this.left = (side === 'right') ? r.right + g : r.left - pw - g;
                // Vertical align
                if      (this.align === 'end')    this.top = r.bottom - ph;
                else if (this.align === 'center') this.top = r.top + r.height / 2 - ph / 2;
                else                              this.top = r.top;
                this.top = Math.max(m, Math.min(this.top, innerHeight - ph - m));
            } else {
                // Vertical flip
                const side = (this.preferred === 'bottom' && r.bottom + ph + g > innerHeight - m) ? 'top'
                           : (this.preferred === 'top'    && r.top    - ph - g < m)               ? 'bottom'
                           : this.preferred;
                this.top = (side === 'bottom') ? r.bottom + g : r.top - ph - g;
                // Horizontal align
                if      (this.align === 'end')    this.left = r.right  - pw;
                else if (this.align === 'center') this.left = r.left   + r.width / 2 - pw / 2;
                else                              this.left = r.left;
                this.left = Math.max(m, Math.min(this.left, innerWidth - pw - m));
            }
        },

        _origin() {
            return { bottom: 'origin-top', top: 'origin-bottom', right: 'origin-left', left: 'origin-right' }[this.preferred] ?? 'origin-top';
        }
    }"
    @keydown.escape.window="open && _close()"
    {{ $attributes->merge(['class' => 'inline-block']) }}
>
    {{ $slot }}
</div>
