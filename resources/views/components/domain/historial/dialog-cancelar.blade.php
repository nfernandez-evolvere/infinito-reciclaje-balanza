<x-ui.dialog @modal-cancelar-open.window="open = true">
    <x-ui.dialog.content size="sm" closeState="'destructive'">
        <x-ui.dialog.header>
            <x-ui.dialog.title x-text="'Cancelar pesaje de ' + cancelarPatente"></x-ui.dialog.title>
            <x-ui.dialog.description>
                Esta acción no se puede deshacer. El pesaje quedará registrado como cancelado en el historial.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <form id="form-cancelar" :action="'/pesajes/' + cancelarId + '/cancelar'" method="POST" class="px-6 space-y-2 pb-2">
            @csrf
            @method('PATCH')
            <input type="hidden" name="origen" value="{{ request()->route()?->getName() }}">
            <x-ui.label for="motivo-cancelar">Motivo</x-ui.label>
            <x-ui.textarea
                id="motivo-cancelar"
                name="motivo"
                x-model="motivoCancelacion"
                placeholder="Describí por qué se cancela este pesaje…"
                rows="3"
                required
                state="destructive"
            />
        </form>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="open = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button
                type="submit"
                form="form-cancelar"
                state="destructive"
                x-bind:disabled="motivoCancelacion.trim().length < 5"
            >
                <x-lucide-ban class="size-4" />
                Confirmar cancelación
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</x-ui.dialog>
