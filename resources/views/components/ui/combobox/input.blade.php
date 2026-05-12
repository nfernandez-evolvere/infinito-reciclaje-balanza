@props([
    'placeholder' => null,   // overrides root placeholder
    'disabled'    => false,
    'required'    => false,
])

{{--
    Trigger button for composition mode.
    Reads size/state/clearable/readOnly from the parent <x-ui.combobox> Alpine scope.
--}}
<button
    type="button"
    role="combobox"
    x-ref="trigger"
    :aria-expanded="open.toString()"
    :aria-controls="uid + '-listbox'"
    aria-haspopup="listbox"
    @if($required) aria-required="true" @endif
    :aria-readonly="readOnly || null"
    @click="open ? _close() : _open()"
    @if($disabled) disabled @endif
    :disabled="disabled"
    {{ $attributes->twMerge('w-full flex items-center justify-between whitespace-nowrap rounded-md border bg-background text-foreground shadow-xs transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50') }}
    :class="_inputCls() + (readOnly ? ' cursor-default' : ' cursor-pointer')"
>
    <span
        x-text="label ?? (@js($placeholder) ?? placeholder ?? '')"
        :class="label === null ? 'text-muted-foreground' : ''"
        class="truncate flex-1 text-left"
    ></span>

    <span class="flex items-center shrink-0 gap-1">
        <span
            x-show="clearable && value !== null && value !== undefined && value !== ''"
            @click="clear($event)"
            tabindex="-1"
            aria-label="Limpiar"
            class="flex items-center justify-center p-0.5 rounded text-muted-foreground hover:text-foreground transition-colors"
        >
            <x-lucide-x x-bind:class="_clearCls()" />
        </span>
        <x-lucide-chevron-down
            class="text-muted-foreground transition-transform duration-200"
            x-bind:class="_chevronCls() + (open ? ' rotate-180' : '')"
        />
    </span>
</button>
