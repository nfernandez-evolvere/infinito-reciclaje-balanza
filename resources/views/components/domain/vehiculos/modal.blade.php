@props(['tiposVehiculo'])

<x-ui.sheet side="right" controlled-by="modalOpen">
    <form
        method="POST"
        :action="modalMode === 'create'
            ? '{{ route('admin.vehiculos.store') }}'
            : '{{ url('admin/vehiculos') }}/' + form.id"
        @submit="saving = true"
    >
        @csrf
        <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
        <input type="hidden" name="_mode"       :value="modalMode" />
        <input type="hidden" name="_editing_id" :value="form.id" />
        <input type="hidden" name="_tab"        value="vehiculos" />
        <input type="hidden" name="_tara_original"  :value="form._tara_original" />
        <input type="hidden" name="_pesajes_count"  :value="form.pesajes_count" />

        <x-ui.sheet.header>
            <x-ui.sheet.title
                x-text="modalMode === 'create' ? 'Nuevo vehículo' : 'Editar vehículo'"
            ></x-ui.sheet.title>
        </x-ui.sheet.header>

        <x-ui.sheet.content>
            <div class="space-y-3">

                {{-- Datos generales: se colapsan cuando hay que decidir sobre la tara,
                     para que el foco quede solo en esa decisión. --}}
                <div x-show="!mostrarDecisionTara" x-collapse>
                    <div class="space-y-3">
                        <x-ui.form-field
                            for="patente"
                            :state="$errors->has('patente') ? 'destructive' : null"
                            :message="$errors->first('patente')"
                        >
                            <x-ui.label for="patente">Patente</x-ui.label>
                            <x-ui.input
                                id="patente"
                                name="patente"
                                x-model="form.patente"
                                placeholder="Ej: ABC123"
                                :state="$errors->has('patente') ? 'destructive' : null"
                                autofocus
                            />
                        </x-ui.form-field>

                        <x-ui.form-field
                            for="numero_interno"
                            :state="$errors->has('numero_interno') ? 'destructive' : null"
                            :message="$errors->first('numero_interno')"
                        >
                            <x-ui.label for="numero_interno">
                                N.° interno
                                <span class="text-muted-foreground font-normal">— opcional</span>
                            </x-ui.label>
                            <x-ui.input
                                id="numero_interno"
                                name="numero_interno"
                                x-model="form.numero_interno"
                                placeholder="Ej: 042"
                                :state="$errors->has('numero_interno') ? 'destructive' : null"
                            />
                        </x-ui.form-field>

                        <x-ui.form-field
                            for="tipo_vehiculo_id"
                            :state="$errors->has('tipo_vehiculo_id') ? 'destructive' : null"
                            :message="$errors->first('tipo_vehiculo_id')"
                        >
                            <x-ui.label for="tipo_vehiculo_id">Tipo de vehículo</x-ui.label>
                            <x-ui.select
                                name="tipo_vehiculo_id"
                                x-effect="value = String(form.tipo_vehiculo_id ?? '')"
                                @select-change="form.tipo_vehiculo_id = $event.detail.value"
                            >
                                <x-ui.select.trigger id="tipo_vehiculo_id" :state="$errors->has('tipo_vehiculo_id') ? 'destructive' : null">
                                    <x-ui.select.value placeholder="Seleccionar tipo…" />
                                </x-ui.select.trigger>
                                <x-ui.select.content>
                                    @foreach($tiposVehiculo as $tipo)
                                        <x-ui.select.item value="{{ $tipo->id }}">{{ $tipo->nombre }}</x-ui.select.item>
                                    @endforeach
                                </x-ui.select.content>
                            </x-ui.select>
                        </x-ui.form-field>

                        <x-ui.form-field
                            for="titular"
                            :state="$errors->has('titular') ? 'destructive' : null"
                            :message="$errors->first('titular')"
                        >
                            <x-ui.label for="titular">Titular</x-ui.label>
                            <x-ui.input
                                id="titular"
                                name="titular"
                                x-model="form.titular"
                                placeholder="Ej: Municipalidad de Corrientes"
                                :state="$errors->has('titular') ? 'destructive' : null"
                            />
                        </x-ui.form-field>
                    </div>
                </div>

                {{-- Tara: ancla, siempre visible (permite ajustar o revertir el cambio). --}}
                <x-ui.form-field
                    for="tara_kg"
                    :state="$errors->has('tara_kg') ? 'destructive' : null"
                    :message="$errors->first('tara_kg')"
                >
                    <x-ui.label for="tara_kg">Tara (kg)</x-ui.label>
                    <x-ui.input
                        id="tara_kg"
                        name="tara_kg"
                        type="number"
                        min="1"
                        x-model="form.tara_kg"
                        @input="taraDecisionConfirmada = false"
                        placeholder="0"
                        :state="$errors->has('tara_kg') ? 'destructive' : null"
                    />
                </x-ui.form-field>

                {{-- Resumen de la decisión ya confirmada: se muestra junto a los campos
                     normales, con opción de volver a editarla. --}}
                <div x-show="mostrarResumenTara" x-cloak x-collapse>
                    <div class="rounded-2xl border border-border bg-muted/40 p-4 space-y-3">
                        {{-- Encabezado: tipo de cambio + acción de editar --}}
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 text-sm font-medium">
                                <x-lucide-circle-check class="size-4 shrink-0 text-success" />
                                <span x-text="form._intencion_tara === 'corregir_dato' ? 'Corregir un dato mal cargado' : 'Cambio real del vehículo'"></span>
                            </div>
                            <x-ui.button size="sm" variant="ghost" class="shrink-0 -mr-1.5" @click="taraDecisionConfirmada = false">
                                <x-lucide-pencil class="size-3.5" />
                            </x-ui.button>
                        </div>

                        {{-- Cambio de tara y, si corrige, cuántos pesajes recalcula --}}
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                            {{-- Valores del cambio: alineados a la base, con dígitos tabulares --}}
                            <div class="flex items-baseline gap-2 text-sm tabular-nums">
                                <span class="text-muted-foreground">
                                    <span x-text="Number(form._tara_original || 0).toLocaleString('es-AR')"></span> kg
                                </span>
                                <x-lucide-arrow-right class="size-3.5 shrink-0 self-center text-muted-foreground/60" />
                                <span class="font-semibold text-foreground">
                                    <span x-text="Number(form.tara_kg || 0).toLocaleString('es-AR')"></span> kg
                                </span>
                            </div>

                            {{-- Consecuencia: cuántos pesajes se recalculan (solo al corregir un dato) --}}
                            <template x-if="form._intencion_tara === 'corregir_dato'">
                                <x-ui.badge variant="outline" class="gap-1 font-normal text-muted-foreground">
                                    <x-lucide-refresh-cw class="size-3 shrink-0" />
                                    Recalcula
                                    <span class="font-semibold text-foreground" x-text="form.pesajes_count"></span>
                                    <span x-text="Number(form.pesajes_count) === 1 ? 'pesaje' : 'pesajes'"></span>
                                </x-ui.badge>
                            </template>
                        </div>

                        {{-- Motivo registrado --}}
                        <p class="text-xs text-muted-foreground wrap-break-word border-t border-border/60 pt-2.5"
                           x-show="form._motivo_tara" x-text="form._motivo_tara"></p>
                    </div>
                </div>

                {{-- Datos generales (cont.): también se colapsan al decidir sobre la tara. --}}
                <div x-show="!mostrarDecisionTara" x-collapse>
                    <div class="space-y-3">
                        <x-ui.form-field
                            for="capacidad_kg"
                            :state="$errors->has('capacidad_kg') ? 'destructive' : null"
                            :message="$errors->first('capacidad_kg')"
                        >
                            <x-ui.label for="capacidad_kg">
                                Capacidad (kg)
                                <span class="text-muted-foreground font-normal">— opcional</span>
                            </x-ui.label>
                            <x-ui.input
                                id="capacidad_kg"
                                name="capacidad_kg"
                                type="number"
                                min="1"
                                x-model="form.capacidad_kg"
                                placeholder="0"
                                :state="$errors->has('capacidad_kg') ? 'destructive' : null"
                            />
                        </x-ui.form-field>

                        <x-ui.form-field
                            for="observaciones"
                            :state="$errors->has('observaciones') ? 'destructive' : null"
                            :message="$errors->first('observaciones')"
                        >
                            <x-ui.label for="observaciones">
                                Observaciones
                                <span class="text-muted-foreground font-normal">— opcional</span>
                            </x-ui.label>
                            <x-ui.textarea
                                id="observaciones"
                                name="observaciones"
                                x-model="form.observaciones"
                                placeholder="Notas visibles para el operador al seleccionar el vehículo…"
                                rows="3"
                                :state="$errors->has('observaciones') ? 'destructive' : null"
                            />
                        </x-ui.form-field>
                    </div>
                </div>

                {{-- Decisión de corrección de tara: solo al editar cuando la tara cambió
                     y el vehículo ya tiene pesajes. Reemplaza a los demás campos hasta
                     que se confirme la acción. --}}
                <div x-show="mostrarDecisionTara" x-cloak x-collapse>
                    <x-ui.alert state="warning" title="Cambiaste la tara de este vehículo" hideIcon="true">
                        <div class="space-y-1">
                            <p class="text-sm">
                                <span x-text="Number(form._tara_original || 0).toLocaleString('es-AR')"></span> kg
                                <span class="px-1">→</span>
                                <span class="font-semibold" x-text="Number(form.tara_kg || 0).toLocaleString('es-AR')"></span> kg
                                <span class="px-1 text-warning-subtle-foreground/60">·</span>
                                <span class="font-semibold" x-text="form.pesajes_count"></span>
                                <span x-text="Number(form.pesajes_count) === 1 ? 'pesaje' : 'pesajes'"></span>
                            </p>
                            <p class="text-xs text-warning-subtle-foreground/90">Elegí cómo aplicar el cambio y confirmá. Podés ajustar la tara arriba.</p>
                        </div>

                        <div class="mt-4 space-y-4">
                            <x-domain.vehiculos.opcion-tara
                                value="corregir_dato"
                                titulo="Corregir tara mal cargada — recalcula los pesajes anteriores"
                            />

                            <x-domain.vehiculos.opcion-tara
                                value="cambio_real"
                                titulo="Cambio real del vehículo — solo afecta los pesajes nuevos"
                            />

                            @error('_intencion_tara')
                                <x-ui.helper-text state="destructive" :message="$message" />
                            @enderror

                            <x-ui.form-field
                                for="_motivo_tara"
                                :state="$errors->has('_motivo_tara') ? 'destructive' : null"
                                :message="$errors->first('_motivo_tara')"
                            >
                                <x-ui.label for="_motivo_tara">Motivo</x-ui.label>
                                <x-ui.textarea
                                    id="_motivo_tara"
                                    name="_motivo_tara"
                                    x-model="form._motivo_tara"
                                    rows="2"
                                    placeholder="Ej: se había cargado 8.000 en vez de 18.000."
                                    :state="$errors->has('_motivo_tara') ? 'destructive' : 'warning'"
                                />
                            </x-ui.form-field>
                        </div>
                    </x-ui.alert>
                </div>

            </div>
        </x-ui.sheet.content>

        <x-ui.sheet.footer>
            {{-- Acciones normales (crear / guardar). Ocultas durante el sub-paso de decisión. --}}
            <x-ui.button type="button" variant="ghost" class="flex-1" x-show="!mostrarDecisionTara" x-bind:disabled="saving" @click="modalOpen = false">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="submit" class="flex-1" x-show="!mostrarDecisionTara" x-bind:disabled="saving">
                <x-ui.spinner size="sm" class="text-current" x-show="saving" x-cloak />
                <x-lucide-save class="size-4" x-show="!saving" />
                <span x-show="!saving" x-text="textoGuardar"></span>
            </x-ui.button>

            {{-- Sub-paso de decisión de tara: confirma la acción, no guarda todavía. --}}
            <x-ui.button type="button" variant="ghost" state="warning" class="flex-1" x-show="mostrarDecisionTara" x-cloak @click="cancelarDecisionTara()">
                <x-lucide-x class="size-4" />
                Cancelar
            </x-ui.button>
            <x-ui.button type="button" state="warning" class="flex-1" x-show="mostrarDecisionTara" x-cloak
                @click="confirmarDecisionTara()"
                x-bind:disabled="!form._intencion_tara || !String(form._motivo_tara).trim()"
            >
                <x-lucide-check class="size-4" />
                Confirmar
            </x-ui.button>
        </x-ui.sheet.footer>
    </form>
</x-ui.sheet>
