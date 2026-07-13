@props(['tiposVehiculo'])

<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="sm">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.tipos-servicio.store') }}'
                : '{{ url('admin/tipos-servicio') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo tipo de servicio' : 'Editar tipo de servicio'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-4 pb-2">
                <x-ui.form-field
                    for="nombre"
                    :state="$errors->has('nombre') ? 'destructive' : null"
                    :message="$errors->first('nombre')"
                >
                    <x-ui.label for="nombre">Nombre</x-ui.label>
                    <x-ui.input
                        id="nombre"
                        name="nombre"
                        x-model="form.nombre"
                        placeholder="Ej: Domiciliario"
                        autofocus
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="descripcion"
                    :state="$errors->has('descripcion') ? 'destructive' : null"
                    :message="$errors->first('descripcion')"
                >
                    <x-ui.label for="descripcion">Descripción</x-ui.label>
                    <x-ui.textarea
                        id="descripcion"
                        name="descripcion"
                        x-model="form.descripcion"
                        rows="3"
                        placeholder="Qué recolecta este servicio. Aparece en el reporte mensual del municipio."
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    :state="$errors->has('tipo_vehiculo_ids') ? 'destructive' : null"
                    :message="$errors->first('tipo_vehiculo_ids')"
                >
                    <x-ui.label>Vehículos sugeridos</x-ui.label>
                    <x-ui.multi-select
                        name="tipo_vehiculo_ids"
                        :options="$tiposVehiculo->pluck('nombre', 'id')->toArray()"
                        placeholder="Sin vehículos asignados"
                        :state="$errors->has('tipo_vehiculo_ids') ? 'destructive' : null"
                        x-effect="values = form.tipo_vehiculo_ids.map(String)"
                        @change="form.tipo_vehiculo_ids = $event.detail.values.map(Number)"
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
