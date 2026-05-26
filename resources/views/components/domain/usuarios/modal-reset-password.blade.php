@props([])

<div x-data="{ get open() { return resetOpen }, set open(v) { resetOpen = v } }">
    <x-ui.dialog.content size="sm">
        <form
            method="POST"
            :action="'{{ url('admin/usuarios') }}/' + resetId + '/reset-password'"
        >
            @csrf
            <input type="hidden" name="_method" value="PATCH" />

            <x-ui.dialog.header>
                <x-ui.dialog.title>Restablecer contraseña</x-ui.dialog.title>
                <x-ui.dialog.description>
                    Nueva contraseña para <strong x-text="resetNombre" class="text-foreground font-medium"></strong>.
                    El usuario deberá usarla en su próximo inicio de sesión.
                </x-ui.dialog.description>
            </x-ui.dialog.header>

            <div class="px-6 space-y-2 pb-2">
                <x-ui.form-field
                    for="reset_password"
                    :state="$errors->has('password') ? 'destructive' : null"
                    :message="$errors->first('password')"
                >
                    <x-ui.label for="reset_password">Nueva contraseña</x-ui.label>
                    <x-ui.input
                        id="reset_password"
                        name="password"
                        type="password"
                        placeholder="Mínimo 8 caracteres"
                        :state="$errors->has('password') ? 'destructive' : null"
                        autocomplete="new-password"
                    />
                </x-ui.form-field>

                <x-ui.form-field
                    for="reset_password_confirmation"
                    :state="$errors->has('password') ? 'destructive' : null"
                >
                    <x-ui.label for="reset_password_confirmation">Confirmar contraseña</x-ui.label>
                    <x-ui.input
                        id="reset_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        placeholder="Repetí la contraseña"
                        :state="$errors->has('password') ? 'destructive' : null"
                        autocomplete="new-password"
                    />
                </x-ui.form-field>
            </div>

            <x-ui.dialog.footer>
                <x-ui.button type="button" variant="ghost" @click="open = false">
                    <x-lucide-x class="size-4" />
                    Cancelar
                </x-ui.button>
                <x-ui.button type="submit">
                    <x-lucide-key-round class="size-4" />
                    Restablecer
                </x-ui.button>
            </x-ui.dialog.footer>
        </form>
    </x-ui.dialog.content>
</div>
