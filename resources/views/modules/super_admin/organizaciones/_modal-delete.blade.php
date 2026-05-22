<div x-data="{ get open() { return deleteOpen }, set open(v) { deleteOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Eliminar organización</x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Seguro que querés eliminar a
                <strong x-text="deleteNombre" class="text-foreground font-medium"></strong>?
                <span class="block mt-1">
                    Esta acción no se puede deshacer.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <form method="POST" x-bind:action="orgBaseUrl + '/' + deleteId">
            @csrf
            <input type="hidden" name="_method" value="DELETE">
            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit" state="destructive">
                    <x-lucide-trash-2 class="size-4" />
                    Eliminar
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
