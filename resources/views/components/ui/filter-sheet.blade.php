@props([
    'action',
    'resetUrl',
    'side' => 'right',       // 'right' (desktop, controlled) | 'bottom' (mobile, self-contained)
    'controlledBy' => null,  // nombre de la variable Alpine del padre que controla open/close
])

@php
$isControlled = !is_null($controlledBy);
$showExpr  = $controlledBy ?? 'open';
$closeExpr = $isControlled ? "$controlledBy = false" : 'open = false';
$isBottom  = $side === 'bottom';

$panelClass = $isBottom
    ? 'inset-x-0 bottom-0 max-h-[80vh] w-full rounded-t-2xl border-t'
    : 'inset-y-0 right-0 w-80 rounded-l-xl border-l';

$enterStart = $isBottom ? 'opacity-0 translate-y-4' : 'opacity-0 translate-x-4';
@endphp

@unless($isControlled)
<div x-data="{ open: false }">
    @isset($trigger)
        <div @click="open = true" class="contents">{{ $trigger }}</div>
    @endisset
@endunless

<template x-teleport="body">
    <div
        x-show="{{ $showExpr }}"
        @keydown.escape.window="{{ $closeExpr }}"
        class="fixed inset-0 z-(--z-modal)"
        x-cloak
    >
        {{-- Overlay --}}
        <div
            x-show="{{ $showExpr }}"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="{{ $closeExpr }}"
            class="absolute inset-0 bg-black/50"
        ></div>

        {{-- Panel --}}
        <div
            x-show="{{ $showExpr }}"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 {{ $enterStart }}"
            x-transition:enter-end="opacity-100 translate-x-0 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 translate-y-0"
            x-transition:leave-end="opacity-0 {{ $enterStart }}"
            class="absolute flex flex-col border-border bg-background shadow-xl {{ $panelClass }}"
        >
            @if($isBottom)
                <button type="button" @click="{{ $closeExpr }}" aria-label="Cerrar"
                    class="flex w-full justify-center py-3 shrink-0 focus-visible:outline-none">
                    <div class="h-1.5 w-12 rounded-full bg-muted-foreground/30 hover:bg-muted-foreground/60 transition-colors"></div>
                </button>
            @endif

            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-border px-5 py-4 shrink-0">
                <x-ui.typography as="h4" class="flex items-center gap-2">
                    <x-lucide-sliders-horizontal class="size-5" />
                    Filtros
                </x-ui.typography>
                <x-ui.button type="button" variant="ghost" size="icon" @click="{{ $closeExpr }}" class="size-7 -mr-1">
                    <x-lucide-x class="size-4" />
                </x-ui.button>
            </div>

            {{-- Campos + footer --}}
            <form method="GET" action="{{ $action }}" class="flex flex-col flex-1 min-h-0">
                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-4">
                    {{ $slot }}
                </div>
                <div class="border-t border-border px-5 py-4 flex gap-2 shrink-0">
                    <a href="{{ $resetUrl }}" class="flex-1">
                        <x-ui.button type="button" variant="secondary" class="w-full">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    </a>
                    <x-ui.button type="submit" class="flex-1">
                        <x-lucide-search class="size-4" />
                        Aplicar
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</template>

@unless($isControlled)
</div>
@endunless
