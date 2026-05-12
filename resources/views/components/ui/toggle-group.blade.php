@props([
    'type'    => 'single',   // single | multiple
    'value'   => null,       // string (single) | array (multiple)
    'variant' => 'default',  // default | outline
    'size'    => 'md',       // sm | md | lg
])

<div
    role="group"
    x-data="{
        type:    @js($type),
        value:   @js($type === 'multiple' ? (is_array($value) ? $value : ($value ? [$value] : [])) : $value),
        variant: @js($variant),
        size:    @js($size),
        isOn(val) {
            return this.type === 'multiple'
                ? this.value.includes(val)
                : this.value === val;
        },
        toggle(val) {
            if (this.type === 'multiple') {
                const i = this.value.indexOf(val);
                i >= 0 ? this.value.splice(i, 1) : this.value.push(val);
            } else {
                this.value = this.value === val ? null : val;
            }
        }
    }"
    {{ $attributes->twMerge('inline-flex items-center gap-1') }}
>
    {{ $slot }}
</div>
