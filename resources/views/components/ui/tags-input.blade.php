@props([
    'name'        => '',
    'value'       => '',
    'fetchUrl'    => null,
    'placeholder' => 'Agregar...',
])

<div
    x-data="tagsInput({ name: '{{ $name }}', fetchUrl: {{ $fetchUrl ? "'$fetchUrl'" : 'null' }}, value: '{{ $value }}' })"
    @click.outside="open = false"
    class="relative"
>
    {{-- Hidden input para el form submit --}}
    <input type="hidden" :name="name" x-bind:value="serialized">

    {{-- Contenedor chips + input — mismas clases que x-ui.input --}}
    <div
        class="flex flex-wrap gap-1.5 min-h-10 w-full rounded-xl border border-input bg-background text-foreground shadow-xs px-3 py-1.5 cursor-text transition-colors focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-ring"
        @click="$refs.tagsInput.focus()"
    >
        <template x-for="(tag, i) in tags" :key="tag">
            <x-ui.badge variant="secondary" class="gap-1 pr-1 font-normal">
                <span x-text="tag" class="max-w-48 truncate"></span>
                <x-ui.button
                    variant="ghost"
                    size="icon"
                    class="size-4 shrink-0 -mr-0.5"
                    @click.stop="remove(i)"
                    aria-label="Quitar"
                >
                    <x-lucide-x class="size-3" />
                </x-ui.button>
            </x-ui.badge>
        </template>

        <x-ui.input
            x-ref="tagsInput"
            x-model="query"
            @input="search()"
            @keydown="keydown($event)"
            @focus="search()"
            type="text"
            autocomplete="off"
            x-bind:placeholder="tags.length === 0 ? '{{ $placeholder }}' : ''"
            class="flex-1 min-w-36 w-auto h-auto border-none shadow-none bg-transparent rounded-none focus-visible:ring-0 focus-visible:ring-offset-0 px-0 py-0.5"
        />
    </div>

    {{-- Dropdown — mismas clases visuales que dropdown-menu.content --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 top-full mt-1 w-full rounded-md bg-popover p-1 text-popover-foreground shadow-md ring-1 ring-foreground/10 overflow-hidden"
    >
        {{-- Cargando --}}
        <template x-if="loading">
            <div class="space-y-1 p-1">
                <x-ui.skeleton class="h-7 w-full" />
                <x-ui.skeleton class="h-7 w-3/4" />
            </div>
        </template>

        <template x-if="!loading">
            <div>
                {{-- Sugerencias existentes --}}
                <template x-for="s in suggestions" :key="s.email ?? s">
                    <x-ui.dropdown-menu.item :close-on-click="true" @click="add(s.email ?? s)">
                        <x-lucide-mail class="size-3.5 text-muted-foreground" />
                        <div class="flex flex-col min-w-0 flex-1">
                            <span x-text="s.nombre || s.email || s" class="truncate leading-tight"></span>
                            <span x-show="s.nombre" x-text="s.email" class="text-xs text-muted-foreground truncate leading-tight"></span>
                        </div>
                        <x-lucide-check
                            x-show="tags.includes((s.email ?? s).toLowerCase())"
                            class="size-3.5 ml-auto shrink-0 text-primary"
                        />
                    </x-ui.dropdown-menu.item>
                </template>

                {{-- Separador + opción "Agregar nuevo" --}}
                <template x-if="isValid(query) && !alreadySuggested(query)">
                    <div>
                        <template x-if="suggestions.length > 0">
                            <x-ui.dropdown-menu.separator />
                        </template>
                        <x-ui.dropdown-menu.item :close-on-click="true" @click="add(query)">
                            <x-lucide-user-plus class="size-3.5 text-primary" />
                            <span class="text-muted-foreground">
                                Agregar
                                <span x-text="`&quot;${query.trim()}&quot;`" class="text-foreground font-medium"></span>
                            </span>
                        </x-ui.dropdown-menu.item>
                    </div>
                </template>

                {{-- Sin resultados --}}
                <template x-if="suggestions.length === 0 && !isValid(query)">
                    <p class="px-2 py-1.5 text-xs text-muted-foreground">
                        Escribí un email válido.
                    </p>
                </template>
            </div>
        </template>
    </div>
</div>
