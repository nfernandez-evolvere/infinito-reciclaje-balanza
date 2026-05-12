@props(['placeholder' => 'Seleccionar...'])

<div
    class="relative"
    x-data="{
        open: false,
        selected: '',
        selectedLabel: '',
        options: [],
        placeholder: '{{ addslashes($placeholder) }}',
        init() {
            const sel = this.$refs.nativeSelect;
            this.options = Array.from(sel.options).map(o => ({
                value: o.value,
                label: o.text,
                disabled: o.disabled,
            }));
            this.syncFromNative(sel);
            sel.addEventListener('change', () => this.syncFromNative(sel));
        },
        syncFromNative(sel) {
            this.selected = sel.value;
            this.selectedLabel = sel.selectedIndex >= 0 ? sel.options[sel.selectedIndex].text : '';
        },
        choose(opt) {
            if (opt.disabled) return;
            this.selected = opt.value;
            this.selectedLabel = opt.label;
            const sel = this.$refs.nativeSelect;
            sel.value = opt.value;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
            sel.dispatchEvent(new Event('input', { bubbles: true }));
            this.open = false;
        }
    }"
>
    {{-- Select nativo oculto — maneja el form submission y x-model --}}
    <select x-ref="nativeSelect" {{ $attributes->merge(['class' => 'sr-only']) }}>
        {{ $slot }}
    </select>

    {{-- Trigger --}}
    <button
        type="button"
        @click="open = !open"
        :disabled="$refs.nativeSelect.disabled"
        class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm transition-colors hover:bg-accent/30 focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
    >
        <span
            :class="selected ? 'text-foreground' : 'text-muted-foreground'"
            class="truncate"
            x-text="selected ? selectedLabel : placeholder"
        ></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
            class="size-4 shrink-0 text-muted-foreground transition-transform duration-200"
            :class="{ 'rotate-180': open }"
        ><path d="m6 9 6 6 6-6"/></svg>
    </button>

    {{-- Dropdown de opciones --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        @keydown.escape.window="open = false"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-y-auto overflow-x-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md"
    >
        <template x-for="opt in options.filter(o => o.value !== '')" :key="opt.value">
            <div
                @click="choose(opt)"
                :class="{
                    'bg-accent text-accent-foreground': opt.value === selected,
                    'pointer-events-none opacity-50': opt.disabled,
                }"
                class="relative flex w-full cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent hover:text-accent-foreground"
            >
                <span class="mr-2 flex size-4 shrink-0 items-center justify-center">
                    <svg x-show="opt.value === selected" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <span x-text="opt.label"></span>
            </div>
        </template>
    </div>
</div>
