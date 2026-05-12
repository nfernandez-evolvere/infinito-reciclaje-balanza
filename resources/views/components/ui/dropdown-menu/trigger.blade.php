{{-- inline-block en lugar de contents: getBoundingClientRect() necesita una caja real --}}
<div
    x-ref="anchor"
    @click="toggle()"
    @keydown.enter.prevent="toggle()"
    @keydown.space.prevent="toggle()"
    aria-haspopup="menu"
    :aria-expanded="open.toString()"
    :data-state="open ? 'open' : 'closed'"
    {{ $attributes->merge(['class' => 'inline-block']) }}
>
    {{ $slot }}
</div>
