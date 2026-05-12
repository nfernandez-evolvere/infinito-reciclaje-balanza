<div
    x-data="{
        mid:    null,
        uid:    null,
        open:   false,
        top:    0,
        left:   0,
        _oc:    null,
        _sc:    null,
        _mbEl:  null,
        _state: null,

        init() {
            this.mid   = 'mbm-' + Math.random().toString(36).slice(2, 9);
            this.uid   = this.mid + '-p';
            this._mbEl = this.$el.closest('[data-menubar]');

            // Read the shared reactive state created by the menubar's init().
            // Parent init() always runs before child init() in Alpine.js.
            this._state = this._mbEl?._mbState ?? Alpine.reactive({ active: null });

            // Close this menu when another one opens (state.active changes).
            this.$watch(
                () => this._state.active,
                val => { if (val !== this.mid) this._doClose(); }
            );
        },

        _anyOpen() { return this._state?.active !== null; },

        _open() {
            this._state.active = this.mid;
            this.open = true;
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!this._mbEl?.contains(e.target) && !p?.contains(e.target)) {
                        this._close();
                    }
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        _doClose() {
            if (!this.open) return;
            this.open = false;
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
        },

        _close() {
            this._state.active = null;
            this._doClose();
        },

        toggle() { this.open ? this._close() : this._open(); },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const pw = p.offsetWidth, ph = p.offsetHeight;
            const g  = 4, m = 8;
            const side = r.bottom + ph + g > innerHeight - m ? 'top' : 'bottom';
            this.top  = side === 'bottom' ? r.bottom + g : r.top - ph - g;
            this.left = r.left;
            this.left = Math.max(m, Math.min(this.left, innerWidth - pw - m));
        },
    }"
    {{ $attributes }}
>
    {{ $slot }}
</div>
