@props([
    'name'     => null,
    'value'    => null,
    'disabled' => false,
])

<div
    {{ $attributes->twMerge('inline-block w-full') }}
    x-data="{
        open: false,
        value: @js($value),
        items: [],
        focusIdx: -1,
        uid: null, top: 0, left: 0, w: 0, _oc: null, _sc: null,

        init() {
            this.uid = 'sel-' + Math.random().toString(36).slice(2, 9);
        },

        get label() {
            const o = this.items.find(o => String(o.value) === String(this.value));
            return o ? o.label : null;
        },

        toggle() {
            if (this.open) { this.close(); return; }
            this.open = true;
            this.focusIdx = this.items.findIndex(o => String(o.value) === String(this.value));
            if (this.focusIdx < 0) this.focusIdx = this.items.findIndex(o => !o.disabled);
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!this.$refs.trigger?.contains(e.target) && !p?.contains(e.target)) this.close();
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        close() {
            this.open = false;
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
            this.$nextTick(() => { if (this.$refs.trigger) this.$refs.trigger.focus(); });
        },

        select(val) {
            this.value = val;
            this.$dispatch('select-change', { value: val });
            this.close();
        },

        move(dir) {
            if (!this.open) { this.toggle(); return; }
            let next = this.focusIdx + dir;
            while (next >= 0 && next < this.items.length && this.items[next].disabled) next += dir;
            if (next >= 0 && next < this.items.length) this.focusIdx = next;
        },

        confirm() {
            if (!this.open) { this.toggle(); return; }
            if (this.focusIdx >= 0 && this.items[this.focusIdx] && !this.items[this.focusIdx].disabled)
                this.select(this.items[this.focusIdx].value);
        },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const ph = p.offsetHeight;
            const g  = 4, m = 8;
            this.w = r.width;
            this.left = r.left;
            this.top  = (r.bottom + ph + g > innerHeight - m) ? r.top - ph - g : r.bottom + g;
        }
    }"
    @select-item-init.stop="items.push($event.detail)"
    @keydown.arrow-down.prevent="move(1)"
    @keydown.arrow-up.prevent="move(-1)"
    @keydown.enter.prevent="confirm()"
    @keydown.space.prevent="open ? confirm() : toggle()"
    @keydown.escape.prevent="open && close()"
    @keydown.tab="open && close()"
>
    {{ $slot }}

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="value" />
    @endif
</div>
