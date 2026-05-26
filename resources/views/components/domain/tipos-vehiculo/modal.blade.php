@props([])

<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="sm">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.tipos-vehiculo.store') }}'
                : '{{ url('admin/tipos-vehiculo') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo tipo de vehículo' : 'Editar tipo de vehículo'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-2 pb-2">
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
                        placeholder="Ej: Compactador"
                        :state="$errors->has('nombre') ? 'destructive' : null"
                        autofocus
                    />
                </x-ui.form-field>

                <div class="grid grid-cols-2 gap-4">
                    <x-ui.form-field
                        for="peso_min_kg"
                        :state="$errors->has('peso_min_kg') ? 'destructive' : null"
                        :message="$errors->first('peso_min_kg')"
                    >
                        <x-ui.label for="peso_min_kg">Peso mínimo (kg)</x-ui.label>
                        <x-ui.input
                            id="peso_min_kg"
                            name="peso_min_kg"
                            type="number"
                            min="0"
                            x-model="form.peso_min_kg"
                            placeholder="0"
                            :state="$errors->has('peso_min_kg') ? 'destructive' : null"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field
                        for="peso_max_kg"
                        :state="$errors->has('peso_max_kg') ? 'destructive' : null"
                        :message="$errors->first('peso_max_kg')"
                    >
                        <x-ui.label for="peso_max_kg">Peso máximo (kg)</x-ui.label>
                        <x-ui.input
                            id="peso_max_kg"
                            name="peso_max_kg"
                            type="number"
                            min="1"
                            x-model="form.peso_max_kg"
                            placeholder="0"
                            :state="$errors->has('peso_max_kg') ? 'destructive' : null"
                        />
                    </x-ui.form-field>
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
