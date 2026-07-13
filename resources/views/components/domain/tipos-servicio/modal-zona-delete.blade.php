<div x-data="{ get open() { return zonaDeleteOpen }, set open(v) { zonaDeleteOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Eliminar zona</x-ui.dialog.title>
            <x-ui.dialog.description>
                Al eliminar
                <strong x-text="zonaDeleteNombre" class="text-foreground font-medium"></strong>
                se pierden sus turnos y horarios. Los pesajes ya registrados no se ven afectados.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button state="destructive" @click="executeDeleteZona(); open = false">
                <x-lucide-trash-2 class="size-4" />
                Eliminar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
