@props([
    'mode'            => 'single',      // single | multiple | range
    'value'           => null,          // single: ISO string; multiple: array; range: {start, end}
    'showOutsideDays' => true,
    'minDate'         => null,
    'maxDate'         => null,
    'disabledDates'   => [],
    'name'            => null,          // hidden input name
    'weekStartsOn'    => 1,             // 0=Sunday, 1=Monday
])

@php
// Normalize value for Alpine
$initialValue = match($mode) {
    'multiple' => is_array($value) ? $value : ($value ? [$value] : []),
    'range'    => is_array($value) ? $value : null,
    default    => $value,
};
@endphp

<div
    x-data="{
        mode:          @js($mode),
        value:         @js($initialValue),
        viewYear:      0,
        viewMonth:     0,   // 0-indexed
        hovering:      null,
        weekStartsOn:  @js((int)$weekStartsOn),
        minDate:       @js($minDate),
        maxDate:       @js($maxDate),
        disabledDates: @js($disabledDates),
        showOutside:   @js((bool)$showOutsideDays),

        init() {
            const now = new Date();
            this.viewYear  = now.getFullYear();
            this.viewMonth = now.getMonth();

            // Jump view to selected value
            if (this.mode === 'single' && this.value) {
                const d = new Date(this.value + 'T00:00:00');
                if (!isNaN(d)) { this.viewYear = d.getFullYear(); this.viewMonth = d.getMonth(); }
            } else if (this.mode === 'range' && this.value?.start) {
                const d = new Date(this.value.start + 'T00:00:00');
                if (!isNaN(d)) { this.viewYear = d.getFullYear(); this.viewMonth = d.getMonth(); }
            } else if (this.mode === 'multiple' && this.value?.length) {
                const d = new Date(this.value[0] + 'T00:00:00');
                if (!isNaN(d)) { this.viewYear = d.getFullYear(); this.viewMonth = d.getMonth(); }
            }
        },

        prevMonth() {
            if (this.viewMonth === 0) { this.viewYear--; this.viewMonth = 11; }
            else this.viewMonth--;
        },

        nextMonth() {
            if (this.viewMonth === 11) { this.viewYear++; this.viewMonth = 0; }
            else this.viewMonth++;
        },

        get monthLabel() {
            return new Date(this.viewYear, this.viewMonth, 1)
                .toLocaleString(navigator.language, { month: 'long', year: 'numeric' });
        },

        get weekDays() {
            // Generate 7 abbreviated day names starting from weekStartsOn
            // Base: 2024-01-07 = Sunday, 2024-01-08 = Monday
            const days = [];
            for (let i = 0; i < 7; i++) {
                const d = new Date(2024, 0, 7 + this.weekStartsOn + i);
                days.push(d.toLocaleString(navigator.language, { weekday: 'narrow' }));
            }
            return days;
        },

        get days() {
            const year = this.viewYear, month = this.viewMonth;
            const firstDay = new Date(year, month, 1);
            let dow = firstDay.getDay();  // 0=Sun
            // Offset to reach the configured week start
            const offset = ((dow - this.weekStartsOn) + 7) % 7;
            const start = new Date(firstDay);
            start.setDate(start.getDate() - offset);
            const result = [];
            for (let i = 0; i < 42; i++) {
                const d = new Date(start);
                d.setDate(start.getDate() + i);
                result.push(d.toISOString().slice(0, 10));
            }
            return result;
        },

        get todayISO() {
            return new Date().toISOString().slice(0, 10);
        },

        isCurrentMonth(iso) {
            return iso.slice(0, 7) === String(this.viewYear).padStart(4, '0') + '-' + String(this.viewMonth + 1).padStart(2, '0');
        },

        isToday(iso) { return iso === this.todayISO; },

        isDisabled(iso) {
            if (this.minDate && iso < this.minDate) return true;
            if (this.maxDate && iso > this.maxDate) return true;
            if (this.disabledDates?.includes(iso)) return true;
            return false;
        },

        isSelected(iso) {
            if (this.mode === 'single')   return iso === this.value;
            if (this.mode === 'multiple') return Array.isArray(this.value) && this.value.includes(iso);
            if (this.mode === 'range')    return this.value?.start === iso || this.value?.end === iso;
            return false;
        },

        isRangeStart(iso) {
            if (this.mode !== 'range') return false;
            const start = this.value?.start;
            const end   = this.value?.end || this.hovering;
            if (!start) return false;
            return end && start > end ? iso === end : iso === start;
        },

        isRangeEnd(iso) {
            if (this.mode !== 'range') return false;
            const start = this.value?.start;
            const end   = this.value?.end || this.hovering;
            if (!start || !end) return false;
            return start > end ? iso === start : iso === end;
        },

        isRangeMiddle(iso) {
            if (this.mode !== 'range') return false;
            const start = this.value?.start;
            const end   = this.value?.end || this.hovering;
            if (!start || !end) return false;
            const [s, e] = start <= end ? [start, end] : [end, start];
            return iso > s && iso < e;
        },

        hasRangeEnd() {
            return this.mode === 'range' && (this.value?.end || this.hovering);
        },

        selectDay(iso) {
            if (this.isDisabled(iso)) return;

            if (this.mode === 'single') {
                this.value = this.value === iso ? null : iso;
                this.$dispatch('change', { value: this.value });

            } else if (this.mode === 'multiple') {
                if (!Array.isArray(this.value)) this.value = [];
                const idx = this.value.indexOf(iso);
                if (idx >= 0) this.value.splice(idx, 1);
                else this.value.push(iso);
                this.$dispatch('change', { value: [...this.value] });

            } else if (this.mode === 'range') {
                if (!this.value?.start || this.value?.end) {
                    this.value = { start: iso, end: null };
                } else {
                    const start = this.value.start;
                    if (iso === start)     this.value = null;
                    else if (iso < start)  this.value = { start: iso, end: start };
                    else                   this.value = { start, end: iso };
                    this.$dispatch('change', { value: this.value });
                }
                this.hovering = null;
            }
        },
    }"
    {{ $attributes->twMerge('inline-block') }}
>
    <div class="p-3 space-y-4 w-fit rounded-lg border border-border bg-background shadow-sm">

        {{-- Header --}}
        <div class="relative flex items-center justify-center">
            <button
                type="button"
                @click="prevMonth()"
                aria-label="Mes anterior"
                class="absolute left-0 flex size-7 items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
            >
                <x-lucide-chevron-left class="size-4" />
            </button>

            <span x-text="monthLabel" class="text-sm font-medium capitalize"></span>

            <button
                type="button"
                @click="nextMonth()"
                aria-label="Mes siguiente"
                class="absolute right-0 flex size-7 items-center justify-center rounded-md border border-input bg-background hover:bg-accent hover:text-accent-foreground transition-colors"
            >
                <x-lucide-chevron-right class="size-4" />
            </button>
        </div>

        {{-- Weekday headers --}}
        <div class="grid grid-cols-7">
            <template x-for="day in weekDays" :key="day">
                <div class="flex size-9 items-center justify-center text-[0.8rem] font-medium text-muted-foreground">
                    <span x-text="day"></span>
                </div>
            </template>
        </div>

        {{-- Days grid --}}
        <div class="grid grid-cols-7">
            <template x-for="iso in days" :key="iso">
                <div
                    class="relative"
                    role="presentation"
                    x-show="showOutside || isCurrentMonth(iso)"
                >
                    {{-- Range fill background --}}
                    <div
                        class="absolute inset-y-1 left-0 right-0 bg-accent"
                        x-show="isRangeMiddle(iso)"
                    ></div>
                    <div
                        class="absolute inset-y-1 right-0 bg-accent"
                        style="left: 50%"
                        x-show="isRangeStart(iso) && hasRangeEnd()"
                    ></div>
                    <div
                        class="absolute inset-y-1 left-0 bg-accent"
                        style="right: 50%"
                        x-show="isRangeEnd(iso)"
                    ></div>

                    <button
                        type="button"
                        @click="selectDay(iso)"
                        @mouseenter="mode === 'range' && value?.start && !value?.end && (hovering = iso)"
                        @mouseleave="hovering = null"
                        :aria-label="iso"
                        :aria-selected="isSelected(iso) ? 'true' : 'false'"
                        :aria-disabled="isDisabled(iso) ? 'true' : undefined"
                        :tabindex="isCurrentMonth(iso) ? '0' : '-1'"
                        :class="{
                            'bg-primary text-primary-foreground hover:bg-primary hover:text-primary-foreground': isSelected(iso),
                            'bg-accent text-accent-foreground font-semibold': isToday(iso) && !isSelected(iso),
                            'text-muted-foreground opacity-40': !isCurrentMonth(iso),
                            'pointer-events-none opacity-30': isDisabled(iso),
                            'hover:bg-accent hover:text-accent-foreground': !isSelected(iso) && !isDisabled(iso),
                        }"
                        class="relative z-10 inline-flex size-9 items-center justify-center rounded-full text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        <span x-text="new Date(iso + 'T00:00:00').getDate()"></span>
                    </button>
                </div>
            </template>
        </div>

        {{-- Hidden inputs for form submission --}}
        @if($name)
            <template x-if="mode === 'single'">
                <input type="hidden" name="{{ $name }}" :value="value ?? ''" />
            </template>
            <template x-if="mode === 'multiple'">
                <template x-for="v in (value ?? [])" :key="v">
                    <input type="hidden" name="{{ $name }}[]" :value="v" />
                </template>
            </template>
            <template x-if="mode === 'range'">
                <span>
                    <input type="hidden" name="{{ $name }}[start]" :value="value?.start ?? ''" />
                    <input type="hidden" name="{{ $name }}[end]"   :value="value?.end ?? ''" />
                </span>
            </template>
        @endif
    </div>
</div>
