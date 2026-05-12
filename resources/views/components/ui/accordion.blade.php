@props([
    'type'        => 'single',    // single | multiple  (required en Radix, default single)
    'value'       => null,        // string (single) | array (multiple) — initially open
    'collapsible' => false,       // single: permite cerrar el ítem abierto
    'disabled'    => false,       // deshabilita todos los ítems
    'orientation' => 'vertical',  // vertical | horizontal — afecta teclas de navegación
])

@php
$defaultOpen = $type === 'multiple'
    ? (is_array($value) ? $value : ($value ? [$value] : []))
    : ($value ?? null);
@endphp

<div
    {{ $attributes->twMerge('') }}
    x-data="{
        accordionType: @js($type),
        collapsible: @js((bool) $collapsible),
        accordionDisabled: @js((bool) $disabled),
        orientation: @js($orientation),
        openItems: @js($defaultOpen),
        triggers: [],
        registerTrigger(val, disabled) {
            if (!this.triggers.find(t => t.value === val)) {
                this.triggers.push({ value: val, disabled });
            }
        },
        focusTrigger(val) {
            const el = this.$el.querySelector('[data-accordion-trigger][data-item-value=' + JSON.stringify(val) + ']');
            if (el) el.focus();
        },
        moveFocus(dir) {
            const enabled = this.triggers.filter(t => !t.disabled && !this.accordionDisabled);
            if (!enabled.length) return;
            const focused = document.activeElement?.dataset?.itemValue;
            let idx = enabled.findIndex(t => t.value === focused);
            if (idx < 0) idx = 0; else idx = (idx + dir + enabled.length) % enabled.length;
            this.focusTrigger(enabled[idx].value);
        },
        toggle(val) {
            if (this.accordionDisabled) return;
            if (this.accordionType === 'multiple') {
                const i = this.openItems.indexOf(val);
                i >= 0 ? this.openItems.splice(i, 1) : this.openItems.push(val);
            } else {
                this.openItems = this.openItems === val
                    ? (this.collapsible ? null : this.openItems)
                    : val;
            }
        },
        isOpen(val) {
            return this.accordionType === 'multiple'
                ? this.openItems.includes(val)
                : this.openItems === val;
        }
    }"
    :data-orientation="orientation"
    :data-disabled="accordionDisabled ? '' : undefined"
    @keydown.arrow-down.prevent="orientation === 'vertical' && moveFocus(1)"
    @keydown.arrow-up.prevent="orientation === 'vertical' && moveFocus(-1)"
    @keydown.arrow-right.prevent="orientation === 'horizontal' && moveFocus(1)"
    @keydown.arrow-left.prevent="orientation === 'horizontal' && moveFocus(-1)"
    @keydown.home.prevent="const e = triggers.filter(t => !t.disabled && !accordionDisabled); e.length && focusTrigger(e[0].value)"
    @keydown.end.prevent="const e = triggers.filter(t => !t.disabled && !accordionDisabled); e.length && focusTrigger(e[e.length - 1].value)"
>
    {{ $slot }}
</div>
