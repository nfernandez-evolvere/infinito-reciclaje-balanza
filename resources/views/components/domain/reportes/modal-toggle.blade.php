@props([])

<div x-data="{ get open() { return toggleOpen }, set open(v) { toggleOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title
                x-text="toggleActivo ? 'Desactivar programado' : 'Activar programado'"
            ></x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés
                <span x-text="toggleActivo ? 'desactivar' : 'activar'"></span>
                <strong x-text="toggleNombre" class="text-foreground font-medium"></strong>?
                <span x-show="toggleActivo" class="block mt-1">
                    Este reporte no se enviará hasta que lo reactives.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" x-show="toggleActivo" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="button" variant="ghost" x-show="!toggleActivo" x-cloak @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button
                x-show="toggleActivo"
                state="destructive"
                @click="executeToggle(); open = false"
            >
                <x-lucide-ban class="size-4" />
                Desactivar
            </x-ui.button>
            <x-ui.button
                x-show="!toggleActivo"
                x-cloak
                state="success"
                @click="executeToggle(); open = false"
            >
                <x-lucide-circle-check class="size-4" />
                Activar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
