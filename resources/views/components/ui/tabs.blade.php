@props([
    'value'          => '',
    'orientation'    => 'horizontal',  // horizontal | vertical
    'activationMode' => 'automatic',   // automatic | manual
    'loop'           => true,
])

<div
    {{ $attributes->twMerge('') }}
    x-data="{
        active: @js($value),
        orientation: @js($orientation),
        activationMode: @js($activationMode),
        loop: @js((bool) $loop),
        triggers: [],
        registerTrigger(val, disabled) {
            if (!this.triggers.find(t => t.value === val)) {
                this.triggers.push({ value: val, disabled });
            }
        },
        focusTrigger(val) {
            const el = this.$el.querySelector('[role=tab][data-tab-value=' + JSON.stringify(val) + ']');
            if (el) el.focus();
            if (this.activationMode === 'automatic') this.active = val;
        },
        moveFocus(dir) {
            const enabled = this.triggers.filter(t => !t.disabled);
            if (!enabled.length) return;
            const focused = document.activeElement?.dataset?.tabValue;
            let idx = enabled.findIndex(t => t.value === focused);
            if (idx < 0) idx = enabled.findIndex(t => t.value === this.active);
            let next = idx + dir;
            if (this.loop) {
                next = (next + enabled.length) % enabled.length;
            } else {
                next = Math.max(0, Math.min(next, enabled.length - 1));
            }
            this.focusTrigger(enabled[next].value);
        }
    }"
    :data-orientation="orientation"
    @keydown.arrow-right.prevent="orientation === 'horizontal' && moveFocus(1)"
    @keydown.arrow-left.prevent="orientation === 'horizontal' && moveFocus(-1)"
    @keydown.arrow-down.prevent="orientation === 'vertical' ? moveFocus(1) : null"
    @keydown.arrow-up.prevent="orientation === 'vertical' ? moveFocus(-1) : null"
    @keydown.home.prevent="const e = triggers.filter(t => !t.disabled); e.length && focusTrigger(e[0].value)"
    @keydown.end.prevent="const e = triggers.filter(t => !t.disabled); e.length && focusTrigger(e[e.length - 1].value)"
>
    {{ $slot }}
</div>
