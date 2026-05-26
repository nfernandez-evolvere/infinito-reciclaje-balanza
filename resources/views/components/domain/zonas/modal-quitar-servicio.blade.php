<div x-data="{ get open() { return quitarOpen }, set open(v) { quitarOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Quitar servicio asignado</x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Querés quitar
                <strong x-text="quitarServicioNombre" class="text-foreground font-medium"></strong>
                de la zona <strong x-text="quitarZonaNombre" class="text-foreground font-medium"></strong>?
                <span class="block mt-1">
                    El servicio deja de aparecer como opción en nuevos pesajes para esta zona.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button state="destructive" @click="executeQuitarServicio(); open = false">
                <x-lucide-trash-2 class="size-4" />
                Quitar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
