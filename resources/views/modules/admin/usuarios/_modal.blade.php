<div x-data="{ get open() { return modalOpen }, set open(v) { modalOpen = v } }">
    <x-ui.dialog.content size="md">
        <form
            method="POST"
            :action="modalMode === 'create'
                ? '{{ route('admin.usuarios.store') }}'
                : '{{ url('admin/usuarios') }}/' + form.id"
        >
            @csrf
            <input type="hidden" name="_method"     :value="modalMode === 'edit' ? 'PUT' : 'POST'" />
            <input type="hidden" name="_mode"       :value="modalMode" />
            <input type="hidden" name="_editing_id" :value="form.id" />

            <x-ui.dialog.header>
                <x-ui.dialog.title
                    x-text="modalMode === 'create' ? 'Nuevo usuario' : 'Editar usuario'"
                ></x-ui.dialog.title>
            </x-ui.dialog.header>

            <div class="px-6 space-y-2 pb-2">

                <x-ui.form-field
                    for="name"
                    :state="$errors->has('name') ? 'destructive' : null"
                    :message="$errors->first('name')"
                >
                    <x-ui.label for="name">Nombre completo</x-ui.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        x-model="form.name"
                        placeholder="Ej: Roberto García"
                        :state="$errors->has('name') ? 'destructive' : null"
                        autofocus
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="email"
                    :state="$errors->has('email') ? 'destructive' : null"
                    :message="$errors->first('email')"
                >
                    <x-ui.label for="email">Correo electrónico</x-ui.label>
                    <x-ui.input
                        id="email"
                        name="email"
                        type="email"
                        x-model="form.email"
                        placeholder="Ej: roberto@municipio.gob.ar"
                        :state="$errors->has('email') ? 'destructive' : null"
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="role"
                    :state="$errors->has('role') ? 'destructive' : null"
                    :message="$errors->first('role')"
                >
                    <x-ui.label for="role">Rol</x-ui.label>
                    <x-ui.select
                        name="role"
                        x-effect="value = String(form.role ?? '')"
                        @select-change="form.role = $event.detail.value"
                    >
                        <x-ui.select.trigger id="role" :state="$errors->has('role') ? 'destructive' : null">
                            <x-ui.select.value placeholder="Seleccionar rol…" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="operador">Operador</x-ui.select.item>
                            <x-ui.select.item value="admin">Admin</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                <div x-show="modalMode === 'create'" x-cloak class="space-y-2">
                    <x-ui.form-field
                        for="password"
                        :state="$errors->has('password') ? 'destructive' : null"
                        :message="$errors->first('password')"
                    >
                        <x-ui.label for="password">Contraseña inicial</x-ui.label>
                        <x-ui.input
                            id="password"
                            name="password"
                            type="password"
                            x-model="form.password"
                            placeholder="Mínimo 8 caracteres"
                            :state="$errors->has('password') ? 'destructive' : null"
                            autocomplete="new-password"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field
                        for="password_confirmation"
                        :state="$errors->has('password') ? 'destructive' : null"
                    >
                        <x-ui.label for="password_confirmation">Confirmar contraseña</x-ui.label>
                        <x-ui.input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            x-model="form.password_confirmation"
                            placeholder="Repetí la contraseña"
                            :state="$errors->has('password') ? 'destructive' : null"
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
