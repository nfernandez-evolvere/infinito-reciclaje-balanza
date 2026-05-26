<div
    x-show="mobileResumenAbierto"
    x-cloak
    class="fixed inset-0 z-50 lg:hidden"
    @keydown.escape.window="mobileResumenAbierto = false"
>
    {{-- Backdrop --}}
    <div
        class="absolute inset-0 bg-black/50 backdrop-blur-sm"
        @click="mobileResumenAbierto = false"
        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>
    {{-- Panel --}}
    <div
        class="absolute inset-x-0 bottom-0 bg-background rounded-t-2xl shadow-2xl max-h-[85vh] overflow-y-auto"
        x-transition:enter="transition-transform duration-300 ease-out"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition-transform duration-200 ease-in"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
    >
        <div class="px-4 pt-3 pb-6">
            <div class="w-10 h-1 bg-border rounded-full mx-auto mb-4"></div>
            <x-domain.balanza.resumen-card />
        </div>
    </div>
</div>
