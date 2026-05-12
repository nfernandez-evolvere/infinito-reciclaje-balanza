@props(['checked' => false, 'name' => null])

<button
    type="button"
    role="switch"
    aria-checked="{{ $checked ? 'true' : 'false' }}"
    x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
    x-on:click="on = !on; $el.setAttribute('aria-checked', on)"
    {{ $attributes->merge(['class' => 'peer inline-flex h-5 w-9 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50']) }}
    :class="on ? 'bg-primary' : 'bg-input'"
>
    @if($name)
        <input type="hidden" :name="$name" :value="on ? '1' : '0'">
    @endif
    <span
        class="pointer-events-none block h-4 w-4 rounded-full bg-background shadow-lg ring-0 transition-transform"
        :class="on ? 'translate-x-4' : 'translate-x-0'"
    ></span>
</button>
