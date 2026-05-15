<x-layouts.auth
    title="Recuperar contraseña"
    cardTitle="Recuperar contraseña"
    cardDescription="Ingresá tu correo y te enviamos un enlace para restablecer tu contraseña."
>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        @if (session('status'))
            <x-ui.alert state="success">
                <x-lucide-circle-check class="size-4" />
                <x-ui.alert.title>Enlace enviado</x-ui.alert.title>
                <x-ui.alert.description>{{ session('status') }}</x-ui.alert.description>
            </x-ui.alert>
        @endif

        <x-ui.form-field for="email" :state="$errors->has('email') ? 'destructive' : null" :message="$errors->first('email')">
            <x-ui.label for="email">Correo electrónico</x-ui.label>
            <x-ui.input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nombre@ejemplo.com"
                autofocus
                autocomplete="username"
            />
        </x-ui.form-field>

        <x-ui.button type="submit" class="w-full">
            Enviar enlace
        </x-ui.button>
    </form>

    <x-slot:footerLink>
        <x-ui.button variant="link" href="{{ route('login') }}">
            <x-lucide-arrow-left class="size-4" />
            Volver al inicio de sesión
        </x-ui.button>
    </x-slot:footerLink>
</x-layouts.auth>
