<div
    x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }"
    x-init="$watch('modalOpen', v => { if (v) syncMapToForm() }); if (modalOpen) syncMapToForm()"
>
    <x-ui.dialog.content size="xl">
        <form
            class="contents"
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.zonas.store') }}'
                : '{{ url('admin/zonas') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />
            <input type="hidden" name="geojson"     :value="form.geojson" />
            <input type="hidden" name="centro_lat"  :value="form.centro_lat" />
            <input type="hidden" name="centro_lng"  :value="form.centro_lng" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nueva zona' : 'Editar zona'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 pb-2 overflow-y-auto flex-1 min-h-0">
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
                        placeholder="Ej: Zona Norte"
                        autofocus
                    />
                </x-ui.form-field>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-4">
                    <x-ui.form-field
                        for="hectareas"
                        :state="$errors->has('hectareas') ? 'destructive' : null"
                        :message="$errors->first('hectareas')"
                    >
                        <x-ui.label for="hectareas">Hectáreas</x-ui.label>
                        <x-ui.input
                            id="hectareas"
                            name="hectareas"
                            type="number"
                            step="0.01"
                            min="0"
                            x-model="form.hectareas"
                            placeholder="0"
                        />
                    </x-ui.form-field>
                    <x-ui.form-field
                        for="barrios"
                        :state="$errors->has('barrios') ? 'destructive' : null"
                        :message="$errors->first('barrios')"
                    >
                        <x-ui.label for="barrios">Barrios</x-ui.label>
                        <x-ui.input
                            id="barrios"
                            name="barrios"
                            type="number"
                            min="0"
                            x-model="form.barrios"
                            placeholder="0"
                        />
                    </x-ui.form-field>
                    <x-ui.form-field
                        for="habitantes"
                        :state="$errors->has('habitantes') ? 'destructive' : null"
                        :message="$errors->first('habitantes')"
                    >
                        <x-ui.label for="habitantes">Habitantes <span class="text-muted-foreground font-normal">(opcional)</span></x-ui.label>
                        <x-ui.input
                            id="habitantes"
                            name="habitantes"
                            type="number"
                            min="0"
                            x-model="form.habitantes"
                            placeholder="0"
                        />
                    </x-ui.form-field>
                </div>

                <div class="mt-4 space-y-2">
                    <x-ui.label>Área en el mapa <span class="text-muted-foreground font-normal">(opcional)</span></x-ui.label>
                    <p class="text-sm text-muted-foreground">
                        Dibujá el polígono de la zona con la herramienta de polígono. Marca los límites que el sistema usa para los mapas de calor.
                    </p>
                    <div id="zona-map" class="h-80 sm:h-120 w-full rounded-md border border-border"></div>
                </div>
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
