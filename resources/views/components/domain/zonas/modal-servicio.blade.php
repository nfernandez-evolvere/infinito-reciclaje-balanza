@props(['tiposServicio'])

<div x-data="{ get open() { return servicioModalOpen }, set open(v) { servicioModalOpen = v } }">
    <x-ui.dialog.content>
        <form
            method="POST"
            :action="servicioModalMode === 'create'
                ? '{{ url('admin/zonas') }}/' + selectedZonaId + '/servicios'
                : '{{ url('admin/zonas') }}/' + selectedZonaId + '/servicios/' + editServicioId"
        >
            @csrf
            <input type="hidden" name="_method"
                :value="servicioModalMode === 'create' ? 'POST' : 'PUT'" />

            <template x-for="turno in servicioForm.turnos" :key="turno">
                <input type="hidden" name="turnos[]" :value="turno" />
            </template>

            <template x-for="(franjas, diaIdx) in servicioForm.horariosPorDia" :key="diaIdx">
                <template x-for="(franja, franjaIdx) in franjas" :key="franjaIdx">
                    <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][inicio]`" :value="franja.inicio" />
                    <input type="hidden" :name="`horarios[${diaIdx}][${franjaIdx}][fin]`" :value="franja.fin" />
                </template>
            </template>

            <x-ui.dialog.header>
                <x-ui.dialog.title>
                    <span x-show="servicioModalMode === 'create'" x-text="'Asignar servicio — ' + selectedZonaNombre"></span>
                    <span x-show="servicioModalMode === 'edit'" x-cloak x-text="'Editar servicio — ' + editServicioNombre"></span>
                </x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-5 pb-2 overflow-y-auto flex-1">

                <x-ui.form-field for="tipo_servicio_id">
                    <x-ui.label for="tipo_servicio_id">Tipo de servicio</x-ui.label>

                    <div x-show="servicioModalMode === 'edit'" x-cloak
                        class="flex h-9 items-center rounded-md border border-input bg-muted px-3 text-sm font-medium text-foreground">
                        <span x-text="editServicioNombre"></span>
                    </div>

                    <div x-show="servicioModalMode === 'create'">
                        <x-ui.select
                            name="tipo_servicio_id"
                            x-effect="value = String(servicioForm.tipo_servicio_id ?? '')"
                            @select-change="servicioForm.tipo_servicio_id = $event.detail.value"
                        >
                            <x-ui.select.trigger id="tipo_servicio_id">
                                <x-ui.select.value placeholder="Seleccionar servicio…" />
                            </x-ui.select.trigger>
                            <x-ui.select.content>
                                <x-ui.select.item value="">Seleccionar servicio…</x-ui.select.item>
                                @foreach($tiposServicio as $ts)
                                    <template x-if="!assignedServicioIds.includes({{ $ts->id }})">
                                        <x-ui.select.item value="{{ $ts->id }}">{{ $ts->nombre }}</x-ui.select.item>
                                    </template>
                                @endforeach
                            </x-ui.select.content>
                        </x-ui.select>
                    </div>
                </x-ui.form-field>

                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="servicioForm.turnosEnabled.toString()"
                            :class="servicioForm.turnosEnabled ? 'bg-primary' : 'bg-input'"
                            @click="servicioForm.turnosEnabled = !servicioForm.turnosEnabled; if (!servicioForm.turnosEnabled) servicioForm.turnos = []"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                        >
                            <span
                                class="pointer-events-none inline-block size-5 rounded-full bg-background shadow-sm ring-0 transition-transform"
                                :class="servicioForm.turnosEnabled ? 'translate-x-5' : 'translate-x-0'"
                            ></span>
                        </button>
                        <span class="text-sm font-medium">Opera con turnos</span>
                    </label>

                    <div x-show="servicioForm.turnosEnabled" x-cloak class="flex gap-2 pl-10">
                        @foreach(['Diurna', 'Nocturna'] as $turno)
                            <button
                                type="button"
                                @click="toggleTurno('{{ $turno }}')"
                                :class="servicioForm.turnos.includes('{{ $turno }}')
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-transparent text-muted-foreground border-border hover:border-foreground/40'"
                                class="px-4 py-1.5 text-sm font-medium rounded-md border transition-colors"
                            >
                                {{ $turno }}
                            </button>
                        @endforeach
                    </div>
                </div>

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
                                :class="servicioForm.horariosPorDia[i].length > 0
                                    ? 'bg-primary text-primary-foreground border-primary'
                                    : 'bg-transparent text-muted-foreground border-border hover:border-foreground/40'"
                                class="px-2.5 py-1 text-xs font-semibold rounded-md border transition-colors"
                                x-text="dia"
                            ></button>
                        </template>
                    </div>

                    <div class="space-y-3 max-h-52 overflow-y-auto pr-1">
                        <template x-for="(franjas, diaIdx) in servicioForm.horariosPorDia" :key="diaIdx">
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
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                class="text-xs h-7 px-2"
                                                @click="addFranja(diaIdx)"
                                            >
                                                <x-lucide-plus class="size-3" />
                                                Franja
                                            </x-ui.button>
                                        </template>
                                        <template x-if="franjaIdx > 0">
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                size="icon"
                                                class="size-7 text-muted-foreground"
                                                @click="removeFranja(diaIdx, franjaIdx)"
                                            >
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
                <x-ui.button
                    type="submit"
                    x-bind:disabled="servicioModalMode === 'create' && !servicioForm.tipo_servicio_id"
                >
                    <x-lucide-save class="size-4" />
                    <span x-text="servicioModalMode === 'create' ? 'Asignar' : 'Guardar cambios'"></span>
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
