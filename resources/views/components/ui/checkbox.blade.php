@props([
    'checked'  => false,
    'name'     => null,
    'value'    => 'on',
    'state'    => null,
])

@php
$checkedClass = match($state) {
    'destructive' => 'bg-destructive border-destructive text-destructive-foreground',
    'success'     => 'bg-success border-success text-success-foreground',
    'warning'     => 'bg-warning border-warning text-warning-foreground',
    'info'        => 'bg-info border-info text-info-foreground',
    default       => 'bg-primary border-primary text-primary-foreground',
};
$uncheckedClass = match($state) {
    'destructive' => 'bg-background border-destructive-border',
    'success'     => 'bg-background border-success-border',
    'warning'     => 'bg-background border-warning-border',
    'info'        => 'bg-background border-info-border',
    default       => 'bg-background border-input',
};
@endphp

<button
    type="button"
    role="checkbox"
    x-data="{ checked: @js((bool) $checked) }"
    :aria-checked="checked.toString()"
    :data-state="checked ? 'checked' : 'unchecked'"
    @click="checked = !checked"
    :class="checked ? '{{ $checkedClass }}' : '{{ $uncheckedClass }}'"
    {{ $attributes->twMerge('size-4 shrink-0 rounded border transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 flex items-center justify-center cursor-pointer') }}
>
    <x-lucide-check x-show="checked"
        x-transition:enter="transition ease-out duration-75"
        x-transition:enter-start="opacity-0 scale-50"
        x-transition:enter-end="opacity-100 scale-100"
        class="size-3"
        stroke-width="3" />
    @if($name)
        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            :checked="checked"
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
        />
    @endif
</button>
