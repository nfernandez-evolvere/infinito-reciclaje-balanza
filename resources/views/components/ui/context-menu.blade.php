<div
    x-data="{
        open: false,
        uid:  null,
        top:  0,
        left: 0,
        _oc:  null,

        init() { this.uid = 'ctx-' + Math.random().toString(36).slice(2, 9); },

        _open(x, y) {
            this.open = true;
            this.$nextTick(() => {
                // Ajusta para que no se salga del viewport
                const p = document.getElementById(this.uid);
                if (p) {
                    const pw = p.offsetWidth, ph = p.offsetHeight, m = 8;
                    this.left = x + pw > innerWidth  - m ? x - pw : x;
                    this.top  = y + ph > innerHeight - m ? y - ph : y;
                }
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!p?.contains(e.target)) this._close();
                };
                document.addEventListener('click', this._oc);
                document.addEventListener('contextmenu', this._oc);
            });
        },

        _close() {
            this.open = false;
            document.removeEventListener('click', this._oc);
            document.removeEventListener('contextmenu', this._oc);
        }
    }"
    @contextmenu.prevent="_open($event.clientX, $event.clientY)"
    @keydown.escape.window="open && _close()"
    {{ $attributes }}
>
    {{ $slot }}
</div>
