@props([
    'name'          => null,
    'value'         => null,
    'placeholder'   => 'Seleccionar fecha',
    'format'        => 'D [de] MMMM [de] YYYY',   // display format (basic)
    'minDate'       => null,
    'maxDate'       => null,
    'disabledDates' => [],
    'disabled'      => false,
    'size'          => 'md',    // sm | md | lg
])

@php
$triggerSize = match($size) {
    'sm'    => 'h-8 pl-3 pr-2 text-[13px] gap-1.5',
    'lg'    => 'h-12 pl-4 pr-3 text-base gap-2.5',
    default => 'h-10 pl-3 pr-2.5 text-sm gap-2',
};
@endphp

<div
    x-data="{
        open:  false,
        value: @js($value),
        top:   0,
        left:  0,
        uid:   null,
        _oc:   null,
        _sc:   null,

        init() {
            this.uid = 'dp-' + Math.random().toString(36).slice(2, 9);
        },

        get label() {
            if (!this.value) return null;
            try {
                const d = new Date(this.value + 'T00:00:00');
                return d.toLocaleDateString(navigator.language, { day: 'numeric', month: 'long', year: 'numeric' });
            } catch { return this.value; }
        },

        _open() {
            if (@js($disabled)) return;
            this.open = true;
            this.$nextTick(() => {
                this._place();
                this._oc = e => {
                    const p = document.getElementById(this.uid);
                    if (!this.$refs.trigger?.contains(e.target) && !p?.contains(e.target)) this._close();
                };
                this._sc = () => { if (this.open) this._place(); };
                document.addEventListener('click', this._oc);
                window.addEventListener('scroll', this._sc, true);
                window.addEventListener('resize', this._sc);
            });
        },

        _close() {
            this.open = false;
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
        },

        toggle() { this.open ? this._close() : this._open(); },

        onPick(iso) {
            this.value = iso;
            this._close();
            this.$dispatch('change', { value: iso });
        },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const pw = p.offsetWidth, ph = p.offsetHeight;
            const g  = 4, m = 8;
            this.top  = r.bottom + ph + g > innerHeight - m ? r.top - ph - g : r.bottom + g;
            this.left = r.left;
            this.left = Math.max(m, Math.min(this.left, innerWidth - pw - m));
        },
    }"
    @keydown.escape="open && _close()"
    {{ $attributes->twMerge('inline-block w-full') }}
>
    {{-- Trigger --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        :aria-expanded="open.toString()"
        @if($disabled) disabled @endif
        class="w-full flex items-center gap-2 rounded-md border border-input bg-background text-left shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {{ $triggerSize }}"
    >
        <x-lucide-calendar-days class="size-4 text-muted-foreground shrink-0" />
        <span
            x-text="label ?? @js($placeholder)"
            :class="label === null ? 'text-muted-foreground' : 'text-foreground'"
            class="flex-1 truncate text-left"
        ></span>
    </button>

    @if($name)
        <input type="hidden" name="{{ $name }}" :value="value ?? ''" />
    @endif

    {{-- Calendar dropdown --}}
    <template x-teleport="body">
        <div
            :id="uid"
            x-show="open"
            x-cloak
            :style="{ top: top + 'px', left: left + 'px' }"
            x-transition:enter="transition ease-out duration-100 origin-top"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75 origin-top"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @change="onPick($event.detail.value)"
            class="fixed z-50"
        >
            <x-ui.calendar
                :value="$value"
                :min-date="$minDate"
                :max-date="$maxDate"
                :disabled-dates="$disabledDates"
            />
        </div>
    </template>
</div>
