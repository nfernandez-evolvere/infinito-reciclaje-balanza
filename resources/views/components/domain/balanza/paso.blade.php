@props(['numero', 'titulo', 'completo', 'inactivo' => null])

<div
    class="rounded-xl transition-all duration-200"
    x-bind:class="{
        'opacity-45 pointer-events-none':                    {!! $inactivo ?? 'false' !!},
        'ring-1 ring-primary/30 shadow-md shadow-primary/20': !({!! $inactivo ?? 'false' !!}) && !({!! $completo !!})
    }"
>
    <x-ui.card class="p-4 sm:p-6" variant="elevated">
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
    </x-ui.card>
</div>
