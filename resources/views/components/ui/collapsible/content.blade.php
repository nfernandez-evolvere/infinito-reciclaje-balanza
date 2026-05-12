{{-- Animación via CSS Grid trick (igual que accordion) --}}
<div
    x-cloak
    :data-state="open ? 'open' : 'closed'"
    class="grid transition-[grid-template-rows] duration-300 ease-[cubic-bezier(0.87,0,0.13,1)] overflow-hidden"
    :style="`grid-template-rows: ${open ? '1fr' : '0fr'}`"
>
    <div class="overflow-hidden">
        <div {{ $attributes->twMerge('') }}>
            {{ $slot }}
        </div>
    </div>
</div>
