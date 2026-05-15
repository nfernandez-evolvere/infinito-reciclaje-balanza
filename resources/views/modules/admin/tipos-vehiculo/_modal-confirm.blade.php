<div x-data="{ get open() { return confirmOpen }, set open(v) { confirmOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title
                x-text="confirmActivo ? 'Desactivar tipo de vehículo' : 'Activar tipo de vehículo'"
            ></x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás que querés
                <span x-text="confirmActivo ? 'desactivar' : 'activar'"></span>
                el tipo <strong x-text="confirmNombre" class="text-foreground font-medium"></strong>?
                <span x-show="confirmActivo" class="block mt-1">
                    Este tipo no estará disponible para nuevos pesajes.
                </span>
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
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
                state="success"
                @click="executeToggle(); open = false"
            >
                <x-lucide-circle-check class="size-4" />
                Activar
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
