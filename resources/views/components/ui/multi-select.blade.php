@props([
    'name'              => null,
    'value'             => [],
    'options'           => [],
    'placeholder'       => 'Seleccionar...',
    'searchPlaceholder' => 'Buscar...',
    'emptyMessage'      => 'Sin resultados.',
    'state'             => null,
    'disabled'          => false,
    'clearable'         => true,
])

@php
// Supports flat options: ['value' => 'label'] or [['value'=>..., 'label'=>...]]
// Supports grouped options: [['label' => 'Group', 'options' => [...]]]
$normalized = [];
foreach ($options as $k => $v) {
    if (is_array($v) && array_key_exists('options', $v)) {
        $groupOpts = [];
        foreach ($v['options'] as $gk => $gv) {
            $groupOpts[] = is_array($gv)
                ? ['value' => (string)($gv['value'] ?? $gk), 'label' => (string)($gv['label'] ?? ''), 'disabled' => (bool)($gv['disabled'] ?? false)]
                : ['value' => (string)$gk, 'label' => (string)$gv, 'disabled' => false];
        }
        $normalized[] = ['group' => true, 'label' => (string)($v['label'] ?? ''), 'options' => $groupOpts];
    } else {
        $normalized[] = is_array($v)
            ? ['value' => (string)($v['value'] ?? $k), 'label' => (string)($v['label'] ?? ''), 'disabled' => (bool)($v['disabled'] ?? false)]
            : ['value' => (string)$k, 'label' => (string)$v, 'disabled' => false];
    }
}

$stateClass = match($state) {
    'destructive' => 'border-destructive-border focus-visible:ring-destructive',
    'success'     => 'border-success-border focus-visible:ring-success',
    'warning'     => 'border-warning-border focus-visible:ring-warning',
    'info'        => 'border-info-border focus-visible:ring-info',
    default       => 'border-input focus-visible:ring-ring',
};

// Always emit as name[] for array form submission
$inputName = $name ? rtrim($name, '[]') . '[]' : null;
@endphp

<div
    {{ $attributes->twMerge('inline-block w-full') }}
    x-data="{
        open:        false,
        values:      @js(array_map('strval', (array) $value)),
        highlighted: null,
        placement:   'bottom',
        search:      '',
        options:     @js($normalized),
        disabled:    @js($disabled),
        clearable:   @js($clearable),
        uid: null, top: 0, left: 0, w: 0, _oc: null, _sc: null,

        init() {
            this.uid = 'ms-' + Math.random().toString(36).slice(2, 9);
            this.$watch('values', () => {
                if (this.open) this.$nextTick(() => this._place());
            });
        },

        _allOpts() {
            return this.options.flatMap(o => o.group ? o.options : [o]);
        },

        get selectedOptions() {
            return this._allOpts().filter(o => this.values.includes(String(o.value)));
        },

        get filtered() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.options;
            return this.options.reduce((acc, o) => {
                if (o.group) {
                    const opts = o.options.filter(i => i.label.toLowerCase().includes(q));
                    if (opts.length) acc.push({ ...o, options: opts });
                } else if (o.label.toLowerCase().includes(q)) {
                    acc.push(o);
                }
                return acc;
            }, []);
        },

        _navItems() {
            return this.filtered.flatMap(o => o.group ? o.options : [o]).filter(o => !o.disabled);
        },

        _open() {
            if (this.disabled) return;
            this.open = true;
            this.search = '';
            this.highlighted = null;
            this.$nextTick(() => {
                this._place();
                this.$refs.searchInput?.focus();
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
            this.search = '';
            this.highlighted = null;
            document.removeEventListener('click', this._oc);
            window.removeEventListener('scroll', this._sc, true);
            window.removeEventListener('resize', this._sc);
            this.$refs.trigger?.focus();
        },

        toggle(val) {
            const s = String(val);
            const i = this.values.indexOf(s);
            if (i === -1) this.values.push(s);
            else this.values.splice(i, 1);
            this.$dispatch('change', { values: this.values });
        },

        deselect(val) {
            this.values = this.values.filter(v => v !== String(val));
            this.$dispatch('change', { values: this.values });
        },

        clearAll(e) {
            if (e) e.stopPropagation();
            this.values = [];
            this.$dispatch('change', { values: [] });
        },

        move(dir) {
            const items = this._navItems();
            if (!items.length) return;
            let idx = items.findIndex(o => String(o.value) === String(this.highlighted));
            if (idx === -1) idx = dir > 0 ? -1 : items.length;
            idx = ((idx + dir) % items.length + items.length) % items.length;
            this.highlighted = items[idx].value;
            this.$nextTick(() => this._scrollTo(this.highlighted));
        },

        commit() {
            if (this.highlighted === null || this.highlighted === undefined) return;
            const opt = this._allOpts().find(o => String(o.value) === String(this.highlighted));
            if (opt && !opt.disabled) this.toggle(opt.value);
        },

        _scrollTo(val) {
            if (val === null || val === undefined) return;
            document.getElementById(this.uid + ':' + val)?.scrollIntoView({ block: 'nearest' });
        },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r = t.getBoundingClientRect();
            const ph = p.offsetHeight;
            const g = 4, m = 8;
            this.w    = r.width;
            this.left = r.left;
            if (r.bottom + ph + g > innerHeight - m) {
                this.top       = r.top - ph - g;
                this.placement = 'top';
            } else {
                this.top       = r.bottom + g;
                this.placement = 'bottom';
            }
        },
    }"
    @keydown.escape.prevent="open && _close()"
    @keydown.arrow-down.prevent="open ? move(1) : _open()"
    @keydown.arrow-up.prevent="open && move(-1)"
    @keydown.enter.prevent="open ? commit() : _open()"
    @keydown.tab="open && _close()"
>
    {{-- Hidden inputs for form submission --}}
    @if($inputName)
        <template x-for="v in values" :key="v">
            <input type="hidden" name="{{ $inputName }}" :value="v" />
        </template>
    @endif

    {{-- Trigger --}}
    <button
        type="button"
        role="combobox"
        x-ref="trigger"
        :aria-expanded="open.toString()"
        :aria-controls="uid + '-listbox'"
        aria-haspopup="listbox"
        @click="open ? _close() : _open()"
        @if($disabled) disabled @endif
        :disabled="disabled"
        class="w-full flex items-start justify-between gap-2 min-h-9 px-3 py-1.5 rounded-full border bg-background text-foreground text-sm shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 cursor-pointer {{ $stateClass }}"
    >
        <span class="flex flex-wrap gap-1 flex-1 min-w-0 self-center">
            <span
                x-show="values.length === 0"
                class="text-muted-foreground leading-none"
            >{{ $placeholder }}</span>

            <template x-for="opt in selectedOptions" :key="opt.value">
                <span class="inline-flex items-center gap-0.5 bg-secondary text-secondary-foreground border border-border rounded px-1.5 py-0.5 text-xs font-medium leading-none">
                    <span x-text="opt.label"></span>
                    <span
                        @click.stop="deselect(opt.value)"
                        tabindex="-1"
                        aria-label="Quitar"
                        class="flex items-center justify-center ml-0.5 rounded-sm hover:bg-foreground/10 transition-colors p-0.5 cursor-pointer"
                    >
                        <x-lucide-x class="size-2.5" />
                    </span>
                </span>
            </template>
        </span>

        <span class="flex items-center shrink-0 gap-1 mt-1.25">
            <span
                x-show="values.length > 0 && clearable"
                @click.stop="clearAll($event)"
                tabindex="-1"
                aria-label="Limpiar todo"
                class="flex items-center justify-center p-0.5 rounded text-muted-foreground hover:text-foreground transition-colors"
            >
                <x-lucide-x class="size-3.5" />
            </span>
            <x-lucide-chevron-down
                class="size-4 text-muted-foreground transition-transform duration-200"
                x-bind:class="open ? 'rotate-180' : ''"
            />
        </span>
    </button>

    {{-- Dropdown --}}
    <template x-teleport="body">
        <div
            :id="uid"
            x-show="open"
            x-cloak
            :style="`top:${top}px;left:${left}px;width:${w}px`"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-y-95"
            x-transition:enter-end="opacity-100 scale-y-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-y-100"
            x-transition:leave-end="opacity-0 scale-y-95"
            :class="placement === 'top' ? 'origin-bottom' : 'origin-top'"
            class="fixed z-(--z-popover) rounded-md border border-border bg-popover shadow-md overflow-hidden"
        >
            {{-- Search --}}
            <div class="border-b border-border p-1">
                <div class="relative flex items-center">
                    <x-lucide-search class="pointer-events-none absolute left-2.5 size-3.5 text-muted-foreground" />
                    <input
                        type="text"
                        x-ref="searchInput"
                        x-model="search"
                        autocomplete="off"
                        placeholder="{{ $searchPlaceholder }}"
                        class="w-full h-9 pl-8 pr-3 bg-transparent text-sm text-foreground placeholder:text-muted-foreground focus-visible:outline-none"
                    />
                </div>
            </div>

            {{-- Options list --}}
            <div
                :id="uid + '-listbox'"
                role="listbox"
                aria-multiselectable="true"
                :aria-activedescendant="highlighted !== null && highlighted !== undefined ? uid + ':' + highlighted : false"
                class="max-h-56 overflow-y-auto p-1"
            >
                <template x-if="filtered.length === 0">
                    <div class="py-6 text-center text-sm text-muted-foreground select-none">{{ $emptyMessage }}</div>
                </template>

                <template x-if="filtered.length > 0">
                    <template x-for="item in filtered" :key="item.group ? 'g:' + item.label : 'o:' + item.value">
                        <div role="presentation">

                            <template x-if="item.group">
                                <div>
                                    <div
                                        x-text="item.label"
                                        class="px-2 py-1.5 text-xs font-semibold text-muted-foreground select-none"
                                    ></div>
                                    <template x-for="opt in item.options" :key="opt.value">
                                        <div
                                            role="option"
                                            :id="uid + ':' + opt.value"
                                            :aria-selected="values.includes(String(opt.value))"
                                            :aria-disabled="opt.disabled || null"
                                            @click="!opt.disabled && toggle(opt.value)"
                                            @mouseenter="!opt.disabled && (highlighted = opt.value)"
                                            :class="{
                                                'bg-accent text-accent-foreground': String(highlighted) === String(opt.value),
                                                'bg-primary/5': values.includes(String(opt.value)) && String(highlighted) !== String(opt.value),
                                                'opacity-50 cursor-not-allowed': opt.disabled,
                                                'cursor-pointer': !opt.disabled,
                                            }"
                                            class="relative flex items-center rounded-sm pl-8 pr-2 py-1.5 text-sm select-none outline-none"
                                        >
                                            <span class="absolute left-2 flex size-4 items-center justify-center" x-show="values.includes(String(opt.value))">
                                                <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                            </span>
                                            <span x-text="opt.label"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <template x-if="!item.group">
                                <div
                                    role="option"
                                    :id="uid + ':' + item.value"
                                    :aria-selected="values.includes(String(item.value))"
                                    :aria-disabled="item.disabled || null"
                                    @click="!item.disabled && toggle(item.value)"
                                    @mouseenter="!item.disabled && (highlighted = item.value)"
                                    :class="{
                                        'bg-accent text-accent-foreground': String(highlighted) === String(item.value),
                                        'bg-primary/5': values.includes(String(item.value)) && String(highlighted) !== String(item.value),
                                        'opacity-50 cursor-not-allowed': item.disabled,
                                        'cursor-pointer': !item.disabled,
                                    }"
                                    class="relative flex items-center rounded-sm pl-8 pr-2 py-1.5 text-sm select-none outline-none"
                                >
                                    <span class="absolute left-2 flex size-4 items-center justify-center" x-show="values.includes(String(item.value))">
                                        <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                    </span>
                                    <span x-text="item.label"></span>
                                </div>
                            </template>

                        </div>
                    </template>
                </template>
            </div>
        </div>
    </template>
</div>
