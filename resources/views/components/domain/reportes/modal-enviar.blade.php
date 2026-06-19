<div x-data="{ get open() { return enviarOpen }, set open(v) { enviarOpen = v } }">
    <x-ui.dialog.content size="sm">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Enviar reporte ahora</x-ui.dialog.title>
            <x-ui.dialog.description>
                ¿Confirmás el envío inmediato de
                <strong x-text="enviarNombre" class="text-foreground font-medium"></strong>?
                Se enviará a todos los destinatarios configurados.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="outline" @click="open = false" x-bind:disabled="enviando">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button @click="executeEnviar()" x-bind:disabled="enviando">
                <x-lucide-send class="size-4" />
                <span x-text="enviando ? 'Enviando…' : 'Enviar'"></span>
            </x-ui.button>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
