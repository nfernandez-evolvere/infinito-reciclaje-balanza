@props([
    'action',                 // destino GET del form
    'resetUrl',               // url para limpiar los filtros
    'storageKey',             // clave localStorage única por pantalla/tab
    'hasFilters' => false,    // hay filtros activos
])

{{--
    Filtros inline para tablet/desktop (md+): un chevron colapsa/muestra el panel.
    - Cerrado: solo la lista de chips de lo seleccionado (removibles).
    - Abierto: la card con los campos (2 columnas en tablet · una fila en desktop)
      y los botones Aplicar/Limpiar en una línea aparte (sin footer con borde).
    El estado abierto/cerrado se recuerda en localStorage.

    Slots:
      - default : los campos del formulario
      - chips   : pills de filtros activos (removibles)
--}}

<div
    class="hidden md:block"
    x-data="{ open: localStorage.getItem('{{ $storageKey }}') === '1', submitting: false }"
    x-init="$watch('open', v => localStorage.setItem('{{ $storageKey }}', v ? '1' : '0'))"
>
    <form method="GET" action="{{ $action }}" @submit="submitting = true">
        {{-- Barra: chevron + chips (cerrado) --}}
        <div class="flex items-center gap-2">
            <button
                type="button"
                @click="open = !open"
                aria-controls="{{ $storageKey }}-panel"
                x-bind:aria-expanded="open.toString()"
                class="inline-flex size-9 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            >
                <x-lucide-chevron-down
                    class="size-5 transition-transform duration-200"
                    x-bind:class="open && 'rotate-180'"
                />
                <span class="sr-only">Mostrar u ocultar filtros</span>
            </button>

            @isset($chips)
                <div x-show="!open" x-cloak class="flex items-center gap-1.5 flex-wrap min-w-0">
                    {{ $chips }}
                </div>
            @endisset
        </div>

        {{-- Card con los campos + botones en línea aparte (sin footer) --}}
        <div id="{{ $storageKey }}-panel" x-show="open" x-collapse x-cloak>
            <x-ui.card class="mt-2">
                <div class="grid grid-cols-2 gap-x-4 gap-y-3 lg:flex lg:flex-wrap lg:items-end lg:*:flex-1 lg:*:min-w-30">
                    {{ $slot }}
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ $resetUrl }}" x-bind:class="submitting && 'pointer-events-none opacity-50'">
                        <x-ui.button type="button" variant="ghost">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    </a>
                    <x-ui.button type="submit" x-bind:disabled="submitting">
                        <x-ui.spinner size="sm" class="text-current" x-show="submitting" x-cloak />
                        <x-lucide-search class="size-4" x-show="!submitting" />
                        <span x-text="submitting ? 'Aplicando…' : 'Aplicar'"></span>
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </form>
</div>
