@props(['value'])

<button
    type="button"
    role="tab"
    id="tab-{{ $value }}"
    aria-controls="panel-{{ $value }}"
    :aria-selected="active === '{{ $value }}' ? 'true' : 'false'"
    :tabindex="active === '{{ $value }}' ? 0 : -1"
    @click="active = '{{ $value }}'"
    :class="active === '{{ $value }}' ? 'bg-background text-foreground shadow' : 'hover:bg-background/50 hover:text-foreground'"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50']) }}
>
    {{ $slot }}
</button>
