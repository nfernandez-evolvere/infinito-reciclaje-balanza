@props([
    'length'   => 6,      // cantidad total de dígitos
    'name'     => null,
    'value'    => '',
    'pattern'  => null,   // regexp string para validar cada carácter (ej: '[0-9]')
    'disabled' => false,
    'state'    => null,   // null | destructive | success
])

@php
$stateRing = match($state) {
    'destructive' => 'has-[:focus]:ring-destructive has-aria-invalid:ring-destructive',
    'success'     => 'has-[:focus]:ring-success',
    default       => 'has-[:focus]:ring-ring',
};
@endphp

<div
    x-data="{
        slots:    Array(@js($length)).fill(''),
        value:    @js((string) $value),
        length:   @js((int) $length),
        pattern:  @js($pattern),
        disabled: @js((bool) $disabled),

        init() {
            // Populate slots from initial value
            if (this.value) {
                this.value.split('').slice(0, this.length).forEach((c, i) => { this.slots[i] = c; });
            }
        },

        // Returns the active slot index (first empty, or last)
        activeIdx() {
            const first = this.slots.findIndex(s => s === '');
            return first === -1 ? this.length - 1 : first;
        },

        onKey(e) {
            if (this.disabled) return;
            const idx = this.activeIdx();
            if (e.key === 'Backspace') {
                e.preventDefault();
                const target = this.slots[idx] ? idx : Math.max(idx - 1, 0);
                this.slots[target] = '';
                this.slots = [...this.slots];
                this.updateValue();
                return;
            }
            if (e.key === 'ArrowLeft') { e.preventDefault(); this.$refs['slot' + Math.max(idx - 1, 0)]?.focus(); return; }
            if (e.key === 'ArrowRight') { e.preventDefault(); this.$refs['slot' + Math.min(idx + 1, this.length - 1)]?.focus(); return; }
            if (e.key.length !== 1) return;
            if (this.pattern && !new RegExp('^' + this.pattern + '$').test(e.key)) return;
            e.preventDefault();
            this.slots[idx] = e.key;
            this.slots = [...this.slots];
            this.updateValue();
            this.$nextTick(() => {
                const next = Math.min(idx + 1, this.length - 1);
                this.$refs['slot' + next]?.focus();
            });
        },

        onPaste(e) {
            if (this.disabled) return;
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            let filled = 0;
            for (let i = 0; i < this.length && filled < text.length; i++) {
                const c = text[filled];
                if (!this.pattern || new RegExp('^' + this.pattern + '$').test(c)) {
                    this.slots[i] = c;
                    filled++;
                }
            }
            this.slots = [...this.slots];
            this.updateValue();
            this.$nextTick(() => {
                const next = Math.min(filled, this.length - 1);
                this.$refs['slot' + next]?.focus();
            });
        },

        updateValue() {
            this.value = this.slots.join('');
        }
    }"
    @keydown="onKey($event)"
    @paste="onPaste($event)"
    class="inline-flex items-center gap-2"
    {{ $attributes }}
>
    {{-- Hidden input para form submission --}}
    @if($name)
        <input type="hidden" name="{{ $name }}" x-model="value" />
    @endif

    {{-- Slots --}}
    <div class="flex items-center rounded-lg {{ $stateRing }}">
        @for($i = 0; $i < $length; $i++)
            @php
                $isGroupStart = $i === 0 || ($i > 0 && $i % 3 === 0);
                $isGroupEnd   = $i === $length - 1 || ($i < $length - 1 && ($i + 1) % 3 === 0);
                $slotRounded  = ($isGroupStart ? 'rounded-l-lg ' : '') . ($isGroupEnd ? 'rounded-r-lg' : '');
                $slotBorder   = $isGroupStart ? 'border-l' : '';
            @endphp
            <div
                x-ref="slot{{ $i }}"
                tabindex="{{ $disabled ? '-1' : '0' }}"
                @click="$refs['slot{{ $i }}'].focus()"
                @focus="$refs['slot{{ $i }}'].dataset.active = 'true'"
                @blur="$refs['slot{{ $i }}'].dataset.active = 'false'"
                :data-active="false"
                :aria-invalid="@js($state === 'destructive') ? 'true' : undefined"
                class="relative flex size-10 cursor-text select-none items-center justify-center border-y border-r border-input bg-background text-sm font-medium text-foreground transition-all {{ $slotBorder }} {{ $slotRounded }} outline-none focus:z-10 focus:border-ring focus:ring-2 focus:ring-ring/50 data-[active=true]:z-10 data-[active=true]:border-ring data-[active=true]:ring-2 data-[active=true]:ring-ring/50 {{ $state === 'destructive' ? 'border-destructive-border aria-invalid:ring-destructive/30' : '' }}"
            >
                <span x-text="slots[{{ $i }}]"></span>
                {{-- Cursor parpadeante cuando el slot está activo y vacío --}}
                <span
                    x-show="$el.dataset.active === 'true' && !slots[{{ $i }}]"
                    class="pointer-events-none absolute inset-0 flex items-center justify-center"
                >
                    <span class="h-4 w-px animate-[caret-blink_1.2s_ease-out_infinite] bg-foreground"></span>
                </span>
            </div>
            @if($i < $length - 1 && ($i + 1) % 3 === 0)
                {{-- Separador automático cada 3 dígitos --}}
                <span class="mx-1 text-muted-foreground">—</span>
            @endif
        @endfor
    </div>
</div>
