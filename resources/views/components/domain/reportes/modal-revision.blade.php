{{--
    Dialog de revisión de un reporte pendiente: metadata + preview de los
    archivos (desde el snapshot congelado), edición de la narrativa IA,
    aprobación del envío o descarte. Vive en el ámbito Alpine reportesHistorial.
--}}
<div x-data="{ get open() { return revisionOpen }, set open(v) { revisionOpen = v } }">
    <x-ui.dialog.content size="lg">
        <x-ui.dialog.header>
            <x-ui.dialog.title>Revisar reporte</x-ui.dialog.title>
            <x-ui.dialog.description>
                El envío está pausado: revisá el contenido y aprobalo o descartalo.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <div class="flex-1 overflow-y-auto px-6 pb-2 space-y-5">

            {{-- Metadata --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-sm">
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <x-lucide-file-bar-chart class="size-3.5 shrink-0 text-primary" />
                    <span>Tipo: <span class="text-foreground font-medium" x-text="revision.tipo"></span></span>
                </div>
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <x-lucide-calendar-range class="size-3.5 shrink-0 text-primary" />
                    <span>Período: <span class="text-foreground font-medium" x-text="revision.periodo"></span></span>
                </div>
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <x-lucide-clock class="size-3.5 shrink-0 text-primary" />
                    <span>Generado: <span class="text-foreground font-medium" x-text="revision.generado"></span></span>
                </div>
                <div class="flex items-center gap-1.5 text-muted-foreground">
                    <x-lucide-user class="size-3.5 shrink-0 text-primary" />
                    <span>Origen: <span class="text-foreground font-medium" x-text="revision.autor"></span></span>
                </div>
            </div>

            {{-- Destinatarios --}}
            <div class="space-y-1.5">
                <p class="text-label">Se enviará a</p>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="email in revision.destinatarios" :key="email">
                        <x-ui.badge variant="outline"><span x-text="email"></span></x-ui.badge>
                    </template>
                </div>
            </div>

            {{-- Preview de archivos (desde el snapshot: idénticos a lo que se enviará) --}}
            <div class="space-y-1.5">
                <p class="text-label">Contenido</p>
                <div class="flex flex-wrap gap-2">
                    <template x-if="revision.urls.pdf">
                        <x-ui.button as="a" variant="outline" size="sm" x-bind:href="revision.urls.pdf" target="_blank">
                            <x-lucide-file-text class="size-4" />
                            Ver PDF
                        </x-ui.button>
                    </template>
                    <template x-if="revision.urls.excel">
                        <x-ui.button as="a" variant="outline" size="sm" x-bind:href="revision.urls.excel" target="_blank">
                            <x-lucide-file-spreadsheet class="size-4" />
                            Ver Excel
                        </x-ui.button>
                    </template>
                </div>
                <p class="text-caption">Los archivos se generan desde los datos congelados: lo que veas acá es exactamente lo que se enviará.</p>
            </div>

            {{-- Narrativa IA editable (solo informes) --}}
            <form
                x-show="revision.esInforme"
                x-cloak
                method="POST"
                x-bind:action="revision.urls.conclusiones"
                class="space-y-1.5"
            >
                @csrf
                @method('PUT')
                <x-ui.label for="rev-conclusiones">Análisis del informe</x-ui.label>
                <x-ui.textarea
                    id="rev-conclusiones"
                    name="conclusiones"
                    rows="8"
                    x-model="revision.conclusiones"
                    placeholder="Sin análisis generado."
                />
                <div class="flex items-center justify-between gap-3">
                    <p class="text-caption">Podés corregir el texto generado por la IA antes de aprobar. Se conserva el original como registro.</p>
                    <x-ui.button type="submit" variant="outline" size="sm" x-bind:disabled="!conclusionesDirty">
                        <x-lucide-save class="size-4" />
                        Guardar análisis
                    </x-ui.button>
                </div>
            </form>

            {{-- Descarte (sección colapsada hasta que se elige descartar) --}}
            <div x-show="descarteAbierto" x-cloak>
                <form method="POST" x-bind:action="revision.urls.descartar" class="space-y-1.5 rounded-xl border border-destructive/40 p-4">
                    @csrf
                    <x-ui.label for="rev-motivo">Motivo del descarte</x-ui.label>
                    <x-ui.input
                        id="rev-motivo"
                        name="motivo"
                        x-model="motivoDescarte"
                        maxlength="500"
                        placeholder="Opcional — queda en el historial"
                    />
                    <div class="flex justify-end pt-1">
                        <x-ui.button type="submit" state="destructive" size="sm">
                            <x-lucide-trash-2 class="size-4" />
                            Confirmar descarte
                        </x-ui.button>
                    </div>
                </form>
            </div>

        </div>

        <x-ui.dialog.footer>
            <x-ui.button type="button" variant="ghost" state="destructive" @click="descarteAbierto = !descarteAbierto">
                <x-lucide-x class="size-4" />
                Descartar
            </x-ui.button>
            <div class="flex flex-col items-end gap-1">
                <form method="POST" x-bind:action="revision.urls.aprobar">
                    @csrf
                    <x-ui.button type="submit" x-bind:disabled="conclusionesDirty">
                        <x-lucide-send class="size-4" />
                        Aprobar y enviar
                    </x-ui.button>
                </form>
                <p class="text-caption" x-show="conclusionesDirty" x-cloak>Guardá los cambios del análisis antes de aprobar.</p>
            </div>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</div>
