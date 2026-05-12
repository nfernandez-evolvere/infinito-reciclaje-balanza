@props([
    'values'      => [50],          // int or int[] — single value, range, or multi-thumb
    'min'         => 0,
    'max'         => 100,
    'step'        => 1,
    'size'        => 'md',          // sm | md | lg
    'state'       => null,          // null | destructive | success | warning | info
    'orientation' => 'horizontal',  // horizontal | vertical
    'disabled'    => false,
    'name'        => null,
    'rtl'         => false,
])

@php
$vals = is_array($values) ? array_map('floatval', $values) : [(float) $values];
$isVertical = $orientation === 'vertical';

$trackSizeClass = match(true) {
    $size === 'sm' => ($isVertical ? 'w-1.5' : 'h-1.5'),
    $size === 'lg' => ($isVertical ? 'w-3'   : 'h-3'),
    default        => ($isVertical ? 'w-2'   : 'h-2'),
};

$thumbSizeClass = match(true) {
    $size === 'sm' => 'size-4',
    $size === 'lg' => 'size-6',
    default        => 'size-5',
};

$fillClass = match(true) {
    $state === 'destructive' => 'bg-destructive',
    $state === 'success'     => 'bg-success',
    $state === 'warning'     => 'bg-warning',
    $state === 'info'        => 'bg-info',
    default                  => 'bg-primary',
};

$thumbStateClass = match(true) {
    $state === 'destructive' => 'border-destructive focus-visible:ring-destructive',
    $state === 'success'     => 'border-success focus-visible:ring-success',
    $state === 'warning'     => 'border-warning focus-visible:ring-warning',
    $state === 'info'        => 'border-info focus-visible:ring-info',
    default                  => 'border-primary focus-visible:ring-ring',
};

$wrapperClass = ($isVertical
    ? 'relative flex justify-center touch-none select-none px-3 h-48'
    : 'relative flex items-center w-full touch-none select-none py-3');
@endphp

<div
    {{ $attributes->twMerge($wrapperClass, $disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer') }}
    x-data="{
        values: @js($vals),
        min: {{ (float) $min }},
        max: {{ (float) $max }},
        step: {{ (float) $step }},
        vert: @js($isVertical),
        rtl: @js((bool) $rtl),
        disabled: @js((bool) $disabled),
        dragging: null,

        pct(val) {
            let p = (val - this.min) / (this.max - this.min) * 100;
            return (this.rtl && !this.vert) ? 100 - p : p;
        },

        fillStart() {
            const p = this.values.map(v => this.pct(v));
            if (this.values.length > 1) return Math.min(...p);
            return (this.rtl && !this.vert) ? this.pct(this.values[0]) : 0;
        },

        fillSize() {
            const p = this.values.map(v => this.pct(v));
            if (this.values.length > 1) return Math.abs(Math.max(...p) - Math.min(...p));
            return (this.rtl && !this.vert)
                ? 100 - this.pct(this.values[0])
                : this.pct(this.values[0]);
        },

        clamp(v) { return Math.min(this.max, Math.max(this.min, v)); },

        snap(v) { return Math.round((v - this.min) / this.step) * this.step + this.min; },

        raw(pct) { return this.clamp(this.snap(this.min + pct * (this.max - this.min))); },

        getPct(e) {
            const r = this.$el.getBoundingClientRect();
            const cx = e.touches ? e.touches[0].clientX : e.clientX;
            const cy = e.touches ? e.touches[0].clientY : e.clientY;
            let p;
            if (this.vert) {
                p = 1 - (cy - r.top) / r.height;
            } else {
                p = (cx - r.left) / r.width;
                if (this.rtl) p = 1 - p;
            }
            return Math.max(0, Math.min(1, p));
        },

        nearest(e) {
            const v = this.raw(this.getPct(e));
            let idx = 0, best = Infinity;
            this.values.forEach((val, i) => {
                const d = Math.abs(val - v);
                if (d < best || (d === best && v > val)) { best = d; idx = i; }
            });
            return idx;
        },

        start(e) {
            if (this.disabled) return;
            e.preventDefault();
            const idx = this.nearest(e);
            this.dragging = idx;
            this.values[idx] = this.raw(this.getPct(e));
        },

        move(e) {
            if (this.dragging === null) return;
            this.values[this.dragging] = this.raw(this.getPct(e));
        },

        end() { this.dragging = null; },

        init() {
            this.$watch('values', vals => {
                this.$dispatch('slider-change', { values: [...vals] });
            });
        },

        key(e, idx) {
            if (this.disabled) return;
            const d = {
                ArrowRight: (this.rtl && !this.vert) ? -this.step :  this.step,
                ArrowLeft:  (this.rtl && !this.vert) ?  this.step : -this.step,
                ArrowUp:    this.step,
                ArrowDown: -this.step,
            };
            if (e.key in d) {
                e.preventDefault();
                this.values[idx] = this.clamp(this.snap(this.values[idx] + d[e.key]));
            }
            if (e.key === 'Home') { e.preventDefault(); this.values[idx] = this.min; }
            if (e.key === 'End')  { e.preventDefault(); this.values[idx] = this.max; }
        }
    }"
    @mousedown="start($event)"
    @touchstart.prevent="start($event)"
    @mousemove.window="move($event)"
    @touchmove.window.prevent="move($event)"
    @mouseup.window="end()"
    @touchend.window="end()"
>
    {{-- Track --}}
    <div class="relative rounded-full bg-secondary {{ $isVertical ? 'h-full ' : 'w-full ' }}{{ $trackSizeClass }}">
        {{-- Fill --}}
        <div
            class="absolute rounded-full {{ $fillClass }}"
            :class="vert ? 'w-full' : 'h-full'"
            :style="vert
                ? 'bottom:' + fillStart() + '%;height:' + fillSize() + '%'
                : 'left:'   + fillStart() + '%;width:'  + fillSize() + '%'"
        ></div>
    </div>

    {{-- Thumbs --}}
    <template x-for="(val, idx) in values" :key="idx">
        <div
            role="slider"
            tabindex="{{ $disabled ? '-1' : '0' }}"
            :aria-valuenow="val"
            aria-valuemin="{{ $min }}"
            aria-valuemax="{{ $max }}"
            aria-orientation="{{ $orientation }}"
            @if($disabled) aria-disabled="true" @endif
            @keydown="key($event, idx)"
            @mousedown.stop="!disabled && (dragging = idx)"
            @touchstart.prevent.stop="!disabled && (dragging = idx)"
            class="absolute z-10 rounded-full border-2 bg-background shadow-sm
                   focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
                   transition-[transform,box-shadow] duration-100
                   {{ $thumbSizeClass }} {{ $thumbStateClass }}
                   {{ $disabled ? 'cursor-not-allowed' : 'cursor-grab active:cursor-grabbing' }}"
            :class="dragging === idx ? 'scale-110 shadow-md' : 'hover:scale-110'"
            :style="vert
                ? 'bottom:' + pct(val) + '%;transform:translateY(50%)'
                : 'left:'   + pct(val) + '%;transform:translateX(-50%)'"
        ></div>
    </template>

    {{-- Hidden inputs for form submission --}}
    @if($name)
        <template x-for="(val, idx) in values" :key="'inp' + idx">
            <input
                type="hidden"
                :name="values.length > 1 ? '{{ $name }}[]' : '{{ $name }}'"
                :value="val"
            />
        </template>
    @endif
</div>
