{{--
Partial: barra de acciones masivas.
Usa el scope Alpine del contenedor padre (selected, clearSelection).
--}}
<div x-show="selected.length > 0" x-cloak x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
    class="flex items-center justify-between gap-3 rounded-lg border border-border bg-muted/50 px-4 py-2.5">
    <x-ui.typography as="muted" class="text-sm flex-1">
        <span x-text="selected.length" class="font-semibold text-foreground"></span>
        miembro(s) seleccionado(s)
    </x-ui.typography>
    <div class="flex gap-2">
        <x-ui.button variant="outline" size="sm">
            <x-lucide-download class="size-4" />
            <span class="hidden md:block">Exportar</span>
        </x-ui.button>

        <x-ui.button variant="outline" size="sm">
            <x-lucide-archive class="size-4" />
            <span class="hidden md:block">Archivar</span>
        </x-ui.button>

        <x-ui.alert-dialog>
            <x-ui.alert-dialog.trigger>
                <x-ui.button variant="outline" size="sm" state="destructive">
                    <x-lucide-trash-2 class="size-4" />
                    <span class="hidden md:block">Eliminar</span>
                </x-ui.button>
            </x-ui.alert-dialog.trigger>
            <x-ui.alert-dialog.content>
                <x-ui.alert-dialog.header>
                    <x-ui.alert-dialog.title>¿Eliminar miembros seleccionados?</x-ui.alert-dialog.title>
                    <x-ui.alert-dialog.description>
                        Esta acción no se puede deshacer. Se eliminarán permanentemente
                        <span x-text="selected.length" class="font-semibold"></span>
                        miembro(s) y todos sus datos asociados.
                    </x-ui.alert-dialog.description>
                </x-ui.alert-dialog.header>
                <x-ui.alert-dialog.footer>
                    <x-ui.alert-dialog.cancel>Cancelar</x-ui.alert-dialog.cancel>
                    <x-ui.alert-dialog.action @click="clearSelection()">
                        Eliminar
                    </x-ui.alert-dialog.action>
                </x-ui.alert-dialog.footer>
            </x-ui.alert-dialog.content>
        </x-ui.alert-dialog>

        <x-ui.button variant="ghost" size="sm" @click="clearSelection()">
            <x-lucide-x class="size-4" />
        </x-ui.button>
    </div>
</div>