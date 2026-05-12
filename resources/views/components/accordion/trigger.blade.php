@props([])

<button
    type="button"
    @click="active === value ? active = null : active = value"
    {{ $attributes->merge(['class' => 'flex flex-1 w-full items-center justify-between py-4 text-sm font-medium transition-all hover:underline text-left [&[aria-expanded=true]>svg]:rotate-180']) }}
    :aria-expanded="active === value"
>
    {{ $slot }}
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 transition-transform duration-200">
        <path d="m6 9 6 6 6-6"/>
    </svg>
</button>
