@props([
    'startName'     => null,               // name del input oculto de inicio (ej: desde)
    'endName'       => null,               // name del input oculto de fin (ej: hasta)
    'start'         => null,               // valor inicial ISO (YYYY-MM-DD)
    'end'           => null,               // valor inicial ISO (YYYY-MM-DD)
    'placeholder'   => 'Seleccionar período',
    'minDate'       => null,
    'maxDate'       => null,
    'disabledDates' => [],
    'disabled'      => false,
    'size'          => 'md',               // sm | md | lg
])

@php
$startVal = $start ?: null;
$endVal   = $end ?: null;
$initialRange = ($startVal || $endVal) ? ['start' => $startVal, 'end' => $endVal] : null;

$triggerSize = match($size) {
    'sm'    => 'h-8 pl-3 pr-2 text-[13px] gap-1.5',
    'lg'    => 'h-12 pl-4 pr-3 text-base gap-2.5',
    default => 'h-10 pl-3 pr-2.5 text-sm gap-2',
};
@endphp

<div
    x-data="{
        open:  false,
        start: @js($startVal),
        end:   @js($endVal),
        top:   0,
        left:  0,
        uid:   null,
        _oc:   null,
        _sc:   null,

        init() {
            this.uid = 'drp-' + Math.random().toString(36).slice(2, 9);
        },

        fmt(iso) {
            if (!iso) return '';
            const [y, m, d] = iso.split('-');
            return `${d}/${m}/${y}`;
        },

        get label() {
            if (!this.start && !this.end) return null;
            if (this.start && this.end) {
                return this.start === this.end
                    ? this.fmt(this.start)
                    : `${this.fmt(this.start)} – ${this.fmt(this.end)}`;
            }
            return this.start ? `Desde ${this.fmt(this.start)}` : `Hasta ${this.fmt(this.end)}`;
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

        onPick(range) {
            this.start = range?.start ?? null;
            this.end   = range?.end ?? null;
            // Cierra solo al completar el rango; con un extremo suelto queda abierto
            // para elegir el otro (o cerrar afuera y filtrar con rango abierto).
            if (this.start && this.end) this._close();
            this.$refs.trigger.dispatchEvent(
                new CustomEvent('range-picked', {
                    detail: { start: this.start, end: this.end },
                    bubbles: true, composed: true,
                })
            );
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
    @set-range.window="start = $event.detail.start ?? null; end = $event.detail.end ?? null"
    {{ $attributes->twMerge('inline-block w-full') }}
>
    {{-- Trigger --}}
    <button
        type="button"
        x-ref="trigger"
        @click="toggle()"
        :aria-expanded="open.toString()"
        @if($disabled) disabled @endif
        class="w-full flex items-center gap-2 rounded-full border border-input bg-background text-left shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {{ $triggerSize }}"
    >
        <x-lucide-calendar-days class="size-4 text-muted-foreground shrink-0" />
        <span
            x-text="label ?? @js($placeholder)"
            :class="label === null ? 'text-muted-foreground' : 'text-foreground'"
            class="flex-1 truncate text-left"
        ></span>
    </button>

    @if($startName)
        <input type="hidden" name="{{ $startName }}" :value="start ?? ''" />
    @endif
    @if($endName)
        <input type="hidden" name="{{ $endName }}" :value="end ?? ''" />
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
            @click.stop
            @change="onPick($event.detail.value)"
            class="fixed z-(--z-popover)"
        >
            <x-ui.calendar
                mode="range"
                :value="$initialRange"
                :min-date="$minDate"
                :max-date="$maxDate"
                :disabled-dates="$disabledDates"
            />
        </div>
    </template>
</div>
