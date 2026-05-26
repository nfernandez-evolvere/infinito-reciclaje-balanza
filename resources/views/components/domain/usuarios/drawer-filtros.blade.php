@props(['filters'])

<template x-teleport="body">
    <div
        x-show="filterOpen"
        @keydown.escape.window="filterOpen = false"
        class="fixed inset-0 z-(--z-modal)"
        x-cloak
    >
        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="filterOpen = false"
            class="absolute inset-0 bg-black/50"
        ></div>

        <div
            x-show="filterOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-4"
            class="absolute inset-y-0 right-0 flex w-80 flex-col rounded-l-xl border-l border-border bg-background shadow-xl"
        >
            <div class="flex items-center justify-between border-b border-border px-5 py-4">
                <x-ui.typography as="h4" class="flex items-center gap-2">
                    <x-lucide-sliders-horizontal class="size-5" />
                    Filtros
                </x-ui.typography>
                <x-ui.button type="button" variant="ghost" size="icon" @click="filterOpen = false" class="size-7 -mr-1">
                    <x-lucide-x class="size-4" />
                </x-ui.button>
            </div>

            <form method="GET" action="{{ route('admin.usuarios.index') }}"
                class="flex flex-col flex-1 min-h-0">

                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-2">

                    <x-ui.form-field for="filter-buscar">
                        <x-ui.label for="filter-buscar">Nombre o correo</x-ui.label>
                        <x-ui.input
                            id="filter-buscar"
                            name="buscar"
                            type="search"
                            placeholder="Buscar usuario…"
                            :value="$filters['buscar'] ?? ''"
                            autofocus
                        />
                    </x-ui.form-field>

                    <x-ui.form-field for="filter-role">
                        <x-ui.label for="filter-role">Rol</x-ui.label>
                        <x-ui.select name="role" :value="$filters['role'] ?? ''">
                            <x-ui.select.trigger id="filter-role">
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                <x-ui.select.item value="operador">Operador</x-ui.select.item>
                                <x-ui.select.item value="admin">Admin</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.form-field>

                    <x-ui.form-field for="filter-activo">
                        <x-ui.label for="filter-activo">Estado</x-ui.label>
                        <x-ui.select name="activo" :value="$filters['activo'] ?? ''">
                            <x-ui.select.trigger id="filter-activo">
                                <x-ui.select.value placeholder="Todos" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Todos</x-ui.select.item>
                                <x-ui.select.item value="1">Activo</x-ui.select.item>
                                <x-ui.select.item value="0">Inactivo</x-ui.select.item>
                            </x-ui.select.content>
                        </x-ui.select>
                    </x-ui.form-field>

                </div>

                <div class="border-t border-border px-5 py-4 flex gap-2">
                    <a href="{{ route('admin.usuarios.index') }}" class="flex-1">
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
