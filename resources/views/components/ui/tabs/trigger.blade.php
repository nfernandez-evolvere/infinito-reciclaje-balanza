@props(['value' => '', 'disabled' => false])

<button type="button" role="tab" data-tab-value="{{ $value }}" :aria-selected="(active === @js($value)).toString()"
    aria-controls="tab-panel-{{ $value }}" :data-state="active === @js($value) ? 'active' : 'inactive'"
    :data-orientation="orientation" @if($disabled) disabled data-disabled @endif
    :tabindex="active === @js($value) ? '0' : '-1'" @click="active = @js($value)"
    x-init="registerTrigger(@js($value), @js((bool) $disabled))" :class="active === @js($value)
        ? 'bg-background text-foreground shadow-sm'
        : 'text-muted-foreground hover:text-foreground'" {{ $attributes->twMerge('inline-flex gap-1 items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 cursor-pointer') }}>
    {{ $slot }}
</button>