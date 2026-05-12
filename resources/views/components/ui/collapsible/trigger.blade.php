<button
    type="button"
    :aria-expanded="open.toString()"
    :data-state="open ? 'open' : 'closed'"
    :disabled="disabled"
    @click="toggle()"
    {{ $attributes->twMerge('cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50') }}
>
    {{ $slot }}
</button>
