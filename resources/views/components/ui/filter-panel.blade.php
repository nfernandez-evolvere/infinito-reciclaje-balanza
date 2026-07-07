@props([
    'action'        => null,       // destino GET del form (modo form)
    'resetUrl'      => null,       // url para limpiar los filtros (modo form)
    'storageKey',                  // clave localStorage única por pantalla/tab
    'hasFilters'      => false,    // hay filtros activos
    'title'           => 'Filtros',
    'submitLabel'     => 'Aplicar',
    'submittingLabel' => 'Aplicando…',
    'emptyLabel'      => 'Sin filtros aplicados',
    // Modo AJAX (opcional): si se pasan, el form no navega — corre expresiones Alpine.
    // Usado por pantallas que filtran sin recargar (ej: dashboard).
    'submitHandler'   => null,     // expresión Alpine al aplicar (con @submit.prevent)
    'clearHandler'    => null,     // expresión Alpine al limpiar (botón, no link)
    // Expresión Alpine booleana de "ocupado". En modo AJAX el submit no navega, así que
    // el `submitting` interno nunca se activa: se pasa el estado async del padre (ej:
    // 'refreshing') para mostrar spinner/disable en Aplicar y Limpiar.
    'busyExpr'        => null,
    // Clases del contenedor de campos. Por defecto: 2 columnas en tablet y una fila
    // que se reparte en desktop. Se puede sobreescribir para forzar más filas cuando
    // hay muchos campos (ej: grid fijo de columnas).
    'bodyClass'       => 'grid grid-cols-2 gap-x-4 gap-y-3 p-4 lg:flex lg:flex-wrap lg:items-end lg:*:min-w-30 lg:*:flex-1',
])

@php
    // "Ocupado": el estado externo (AJAX) o el submitting interno del form.
    $busy = $busyExpr ?? 'submitting';
@endphp

{{--
    Filtros inline para tablet/desktop (md+): una card con header siempre visible
    y cuerpo colapsable.

    Header (siempre visible):
      · Título a la izquierda (clickeable — expande/colapsa).
      · Chips de lo seleccionado (removibles) en el centro, o «Sin filtros aplicados».
      · Botones Limpiar / Aplicar + chevron a la derecha (chevron expande/colapsa).

    Cuerpo (colapsable):
      · Los campos del formulario (2 columnas en tablet · una fila en desktop).

    Estado inicial: colapsado. El abierto/cerrado se recuerda en localStorage por
    pantalla (storageKey). Los inputs, aunque estén ocultos, se envían igual al
    aplicar desde el header.

    Slots:
      · default : los campos del formulario
      · chips   : pills de filtros activos (removibles)
--}}

<div
    class="hidden md:block"
    x-data="{ open: localStorage.getItem('{{ $storageKey }}') === '1', submitting: false }"
    x-init="$watch('open', v => localStorage.setItem('{{ $storageKey }}', v ? '1' : '0'))"
>
    <form
        @if($submitHandler)
            @submit.prevent="{{ $submitHandler }}"
        @else
            method="GET" action="{{ $action }}" @submit="submitting = true"
        @endif
    >
        <x-ui.card class="gap-0 p-0 overflow-hidden">

            {{-- ── Header (siempre visible) ───────────────────────────── --}}
            <div class="flex items-center gap-3 px-4 py-2.5">

                {{-- Título — expande/colapsa --}}
                <button
                    type="button"
                    @click="open = !open"
                    class="flex shrink-0 items-center gap-2 text-sm font-semibold text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 rounded-md"
                >
                    <x-lucide-sliders-horizontal class="size-4 text-muted-foreground" />
                    {{ $title }}
                </button>

                {{-- Chips (o vacío) — alineados a la derecha, junto a las acciones --}}
                <div class="ml-auto flex min-w-0 flex-wrap items-center justify-end gap-1.5">
                    @isset($chips)
                        {{ $chips }}
                    @else
                        <span class="text-xs text-muted-foreground">{{ $emptyLabel }}</span>
                    @endisset
                </div>

                {{-- Divider: separa la selección de las acciones --}}
                <x-ui.separator orientation="vertical" class="h-5 shrink-0 self-center" />

                {{-- Acciones --}}
                <div class="flex shrink-0 items-center gap-1.5">
                    @if($clearHandler)
                        <x-ui.button type="button" variant="ghost" size="sm" @click="{{ $clearHandler }}" x-bind:disabled="{{ $busy }}">
                            <x-lucide-x class="size-4" />
                            Limpiar
                        </x-ui.button>
                    @else
                        <a
                            href="{{ $resetUrl }}"
                            @class(['pointer-events-none opacity-40' => ! $hasFilters])
                            x-bind:class="({{ $busy }}) && 'pointer-events-none opacity-50'"
                        >
                            <x-ui.button type="button" variant="ghost" size="sm">
                                <x-lucide-x class="size-4" />
                                Limpiar
                            </x-ui.button>
                        </a>
                    @endif

                    <x-ui.button type="submit" size="sm" x-bind:disabled="{{ $busy }}">
                        <x-ui.spinner size="sm" class="text-current" x-show="{{ $busy }}" x-cloak />
                        <x-lucide-search class="size-4" x-show="!({{ $busy }})" />
                        <span x-show="!({{ $busy }})">{{ $submitLabel }}</span>
                        <span x-show="{{ $busy }}" x-cloak>{{ $submittingLabel }}</span>
                    </x-ui.button>

                    <x-ui.separator orientation="vertical" class="mx-0.5 h-5 self-center" />

                    <button
                        type="button"
                        @click="open = !open"
                        aria-controls="{{ $storageKey }}-panel"
                        x-bind:aria-expanded="open.toString()"
                        class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                    >
                        <x-lucide-chevron-down
                            class="size-5 transition-transform duration-200"
                            x-bind:class="open && 'rotate-180'"
                        />
                        <span class="sr-only">Mostrar u ocultar filtros</span>
                    </button>
                </div>
            </div>

            {{-- ── Cuerpo colapsable ──────────────────────────────────── --}}
            <div id="{{ $storageKey }}-panel" x-show="open" x-collapse x-cloak>
                <x-ui.separator />
                <div class="{{ $bodyClass }}">
                    {{ $slot }}
                </div>
            </div>

        </x-ui.card>
    </form>
</div>
