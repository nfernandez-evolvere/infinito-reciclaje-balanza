<x-ui.dialog @modal-egreso-open.window="open = true">
    <x-ui.dialog.content>
        <x-ui.dialog.header>
            <x-ui.dialog.title>Marcar egreso</x-ui.dialog.title>
            <p class="text-sm text-muted-foreground" x-text="'Vehículo: ' + egresoPatente"></p>
        </x-ui.dialog.header>
        <form id="form-egreso" :action="'/pesajes/' + egresoId + '/egreso'" method="POST" class="px-6 space-y-4 pb-2">
            @csrf
            <div class="space-y-2">
                <x-ui.label>Hora de egreso</x-ui.label>
                <div class="text-sm font-semibold" x-text="horaActual"></div>
            </div>
            <div class="space-y-2">
                <x-ui.label for="bruto_salida_kg">Peso bruto de salida (opcional)</x-ui.label>
                <x-ui.input id="bruto_salida_kg" name="bruto_salida_kg" type="number" min="1" inputmode="numeric" placeholder="—" />
            </div>
        </form>
        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" @click="open = false">Cancelar</x-ui.button>
            <x-ui.button type="submit" form="form-egreso">Registrar egreso</x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</x-ui.dialog>
