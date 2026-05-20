<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="md">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('super.organizaciones.store') }}'
                : '{{ url('organizaciones') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nueva organización' : 'Editar organización'"
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
                        placeholder="Ej: Municipalidad de Corrientes"
                        :state="$errors->has('nombre') ? 'destructive' : null"
                        autofocus
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="slug"
                    :state="$errors->has('slug') ? 'destructive' : null"
                    :message="$errors->first('slug')"
                >
                    <x-ui.label for="slug">Subdominio</x-ui.label>
                    <x-ui.input
                        id="slug"
                        name="slug"
                        x-model="form.slug"
                        placeholder="Ej: corrientes"
                        :state="$errors->has('slug') ? 'destructive' : null"
                    />
                    <p class="text-xs text-muted-foreground">
                        Se usa como subdominio para acceder al sistema. Solo letras, números y guiones.
                    </p>
                </x-ui.form-field>

                {{-- Campos de admin: solo al crear --}}
                <div x-show="modalMode === 'create'" x-cloak class="space-y-2 pt-2">
                    <x-ui.separator />
                    <p class="text-sm font-medium pt-1">Usuario administrador</p>

                    <x-ui.form-field
                        for="admin_email"
                        :state="$errors->has('admin_email') ? 'destructive' : null"
                        :message="$errors->first('admin_email')"
                    >
                        <x-ui.label for="admin_email">Correo electrónico</x-ui.label>
                        <x-ui.input
                            id="admin_email"
                            name="admin_email"
                            type="email"
                            x-model="form.admin_email"
                            placeholder="Ej: admin@corrientes.gob.ar"
                            :state="$errors->has('admin_email') ? 'destructive' : null"
                            autocomplete="off"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field
                        for="admin_password"
                        :state="$errors->has('admin_password') ? 'destructive' : null"
                        :message="$errors->first('admin_password')"
                    >
                        <x-ui.label for="admin_password">Contraseña inicial</x-ui.label>
                        <x-ui.input
                            id="admin_password"
                            name="admin_password"
                            type="password"
                            x-model="form.admin_password"
                            placeholder="Mínimo 8 caracteres"
                            :state="$errors->has('admin_password') ? 'destructive' : null"
                            autocomplete="new-password"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field
                        for="admin_password_confirmation"
                        :state="$errors->has('admin_password') ? 'destructive' : null"
                    >
                        <x-ui.label for="admin_password_confirmation">Confirmar contraseña</x-ui.label>
                        <x-ui.input
                            id="admin_password_confirmation"
                            name="admin_password_confirmation"
                            type="password"
                            x-model="form.admin_password_confirmation"
                            placeholder="Repetí la contraseña"
                            :state="$errors->has('admin_password') ? 'destructive' : null"
                            autocomplete="new-password"
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
