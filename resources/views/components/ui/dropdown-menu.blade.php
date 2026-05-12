@props([
    'open'  => false,
    'modal' => false,   // true bloquea scroll del body
    'side'  => 'bottom',  // top | bottom (flip automático, left/right futuro)
    'align' => 'start',   // start | center | end
])

<div
    x-data="{
        open: @js((bool) $open),
        modal: @js((bool) $modal),
        preferred: '{{ $side }}',
        align: '{{ $align }}',
        uid: null, top: 0, left: 0, _oc: null, _sc: null,

        init() {
            this.uid = 'dm-' + Math.random().toString(36).slice(2, 9);
        },

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
            // Vertical flip
            const side = (this.preferred === 'bottom' && r.bottom + ph + g > innerHeight - m) ? 'top'
                       : (this.preferred === 'top'    && r.top    - ph - g < m)               ? 'bottom'
                       : this.preferred;
            this.top = (side === 'bottom') ? r.bottom + g : r.top - ph - g;
            // Horizontal align
            if      (this.align === 'end')    this.left = r.right  - pw;
            else if (this.align === 'center') this.left = r.left   + r.width / 2 - pw / 2;
            else                              this.left = r.left;
            // Clamp to viewport
            this.left = Math.max(m, Math.min(this.left, innerWidth - pw - m));
        },

        _origin() {
            return { bottom: 'origin-top', top: 'origin-bottom' }[this.preferred] ?? 'origin-top';
        }
    }"
    @keydown.escape.window="open && _close()"
    {{ $attributes->merge(['class' => 'inline-block']) }}
>
    {{ $slot }}
</div>
