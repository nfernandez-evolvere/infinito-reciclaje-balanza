@props([])

<div
    x-data="{ get open() { return zonaModalOpen }, set open(v) { zonaModalOpen = v } }"
    x-init="$watch('zonaModalOpen', v => { if (v) syncMapToForm() }); if (zonaModalOpen) syncMapToForm()"
>
    <x-ui.dialog.content size="xl">
        <form
            class="contents"
            method="POST"
            :action="zonaModalMode === 'create'
                ? '{{ route('admin.zonas.store') }}'
                : '{{ url('admin/zonas') }}/' + zonaForm.id"
        >
            @csrf
            <input type="hidden" name="_method"          :value="zonaModalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_form"            value="zona" />
            <input type="hidden" name="_mode"            :value="zonaModalMode" />
            <input type="hidden" name="_editing_id"      :value="zonaForm.id" />
            <input type="hidden" name="tipo_servicio_id" :value="zonaForm.tipo_servicio_id" />
            <input type="hidden" name="geojson"          :value="zonaForm.geojson" />
            <input type="hidden" name="centro_lat"       :value="zonaForm.centro_lat" />
            <input type="hidden" name="centro_lng"       :value="zonaForm.centro_lng" />

            <template x-for="turno in zonaForm.turnos" :key="turno">
                <input type="hidden" name="turnos[]" :value="turno" />
            </template>

            {{--
                x-for exige un único elemento raíz por iteración — envolver los dos
                inputs en un <span> (antes eran hermanos sueltos y Alpine solo clonaba
                el primero, "inicio", descartando "fin" en silencio sin error visible).
            --}}
            <template x-for="(franjas, diaIdx) in zonaForm.horariosPorDia" :key="diaIdx">
                <template x-for="(franja, franjaIdx) in franjas" :key="franjaIdx">
                    <span>
                        <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][inicio]`" :value="franja.inicio" />
                        <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][fin]`" :value="franja.fin" />
                    </span>
                </template>
            </template>

            <x-ui.dialog.header class="gap-0">
                {{-- Servicio (padre): tile + etiqueta + nombre, presentado como el contenedor de la zona --}}
                <div class="flex items-start gap-3">
                    <div class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-lucide-layers class="size-5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-overline">Servicio</p>
                        <p class="truncate text-lg font-semibold leading-tight text-foreground" x-text="selectedServicioNombre"></p>
                    </div>
                </div>
                {{-- Zona (hijo): la acción queda anidada bajo el servicio --}}
                <div class="mt-2.5 flex items-center gap-2 pl-13">
                    <x-lucide-corner-down-right class="size-4 shrink-0 text-muted-foreground" />
                    <x-ui.dialog.title class="text-sm font-medium text-foreground">
                        <span x-text="zonaModalMode === 'create' ? 'Nueva zona' : 'Editar zona'"></span>
                    </x-ui.dialog.title>
                </div>
            </x-ui.dialog.header>

            <div class="px-6 pb-2 overflow-y-auto flex-1 min-h-0 space-y-4">
                <x-ui.form-field
                    for="zona-nombre"
                    :state="old('_form') === 'zona' && $errors->has('nombre') ? 'destructive' : null"
                    :message="old('_form') === 'zona' ? $errors->first('nombre') : null"
                >
                    <x-ui.label for="zona-nombre">Nombre</x-ui.label>
                    <x-ui.input
                        id="zona-nombre"
                        name="nombre"
                        x-model="zonaForm.nombre"
                        placeholder="Ej: Zona Norte"
                        autofocus
                    />
                </x-ui.form-field>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4">
                    <x-ui.form-field
                        for="zona-hectareas"
                        :state="old('_form') === 'zona' && $errors->has('hectareas') ? 'destructive' : null"
                        :message="old('_form') === 'zona' ? $errors->first('hectareas') : null"
                    >
                        <x-ui.label for="zona-hectareas">Hectáreas</x-ui.label>
                        <x-ui.input id="zona-hectareas" name="hectareas" type="number" step="0.01" min="0" x-model="zonaForm.hectareas" placeholder="0" />
                    </x-ui.form-field>
                    <x-ui.form-field
                        for="zona-barrios"
                        :state="old('_form') === 'zona' && $errors->has('barrios') ? 'destructive' : null"
                        :message="old('_form') === 'zona' ? $errors->first('barrios') : null"
                    >
                        <x-ui.label for="zona-barrios">Barrios</x-ui.label>
                        <x-ui.input id="zona-barrios" name="barrios" type="number" min="0" x-model="zonaForm.barrios" placeholder="0" />
                    </x-ui.form-field>
                    <x-ui.form-field
                        for="zona-habitantes"
                        :state="old('_form') === 'zona' && $errors->has('habitantes') ? 'destructive' : null"
                        :message="old('_form') === 'zona' ? $errors->first('habitantes') : null"
                    >
                        <x-ui.label for="zona-habitantes">Habitantes <span class="text-muted-foreground font-normal">(opcional)</span></x-ui.label>
                        <x-ui.input id="zona-habitantes" name="habitantes" type="number" min="0" x-model="zonaForm.habitantes" placeholder="0" />
                    </x-ui.form-field>
                </div>

                {{-- Turnos --}}
                <x-ui.collapsible class="rounded-md border border-border" x-model="turnosSeccionAbierta">
                    <x-ui.collapsible.trigger class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left">
                        <span class="text-sm font-medium">Turnos</span>
                        <span class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground" x-text="resumenTurnos()"></span>
                            <x-lucide-chevron-down class="size-4 text-muted-foreground transition-transform" x-bind:class="open && 'rotate-180'" />
                        </span>
                    </x-ui.collapsible.trigger>
                    <x-ui.collapsible.content class="px-3 pb-3 space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="zonaForm.turnosEnabled.toString()"
                                :class="zonaForm.turnosEnabled ? 'bg-primary' : 'bg-input'"
                                @click="zonaForm.turnosEnabled = !zonaForm.turnosEnabled; if (!zonaForm.turnosEnabled) zonaForm.turnos = []"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <span
                                    class="pointer-events-none inline-block size-5 rounded-full bg-background shadow-sm ring-0 transition-transform"
                                    :class="zonaForm.turnosEnabled ? 'translate-x-5' : 'translate-x-0'"
                                ></span>
                            </button>
                            <span class="text-sm font-medium">Opera con turnos</span>
                        </label>

                        <div x-show="zonaForm.turnosEnabled" x-cloak class="space-y-2">
                            {{--
                                Turnos de texto libre por zona: no hay catálogo — se escribe el
                                nombre y Enter (o coma) lo agrega como chip, o se agrega con un
                                clic desde las sugerencias. Editar un turno es sacarlo y volver a
                                escribirlo; los pesajes ya cargados con el nombre anterior quedan
                                igual (es un dato copiado, no una FK).
                            --}}
                            <div class="flex flex-wrap items-center gap-1.5 rounded-md border border-input bg-background px-2 py-1.5 min-h-9 focus-within:ring-2 focus-within:ring-ring">
                                <template x-for="(turno, i) in zonaForm.turnos" :key="turno + i">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-primary/10 text-primary px-2 py-0.5 text-sm font-medium">
                                        <span x-text="turno"></span>
                                        <button type="button" @click="removeTurno(i)" class="text-primary/70 hover:text-primary">
                                            <x-lucide-x class="size-3.5" />
                                        </button>
                                    </span>
                                </template>
                                <input
                                    type="text"
                                    x-model="turnoInput"
                                    @keydown.enter.prevent="addTurno()"
                                    @keydown.comma.prevent="addTurno()"
                                    @keydown.backspace="if (!turnoInput && zonaForm.turnos.length) removeTurno(zonaForm.turnos.length - 1)"
                                    :placeholder="zonaForm.turnos.length ? 'Agregar otro…' : 'Escribir turno y Enter…'"
                                    maxlength="20"
                                    class="flex-1 min-w-32 bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                                />
                            </div>

                            {{-- Sugerencias de un clic (solo las que todavía no están cargadas) --}}
                            <div x-show="turnosSugeridos().length > 0" class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs text-muted-foreground">Sugerencias:</span>
                                <template x-for="sug in turnosSugeridos()" :key="sug">
                                    <button
                                        type="button"
                                        @click="addTurno(sug)"
                                        class="inline-flex items-center gap-1 rounded-md border border-dashed border-border px-2 py-0.5 text-sm text-muted-foreground transition-colors hover:border-primary/50 hover:text-foreground"
                                    >
                                        <x-lucide-plus class="size-3" />
                                        <span x-text="sug"></span>
                                    </button>
                                </template>
                            </div>

                            <p class="text-xs text-muted-foreground">
                                Nombres propios de esta zona. Escribí el que necesites (ej: Refuerzo) o usá una sugerencia.
                            </p>
                        </div>
                    </x-ui.collapsible.content>
                </x-ui.collapsible>

                {{-- Horarios --}}
                <x-ui.collapsible class="rounded-md border border-border" x-model="horariosSeccionAbierta">
                    <x-ui.collapsible.trigger class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left">
                        <span class="text-sm font-medium">Horarios de recorrido</span>
                        <span class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground" x-text="resumenHorarios()"></span>
                            <x-lucide-chevron-down class="size-4 text-muted-foreground transition-transform" x-bind:class="open && 'rotate-180'" />
                        </span>
                    </x-ui.collapsible.trigger>
                    <x-ui.collapsible.content class="px-3 pb-3 space-y-3">
                        <p class="text-xs text-muted-foreground -mt-1">Optativo. Elegí los días y cargá las franjas horarias.</p>

                        {{-- Selector de días --}}
                        <div class="space-y-2">
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="(dia, i) in diasCorto" :key="i">
                                    <button
                                        type="button"
                                        @click="toggleDia(i)"
                                        :class="zonaForm.horariosPorDia[i].length > 0
                                            ? 'bg-primary text-primary-foreground border-primary'
                                            : 'bg-transparent text-muted-foreground border-border hover:border-foreground/40'"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-md border transition-colors"
                                        x-text="dia"
                                    ></button>
                                </template>
                            </div>

                            {{-- Presets rápidos de días --}}
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-xs text-muted-foreground">Rápido:</span>
                                @foreach (['labores' => 'Lun a Vie', 'todos' => 'Todos', 'finde' => 'Fin de semana'] as $preset => $label)
                                    <button
                                        type="button"
                                        @click="togglePreset('{{ $preset }}')"
                                        :class="presetActivo('{{ $preset }}')
                                            ? 'bg-accent text-accent-foreground border-border'
                                            : 'bg-transparent text-muted-foreground border-dashed border-border hover:border-foreground/40 hover:text-foreground'"
                                        class="rounded-md border px-2 py-0.5 text-xs font-medium transition-colors"
                                    >{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Estado vacío: ningún día seleccionado --}}
                        <p
                            x-show="zonaForm.horariosPorDia.every(f => f.length === 0)"
                            class="rounded-md border border-dashed border-border px-3 py-4 text-center text-xs text-muted-foreground"
                        >
                            Sin días seleccionados. Elegí uno arriba o usá un preset para cargar horarios.
                        </p>

                        {{-- Tarjetas por día activo --}}
                        <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                            <template x-for="(franjas, diaIdx) in zonaForm.horariosPorDia" :key="diaIdx">
                                <div x-show="franjas.length > 0" class="rounded-md border border-border bg-muted/30 p-3 space-y-2">
                                    {{-- Encabezado: día + copiar + quitar --}}
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-foreground" x-text="diasLargo[diaIdx]"></span>
                                        <div class="flex items-center gap-1">
                                            <button
                                                type="button"
                                                x-show="hayOtrosDiasActivos(diaIdx)"
                                                @click="copiarFranjasADiasActivos(diaIdx)"
                                                class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                                title="Copiar estas franjas a los demás días activos"
                                            >
                                                <x-lucide-copy class="size-3" />
                                                Copiar a días
                                            </button>
                                            <button
                                                type="button"
                                                @click="toggleDia(diaIdx)"
                                                class="inline-flex items-center rounded-md p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                                                title="Quitar el día"
                                            >
                                                <x-lucide-x class="size-3.5" />
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Franjas: cada una eliminable --}}
                                    <template x-for="(franja, franjaIdx) in franjas" :key="franjaIdx">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="time"
                                                :value="franja.inicio"
                                                @change="updateFranja(diaIdx, franjaIdx, 'inicio', $event.target.value)"
                                                class="h-8 w-28 rounded-md border border-input bg-background px-2 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-ring"
                                            />
                                            <span class="text-muted-foreground text-sm">–</span>
                                            <input
                                                type="time"
                                                :value="franja.fin"
                                                @change="updateFranja(diaIdx, franjaIdx, 'fin', $event.target.value)"
                                                class="h-8 w-28 rounded-md border border-input bg-background px-2 text-sm tabular-nums focus:outline-none focus:ring-1 focus:ring-ring"
                                            />
                                            <x-ui.button type="button" variant="ghost" size="icon" class="size-7 text-muted-foreground" @click="removeFranja(diaIdx, franjaIdx)">
                                                <x-lucide-x class="size-3.5" />
                                            </x-ui.button>
                                        </div>
                                    </template>

                                    {{-- Agregar franja al día --}}
                                    <x-ui.button type="button" variant="ghost" size="sm" class="text-xs h-7 px-2" @click="addFranja(diaIdx)">
                                        <x-lucide-plus class="size-3" />
                                        Agregar franja
                                    </x-ui.button>
                                </div>
                            </template>
                        </div>
                    </x-ui.collapsible.content>
                </x-ui.collapsible>

                {{-- Mapa --}}
                <x-ui.collapsible class="rounded-md border border-border" x-model="mapaSeccionAbierta" x-effect="if (open) onMapaExpandido()">
                    <x-ui.collapsible.trigger class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left">
                        <span class="text-sm font-medium">Área en el mapa <span class="text-muted-foreground font-normal">(opcional)</span></span>
                        <span class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground" x-text="resumenMapa()"></span>
                            <x-lucide-chevron-down class="size-4 text-muted-foreground transition-transform" x-bind:class="open && 'rotate-180'" />
                        </span>
                    </x-ui.collapsible.trigger>
                    <x-ui.collapsible.content class="px-3 pb-3 space-y-2">
                        <p class="text-sm text-muted-foreground">
                            Dibujá el polígono de la zona con la herramienta de polígono. Marca los límites que el sistema usa para los mapas de calor. Las demás zonas de este servicio se muestran punteadas como referencia.
                        </p>
                        <div id="zona-map" class="h-72 sm:h-96 w-full rounded-md border border-border"></div>
                    </x-ui.collapsible.content>
                </x-ui.collapsible>
            </div>

            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit">
                    <x-lucide-save class="size-4" />
                    <span x-text="zonaModalMode === 'create' ? 'Crear zona' : 'Guardar cambios'"></span>
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
