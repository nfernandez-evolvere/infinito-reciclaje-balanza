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

            <template x-for="(franjas, diaIdx) in zonaForm.horariosPorDia" :key="diaIdx">
                <template x-for="(franja, franjaIdx) in franjas" :key="franjaIdx">
                    <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][inicio]`" :value="franja.inicio" />
                    <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][fin]`" :value="franja.fin" />
                </template>
            </template>

            <x-ui.dialog.header>
                <x-ui.dialog.title>
                    <span x-text="(zonaModalMode === 'create' ? 'Nueva zona — ' : 'Editar zona — ') + selectedServicioNombre"></span>
                </x-ui.dialog.title>
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

                <div class="space-y-2">
                    <x-ui.label>Área en el mapa <span class="text-muted-foreground font-normal">(opcional)</span></x-ui.label>
                    <p class="text-sm text-muted-foreground">
                        Dibujá el polígono de la zona con la herramienta de polígono. Marca los límites que el sistema usa para los mapas de calor.
                    </p>
                    <div id="zona-map" class="h-72 sm:h-96 w-full rounded-md border border-border"></div>
                </div>

                {{-- Turnos --}}
                <div class="space-y-3">
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

                    <div x-show="zonaForm.turnosEnabled" x-cloak class="flex gap-2 pl-10">
                        @foreach(['Diurna', 'Nocturna'] as $turno)
                            <button
                                type="button"
                                @click="toggleTurno('{{ $turno }}')"
                                :class="zonaForm.turnos.includes('{{ $turno }}')
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-transparent text-muted-foreground border-border hover:border-foreground/40'"
                                class="px-4 py-1.5 text-sm font-medium rounded-md border transition-colors"
                            >
                                {{ $turno }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Horarios --}}
                <div class="space-y-3">
                    <div>
                        <p class="text-sm font-medium">Horarios de recorrido</p>
                        <p class="text-xs text-muted-foreground">Optativo. Seleccioná los días y cargá las franjas horarias.</p>
                    </div>

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

                    <div class="space-y-3 max-h-52 overflow-y-auto pr-1">
                        <template x-for="(franjas, diaIdx) in zonaForm.horariosPorDia" :key="diaIdx">
                            <div x-show="franjas.length > 0" class="space-y-2">
                                <span class="text-xs font-semibold text-foreground" x-text="diasLargo[diaIdx]"></span>
                                <template x-for="(franja, franjaIdx) in franjas" :key="franjaIdx">
                                    <div class="flex items-center gap-2 pl-2">
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
                                        <template x-if="franjaIdx === 0">
                                            <x-ui.button type="button" variant="ghost" size="sm" class="text-xs h-7 px-2" @click="addFranja(diaIdx)">
                                                <x-lucide-plus class="size-3" />
                                                Franja
                                            </x-ui.button>
                                        </template>
                                        <template x-if="franjaIdx > 0">
                                            <x-ui.button type="button" variant="ghost" size="icon" class="size-7 text-muted-foreground" @click="removeFranja(diaIdx, franjaIdx)">
                                                <x-lucide-x class="size-3.5" />
                                            </x-ui.button>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
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
