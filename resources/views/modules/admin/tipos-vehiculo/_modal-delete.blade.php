<div x-data="{ get open() { return deleteOpen }, set open(v) { deleteOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Eliminar tipo de vehículo</x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés eliminar el tipo
                <strong x-text="deleteNombre" class="text-foreground font-medium"></strong>?
                Esta acción no se puede deshacer.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button state="destructive" @click="executeDelete(); open = false">
                <x-lucide-trash-2 class="size-4" />
                Eliminar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
