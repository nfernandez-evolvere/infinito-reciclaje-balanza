<div x-data="{ get open() { return confirmOpen }, set open(v) { confirmOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title
                x-text="confirmActivo ? 'Desactivar usuario' : 'Activar usuario'"
            ></x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés
                <span x-text="confirmActivo ? 'desactivar' : 'activar'"></span>
                a <strong x-text="confirmNombre" class="text-foreground font-medium"></strong>?
                <span x-show="confirmActivo" class="block mt-1">
                    Este usuario no podrá iniciar sesión hasta que sea reactivado.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" x-show="confirmActivo" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="button" variant="ghost" x-show="!confirmActivo" x-cloak @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button
                x-show="confirmActivo"
                state="destructive"
                @click="executeToggle(); open = false"
            >
                <x-lucide-ban class="size-4" />
                Desactivar
            </x-ui.button>
            <x-ui.button
                x-show="!confirmActivo"
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
