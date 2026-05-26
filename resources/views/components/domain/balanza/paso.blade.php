@props(['numero', 'titulo', 'completo', 'inactivo' => null, 'abierto' => 'false'])

<div
    id="paso-{{ $numero }}"
    class="rounded-xl transition-all duration-300"
    x-bind:class="{
        'opacity-40 pointer-events-none':                     {!! $inactivo ?? 'false' !!},
        'ring-1 ring-primary/30 shadow-md shadow-primary/20': !({!! $inactivo ?? 'false' !!}) && !({!! $completo !!})
    }"
>
    <x-ui.card variant="elevated">

        {{-- ── Header (siempre visible) ── --}}
        <div class="flex items-center gap-3">
            <div
                class="size-7 rounded-full grid place-items-center text-sm font-bold shrink-0 transition-all duration-300"
                x-bind:class="({!! $completo !!}) ? 'bg-success text-success-foreground' : 'bg-card border-2 border-primary text-primary'"
            >
                <x-lucide-check x-show="({!! $completo !!})" class="size-4" />
                <span x-show="!({!! $completo !!})">{{ $numero }}</span>
            </div>
            <h3 class="text-[18px] font-semibold flex-1 leading-snug">{{ $titulo }}</h3>
            <button
                x-show="({!! $completo !!})"
                x-cloak
                @click="paso{{ $numero }}Editando = !paso{{ $numero }}Editando"
                class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground hover:text-foreground transition-colors px-2 py-1 rounded-md hover:bg-muted"
            >
                <span x-show="!({!! $abierto !!})" x-cloak class="flex items-center gap-1.5">
                    <x-lucide-pencil-line class="size-3.5" />
                    Editar
                </span>
                <span x-show="({!! $abierto !!})" class="flex items-center gap-1.5">
                    <x-lucide-chevron-up class="size-3.5" />
                    Cerrar
                </span>
            </button>
        </div>

        {{-- ── Resumen compacto (completado y colapsado) ── --}}
{{--         @isset($resumen)
        <div
            x-show="({!! $completo !!}) && !({!! $abierto !!})"
            x-cloak
            x-collapse
        >
            <div class="px-4 sm:px-6 pb-4 sm:pb-5 border-t border-border/50 pt-3 sm:pt-3.5">
                {{ $resumen }}
            </div>
        </div>
        @endisset --}}

        {{-- ── Contenido completo ── --}}
        {{-- Visible cuando: no está inactivo Y (no está completo O el usuario eligió editar) --}}
        <div
            x-show="!({!! $inactivo ?? 'false' !!}) && (!({!! $completo !!}) || ({!! $abierto !!}))"
            x-collapse
        >
            <div class="pb-4 sm:pb-6 pt-4 sm:pt-5">
                {{ $slot }}
            </div>
        </div>

    </x-ui.card>
</div>
