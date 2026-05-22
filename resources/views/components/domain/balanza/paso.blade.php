@props(['numero', 'titulo', 'completo', 'inactivo' => null])

<div
    class="bg-card border border-border rounded-lg p-4 sm:p-6 shadow-sm transition-opacity duration-150"
    @if($inactivo !== null)
        x-bind:class="({!! $inactivo !!}) ? 'opacity-45 pointer-events-none' : ''"
    @endif
>
    <div class="flex items-center gap-3 mb-3 sm:mb-4">
        <div
            class="size-7 rounded-full grid place-items-center text-sm font-bold shrink-0 transition-all duration-150"
            x-bind:class="({!! $completo !!}) ? 'bg-success text-success-foreground' : 'bg-card border-2 border-primary text-primary'"
        >
            <x-lucide-check x-show="({!! $completo !!})" class="size-4" />
            <span x-show="!({!! $completo !!})">{{ $numero }}</span>
        </div>
        <h3 class="text-[18px] font-semibold">{{ $titulo }}</h3>
    </div>

    {{ $slot }}
</div>
