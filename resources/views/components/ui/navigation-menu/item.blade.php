<li
    x-data="{
        nid: null,
        uid: null,
        top: 0,
        left: 0,
        _oc: null,
        _sc: null,

        get open() { return this.$parent.active === this.nid; },

        init() {
            this.nid = 'nm-' + Math.random().toString(36).slice(2, 9);
            this.uid = this.nid + '-p';

            this.$watch('open', val => {
                if (!val) {
                    document.removeEventListener('click', this._oc);
                    window.removeEventListener('scroll', this._sc, true);
                    window.removeEventListener('resize', this._sc);
                }
            });
        },

        _open() {
            this.$parent.active = this.nid;
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    const nav = this.$el.closest('[data-navmenu]');
                    if (!nav?.contains(e.target) && !p?.contains(e.target)) {
                        this.$parent.active = null;
                    }
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        _close() { this.$parent.active = null; },
        toggle() { this.open ? this._close() : this._open(); },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const pw = p.offsetWidth, ph = p.offsetHeight;
            const g  = 8, m = 8;
            this.top  = r.bottom + g > innerHeight - m ? r.top - ph - g : r.bottom + g;
            this.left = r.left + r.width / 2 - pw / 2;
            this.left = Math.max(m, Math.min(this.left, innerWidth - pw - m));
        },
    }"
    {{ $attributes->twMerge('relative') }}
>
    {{ $slot }}
</li>
