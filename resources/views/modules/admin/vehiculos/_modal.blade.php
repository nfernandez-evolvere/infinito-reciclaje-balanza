<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="md">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.vehiculos.store') }}'
                : '{{ url('admin/vehiculos') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo vehículo' : 'Editar vehículo'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-2 pb-2">

                <div class="grid grid-cols-2 gap-4">
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
                        <x-ui.label for="numero_interno">N.° interno</x-ui.label>
                        <x-ui.input
                            id="numero_interno"
                            name="numero_interno"
                            x-model="form.numero_interno"
                            placeholder="Ej: 042"
                            :state="$errors->has('numero_interno') ? 'destructive' : null"
                        />
                    </x-ui.form-field>
                </div>

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

                <div class="grid grid-cols-2 gap-4">
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
                            placeholder="0"
                            :state="$errors->has('tara_kg') ? 'destructive' : null"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field
                        for="capacidad_kg"
                        :state="$errors->has('capacidad_kg') ? 'destructive' : null"
                        :message="$errors->first('capacidad_kg')"
                    >
                        <x-ui.label for="capacidad_kg">Capacidad (kg) <span class="text-muted-foreground font-normal">— opcional</span></x-ui.label>
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
                </div>

                <x-ui.form-field
                    for="observaciones"
                    :state="$errors->has('observaciones') ? 'destructive' : null"
                    :message="$errors->first('observaciones')"
                >
                    <x-ui.label for="observaciones">Observaciones <span class="text-muted-foreground font-normal">— opcional</span></x-ui.label>
                    <x-ui.textarea
                        id="observaciones"
                        name="observaciones"
                        x-model="form.observaciones"
                        placeholder="Notas visibles para el operador al seleccionar el vehículo…"
                        rows="2"
                        :state="$errors->has('observaciones') ? 'destructive' : null"
                    />
                </x-ui.form-field>

            </div>

            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit">
                    <x-lucide-save class="size-4" />
                    <span x-text="modalMode === 'create' ? 'Crear' : 'Guardar cambios'"></span>
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
