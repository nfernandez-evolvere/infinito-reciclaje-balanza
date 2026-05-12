<div class="space-y-4 pt-4">

    <x-ui.alert state="warning">
        <x-lucide-triangle-alert class="size-4" />
        <x-ui.alert.title>Zona de peligro</x-ui.alert.title>
        <x-ui.alert.description>
            Las acciones en esta sección son permanentes e irreversibles. Procedé con precaución.
        </x-ui.alert.description>
    </x-ui.alert>

    {{-- Exportar datos --}}
    <x-ui.card>
        <x-ui.card.content class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <x-ui.typography as="small" element="p">Exportar mis datos</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs max-w-prose">
                        Descargá una copia de todos tus datos en formato JSON. El proceso puede tardar hasta 24 h.
                    </x-ui.typography>
                </div>
                <x-ui.button
                    variant="outline"
                    size="sm"
                    class="shrink-0"
                    @click="$store.toast.info('Exportación iniciada. Recibirás un email cuando esté lista.')"
                >
                    <x-lucide-download class="size-4" />
                    Exportar
                </x-ui.button>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Eliminar cuenta --}}
    <x-ui.card class="border-destructive-border">
        <x-ui.card.header>
            <x-ui.card.title class="text-destructive">Eliminar cuenta</x-ui.card.title>
            <x-ui.card.description>
                Eliminá permanentemente tu cuenta y todos los datos asociados. Esta acción no se puede deshacer.
            </x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content>
            <x-ui.typography as="muted" class="text-sm max-w-prose">
                Al eliminar tu cuenta se borrarán todos tus proyectos, datos, configuraciones e integraciones. Los miembros de tu equipo perderán el acceso.
            </x-ui.typography>
        </x-ui.card.content>
        <x-ui.card.footer>
            <x-ui.alert-dialog>
                <x-ui.alert-dialog.trigger>
                    <x-ui.button state="destructive">
                        <x-lucide-trash-2 class="size-4" />
                        Eliminar mi cuenta
                    </x-ui.button>
                </x-ui.alert-dialog.trigger>
                <x-ui.alert-dialog.content>
                    <x-ui.alert-dialog.header>
                        <x-ui.alert-dialog.title>¿Eliminar tu cuenta?</x-ui.alert-dialog.title>
                        <x-ui.alert-dialog.description>
                            Esta acción es permanente. Se eliminarán todos tus datos, proyectos y configuraciones.
                            No podrás recuperar tu cuenta una vez confirmado.
                        </x-ui.alert-dialog.description>
                    </x-ui.alert-dialog.header>
                    <x-ui.alert-dialog.footer>
                        <x-ui.alert-dialog.cancel>Cancelar</x-ui.alert-dialog.cancel>
                        <x-ui.alert-dialog.action>
                            Sí, eliminar mi cuenta
                        </x-ui.alert-dialog.action>
                    </x-ui.alert-dialog.footer>
                </x-ui.alert-dialog.content>
            </x-ui.alert-dialog>
        </x-ui.card.footer>
    </x-ui.card>

</div>
