<x-layouts.auth
    title="Nueva contraseña"
    cardTitle="Nueva contraseña"
    cardDescription="Elegí una contraseña segura para tu cuenta."
>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-ui.form-field for="email" :state="$errors->has('email') ? 'destructive' : null" :message="$errors->first('email')">
            <x-ui.label for="email">Correo electrónico</x-ui.label>
            <x-ui.input
                id="email"
                type="email"
                name="email"
                :value="old('email', $request->email)"
                autocomplete="username"
            />
        </x-ui.form-field>

        <x-ui.form-field for="password" :state="$errors->has('password') ? 'destructive' : null" :message="$errors->first('password')">
            <x-ui.label for="password">Nueva contraseña</x-ui.label>
            <x-ui.input
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
            />
        </x-ui.form-field>

        <x-ui.form-field for="password_confirmation" :state="$errors->has('password_confirmation') ? 'destructive' : null" :message="$errors->first('password_confirmation')">
            <x-ui.label for="password_confirmation">Confirmá la contraseña</x-ui.label>
            <x-ui.input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
            />
        </x-ui.form-field>

        <x-ui.button type="submit" class="w-full">
            Guardar contraseña
        </x-ui.button>
    </form>

    <x-slot:footerLink>
        <x-ui.button variant="link" href="{{ route('login') }}">
            <x-lucide-arrow-left class="size-4" />
            Volver al inicio de sesión
        </x-ui.button>
    </x-slot:footerLink>
</x-layouts.auth>
