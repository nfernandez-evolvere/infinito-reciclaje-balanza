<x-ui.dialog @modal-log-open.window="open = true">
    <x-ui.dialog.content>
        <x-ui.dialog.header>
            <x-ui.dialog.title>Historial de cambios</x-ui.dialog.title>
            <p class="text-sm text-muted-foreground" x-text="'Vehículo: ' + logPatente"></p>
        </x-ui.dialog.header>
        <div class="px-6 pb-4">
            <div x-show="logCargando" class="py-8 flex justify-center">
                <x-ui.skeleton class="h-4 w-48" />
            </div>
            <div x-show="!logCargando && logEntradas.length === 0" x-cloak class="py-8 text-center text-sm text-muted-foreground">
                Sin cambios registrados.
            </div>
            <div x-show="!logCargando && logEntradas.length > 0" class="space-y-3 max-h-96 overflow-y-auto pr-1 [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar-thumb]:bg-border [&::-webkit-scrollbar-thumb]:rounded-full">
                <template x-for="(grupo, gi) in logEntradas" :key="gi">
                    <div class="rounded-md border border-primary/20 text-sm overflow-hidden">
                        <div class="flex items-center justify-between px-3 py-2 bg-primary/8 border-b border-primary/20">
                            <div class="flex items-center gap-2 min-w-0">
                                <x-lucide-pencil-line class="size-3.5 shrink-0 text-primary" />
                                <span class="text-xs font-medium text-primary truncate" x-text="grupo.usuario"></span>
                            </div>
                            <span class="text-xs text-primary/70 shrink-0 ml-2" x-text="grupo.fecha"></span>
                        </div>
                        <div class="divide-y divide-border/60">
                            <template x-for="(cambio, ci) in grupo.cambios" :key="ci">
                                <div class="px-3 py-2.5">
                                    <div class="text-xs font-semibold text-muted-foreground uppercase tracking-wide mb-1.5" x-text="cambio.campo"></div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-muted-foreground line-through" x-text="cambio.anterior || '—'"></span>
                                        <x-lucide-arrow-right class="size-3.5 shrink-0 text-primary" />
                                        <span class="text-sm font-semibold text-foreground" x-text="cambio.nuevo || '—'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="px-3 py-2 bg-primary/5 border-t border-primary/20 text-xs text-muted-foreground">
                            <span class="font-medium text-foreground">Motivo:</span> <span x-text="grupo.motivo"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </x-ui.dialog.content>
</x-ui.dialog>
