@props([
    'name'              => null,
    'value'             => null,
    'options'           => [],
    'placeholder'       => null,
    'searchPlaceholder' => 'Buscar...',
    'emptyMessage'      => 'Sin resultados.',
    'size'              => 'md',
    'state'             => null,
    'disabled'          => false,
    'readOnly'          => false,
    'required'          => false,
    'clearable'         => false,
])

@php
// Supports flat options and grouped options:
//   Flat:    ['value' => 'label']  or  [['value'=>..., 'label'=>..., 'disabled'=>...]]
//   Grouped: [['label' => 'Group', 'options' => [...]]]
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

// PHP-side size/state classes for monolithic mode
$triggerSize = match($size) {
    'sm'    => 'h-8 pl-3 pr-2 text-[13px] gap-1.5',
    'lg'    => 'h-12 pl-4 pr-3 text-base gap-2.5',
    default => 'h-10 pl-3 pr-2.5 text-sm gap-2',
};

$stateClass = match($state) {
    'destructive' => 'border-destructive-border focus-visible:ring-destructive',
    'success'     => 'border-success-border focus-visible:ring-success',
    'warning'     => 'border-warning-border focus-visible:ring-warning',
    'info'        => 'border-info-border focus-visible:ring-info',
    default       => 'border-input focus-visible:ring-ring',
};

$chevronSize = match($size) {
    'sm'    => 'size-3.5',
    'lg'    => 'size-5',
    default => 'size-4',
};

$clearSize = match($size) {
    'sm'    => 'size-3',
    'lg'    => 'size-[18px]',
    default => 'size-3.5',
};

$inputSize = match($size) {
    'sm'    => 'h-8 px-3 text-[13px]',
    'lg'    => 'h-12 px-4 text-base',
    default => 'h-9 px-3 text-sm',
};

$itemSize = match($size) {
    'sm'    => 'py-1 text-[13px]',
    'lg'    => 'py-2 text-base',
    default => 'py-1.5 text-sm',
};
@endphp

<div
    {{ $attributes->twMerge('inline-block w-full') }}
    x-data="{
        open:              false,
        value:             @js($value),
        highlighted:       null,
        placement:         'bottom',
        search:            '',
        options:           @js($normalized),
        readOnly:          @js($readOnly),
        disabled:          @js($disabled),
        size:              @js($size),
        state:             @js($state),
        placeholder:       @js($placeholder),
        searchPlaceholder: @js($searchPlaceholder),
        clearable:         @js($clearable),
        matchCount:        9999,
        uid: null, top: 0, left: 0, w: 0, _oc: null, _sc: null,

        init() {
            this.uid = 'cb-' + Math.random().toString(36).slice(2, 9);
            this.$watch('search', () => {
                this.$nextTick(() => this._updateMatchCount());
            });
        },

        _updateMatchCount() {
            const lb = document.getElementById(this.uid + '-listbox');
            if (!lb) return;
            this.matchCount = [...lb.querySelectorAll('[role=option]')]
                .filter(el => el.style.display !== 'none').length;
        },

        _allOpts() {
            return this.options.flatMap(o => o.group ? o.options : [o]);
        },

        get label() {
            if (this.value === null || this.value === undefined || this.value === '') return null;
            if (this.options.length) {
                const found = this._allOpts().find(o => String(o.value) === String(this.value));
                return found ? found.label : null;
            }
            // Composition mode: read label from DOM
            const lb = document.getElementById(this.uid + '-listbox');
            if (!lb) return null;
            const el = lb.querySelector('[role=option][data-value=\'' + CSS.escape(String(this.value)) + '\']');
            return el?.dataset.label ?? el?.textContent?.trim() ?? null;
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
            if (this.options.length) {
                return this.filtered.flatMap(o => o.group ? o.options : [o]).filter(o => !o.disabled);
            }
            // Composition mode: query visible, enabled items from DOM
            const lb = document.getElementById(this.uid + '-listbox');
            if (!lb) return [];
            return [...lb.querySelectorAll('[role=option]')]
                .filter(el => el.style.display !== 'none' && !el.hasAttribute('aria-disabled'))
                .map(el => ({ value: el.dataset.value }));
        },

        _open() {
            if (this.readOnly || this.disabled) return;
            this.open = true;
            this.search = '';
            this.highlighted = this.value;
            this.$nextTick(() => {
                this._place();
                this.$refs.searchInput?.focus();
                this._scrollTo(this.highlighted);
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

        select(val) {
            this.value = val;
            this.$dispatch('change', { value: val });
            this._close();
        },

        clear(e) {
            e.stopPropagation();
            this.value = null;
            this.$dispatch('change', { value: null });
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
            if (this.options.length) {
                const opt = this._allOpts().find(o => String(o.value) === String(this.highlighted));
                if (opt && !opt.disabled) this.select(opt.value);
            } else {
                const lb = document.getElementById(this.uid + '-listbox');
                if (!lb) return;
                const el = lb.querySelector('[role=option][data-value=\'' + CSS.escape(String(this.highlighted)) + '\']');
                if (el && !el.hasAttribute('aria-disabled')) this.select(this.highlighted);
            }
        },

        _scrollTo(val) {
            if (val === null || val === undefined) return;
            document.getElementById(this.uid + ':' + val)?.scrollIntoView({ block: 'nearest' });
        },

        _place() {
            const p = document.getElementById(this.uid);
            const t = this.$refs.trigger;
            if (!p || !t) return;
            const r  = t.getBoundingClientRect();
            const ph = p.offsetHeight;
            const g  = 4, m = 8;
            this.w = r.width;
            this.left = r.left;
            if (r.bottom + ph + g > innerHeight - m) {
                this.top       = r.top - ph - g;
                this.placement = 'top';
            } else {
                this.top       = r.bottom + g;
                this.placement = 'bottom';
            }
        },

        // Class helpers for composition-mode subcomponents
        _inputCls() {
            const s = { sm: 'h-8 pl-3 pr-2 text-[13px] gap-1.5', md: 'h-10 pl-3 pr-2.5 text-sm gap-2', lg: 'h-12 pl-4 pr-3 text-base gap-2.5' };
            const t = { destructive: 'border-destructive-border focus-visible:ring-destructive', success: 'border-success-border focus-visible:ring-success', warning: 'border-warning-border focus-visible:ring-warning', info: 'border-info-border focus-visible:ring-info' };
            return (s[this.size] ?? s.md) + ' ' + (t[this.state] ?? 'border-input focus-visible:ring-ring');
        },
        _searchCls() {
            return { sm: 'h-8 px-3 text-[13px]', md: 'h-9 px-3 text-sm', lg: 'h-12 px-4 text-base' }[this.size] ?? 'h-9 px-3 text-sm';
        },
        _itemCls() {
            return { sm: 'py-1 text-[13px]', md: 'py-1.5 text-sm', lg: 'py-2 text-base' }[this.size] ?? 'py-1.5 text-sm';
        },
        _chevronCls() {
            return { sm: 'size-3.5', md: 'size-4', lg: 'size-5' }[this.size] ?? 'size-4';
        },
        _clearCls() {
            return { sm: 'size-3', md: 'size-3.5', lg: 'size-[18px]' }[this.size] ?? 'size-3.5';
        },
    }"
    @keydown.escape.prevent="open && _close()"
    @keydown.arrow-down.prevent="open ? move(1) : _open()"
    @keydown.arrow-up.prevent="open && move(-1)"
    @keydown.enter.prevent="open ? commit() : _open()"
    @keydown.tab="open && _close()"
>
    @if($name)
        <input type="hidden" name="{{ $name }}" :value="value" @if($required) required @endif />
    @endif

    @if($slot->isEmpty())
        {{-- Monolithic mode: render trigger + dropdown inline --}}

        <button
            type="button"
            role="combobox"
            x-ref="trigger"
            :aria-expanded="open.toString()"
            :aria-controls="uid + '-listbox'"
            aria-haspopup="listbox"
            @if($required) aria-required="true" @endif
            @if($readOnly) aria-readonly="true" @endif
            @click="open ? _close() : _open()"
            @if($disabled) disabled @endif
            class="w-full flex items-center justify-between whitespace-nowrap rounded-md border bg-background text-foreground shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 {{ $triggerSize }} {{ $stateClass }}"
            :class="readOnly ? 'cursor-default' : 'cursor-pointer'"
        >
            <span
                x-text="label ?? @js($placeholder ?? '')"
                :class="label === null ? 'text-muted-foreground' : ''"
                class="truncate flex-1 text-left"
            ></span>

            <span class="flex items-center shrink-0 gap-1">
                @if($clearable)
                <span
                    x-show="value !== null && value !== undefined && value !== ''"
                    @click="clear($event)"
                    tabindex="-1"
                    aria-label="Limpiar"
                    class="flex items-center justify-center p-0.5 rounded text-muted-foreground hover:text-foreground transition-colors"
                >
                    <x-lucide-x class="{{ $clearSize }}" />
                </span>
                @endif
                <x-lucide-chevron-down class="text-muted-foreground transition-transform duration-200 {{ $chevronSize }}"
                    x-bind:class="open ? 'rotate-180' : ''" />
            </span>
        </button>

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
                class="fixed z-50 rounded-md border border-border bg-popover shadow-md overflow-hidden"
            >
                <div class="border-b border-border p-1">
                    <div class="relative flex items-center">
                        <x-lucide-search class="pointer-events-none absolute left-2.5 size-3.5 text-muted-foreground" />
                        <input
                            type="text"
                            x-ref="searchInput"
                            x-model="search"
                            autocomplete="off"
                            placeholder="{{ $searchPlaceholder }}"
                            class="w-full {{ $inputSize }} pl-8 bg-transparent text-foreground placeholder:text-muted-foreground focus-visible:outline-none"
                        />
                    </div>
                </div>

                <div
                    :id="uid + '-listbox'"
                    role="listbox"
                    :aria-activedescendant="highlighted !== null && highlighted !== undefined ? uid + ':' + highlighted : false"
                    class="max-h-56 overflow-y-auto p-1"
                >
                    <template x-if="filtered.length === 0">
                        <div class="py-6 text-center text-sm text-muted-foreground select-none">{{ $emptyMessage }}</div>
                    </template>

                    <template x-if="filtered.length > 0">
                        <template x-for="item in filtered" :key="item.group ? 'g:' + item.label : 'o:' + item.value">
                            <div role="presentation">

                                <div
                                    x-show="item.group"
                                    x-text="item.label"
                                    class="px-2 py-1.5 text-xs font-semibold text-muted-foreground select-none"
                                ></div>

                                <template x-if="item.group">
                                    <div role="presentation">
                                        <template x-for="opt in item.options" :key="opt.value">
                                            <div
                                                role="option"
                                                :id="uid + ':' + opt.value"
                                                :aria-selected="String(value) === String(opt.value)"
                                                :aria-disabled="opt.disabled || null"
                                                @click="!opt.disabled && select(opt.value)"
                                                @mouseenter="!opt.disabled && (highlighted = opt.value)"
                                                :class="{
                                                    'bg-accent text-accent-foreground': String(highlighted) === String(opt.value),
                                                    'opacity-50 cursor-not-allowed': opt.disabled,
                                                    'cursor-pointer': !opt.disabled,
                                                }"
                                                class="relative flex items-center rounded-sm pl-8 pr-2 {{ $itemSize }} select-none outline-none"
                                            >
                                                <span class="absolute left-2 flex size-4 items-center justify-center" x-show="String(value) === String(opt.value)">
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
                                        :aria-selected="String(value) === String(item.value)"
                                        :aria-disabled="item.disabled || null"
                                        @click="!item.disabled && select(item.value)"
                                        @mouseenter="!item.disabled && (highlighted = item.value)"
                                        :class="{
                                            'bg-accent text-accent-foreground': String(highlighted) === String(item.value),
                                            'opacity-50 cursor-not-allowed': item.disabled,
                                            'cursor-pointer': !item.disabled,
                                        }"
                                        class="relative flex items-center rounded-sm pl-8 pr-2 {{ $itemSize }} select-none outline-none"
                                    >
                                        <span class="absolute left-2 flex size-4 items-center justify-center" x-show="String(value) === String(item.value)">
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
    @else
        {{-- Composition mode: slot contains subcomponents --}}
        {{ $slot }}
    @endif
</div>
