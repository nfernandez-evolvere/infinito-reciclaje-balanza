<div x-data="{ get open() { return zonaConfirmOpen }, set open(v) { zonaConfirmOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title
                x-text="zonaConfirmActivo ? 'Desactivar zona' : 'Activar zona'"
            ></x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés
                <span x-text="zonaConfirmActivo ? 'desactivar' : 'activar'"></span>
                la zona <strong x-text="zonaConfirmNombre" class="text-foreground font-medium"></strong>?
                <span x-show="zonaConfirmActivo" class="block mt-1">
                    No aparecerá en el formulario de pesaje. Los pesajes históricos no se ven afectados.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" x-show="zonaConfirmActivo" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="button" variant="ghost" x-show="!zonaConfirmActivo" x-cloak @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button
                x-show="zonaConfirmActivo"
                state="destructive"
                @click="executeToggleZona(); open = false"
            >
                <x-lucide-ban class="size-4" />
                Desactivar
            </x-ui.button>
            <x-ui.button
                x-show="!zonaConfirmActivo"
                state="success"
                @click="executeToggleZona(); open = false"
            >
                <x-lucide-circle-check class="size-4" />
                Activar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
