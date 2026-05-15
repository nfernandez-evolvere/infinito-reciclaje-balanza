<x-layouts.auth title="Ingresar">
    <div class="w-full max-w-100 space-y-8">

        {{-- Brand --}}
        <div class="flex flex-col items-center gap-4">
            <div class="flex items-center justify-center size-14 rounded-xl bg-primary shrink-0">
                <span class="text-xl font-bold text-primary-foreground leading-none">IR</span>
            </div>
            <div class="text-center space-y-1">
                <x-ui.typography as="h3" element="h1">Infinito Reciclaje</x-ui.typography>
                <x-ui.typography as="muted">Sistema de gestión de balanza</x-ui.typography>
            </div>
        </div>

        {{-- Form card --}}
        <x-ui.card>
            <x-ui.card.header class="text-center">
                <x-ui.card.title>Ingresá a tu cuenta</x-ui.card.title>
                <x-ui.card.description>Usá tu correo y contraseña para continuar</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    @if ($errors->any())
                        <x-ui.alert state="destructive">
                            <x-lucide-circle-alert class="size-4" />
                            <x-ui.alert.title>No pudimos iniciar sesión</x-ui.alert.title>
                            <x-ui.alert.description>
                                Verificá tu correo y contraseña e intentá de nuevo.
                            </x-ui.alert.description>
                        </x-ui.alert>
                    @endif

                    <x-ui.form-field for="email">
                        <x-ui.label for="email">Correo electrónico</x-ui.label>
                        <x-ui.input
                            id="email"
                            type="email"
                            name="email"
                            :value="old('email')"
                            :error="$errors->has('email')"
                            placeholder="nombre@ejemplo.com"
                            autofocus
                            autocomplete="username"
                        />
                    </x-ui.form-field>

                    <x-ui.form-field for="password">
                        <x-ui.label for="password">Contraseña</x-ui.label>
                        <x-ui.input
                            id="password"
                            type="password"
                            name="password"
                            :error="$errors->has('password')"
                            autocomplete="current-password"
                        />
                    </x-ui.form-field>

                    <x-ui.button type="submit" class="w-full">
                        Ingresar
                    </x-ui.button>
                </form>
            </x-ui.card.content>
        </x-ui.card>

        {{-- Tipos de usuario (referencia para entornos de desarrollo) --}}
        <x-ui.alert state="info">
            <x-lucide-info class="size-4" />
            <x-ui.alert.title>Usuarios de prueba</x-ui.alert.title>
            <x-ui.alert.description>
                <x-ui.typography as="span" class="text-sm"><b>Admin</b> — nacho@balanza.test - pass: password</x-ui.typography>
                <x-ui.typography as="span" class="text-sm"><b>Operador</b> — roberto@balanza.test</x-ui.typography>
                <x-ui.typography as="span" class="text-sm"><b>Contraseña</b> — password</x-ui.typography>
            </x-ui.alert.description>
        </x-ui.alert>

        <x-ui.typography as="muted" class="text-center text-xs">
            Balanza v1 · Gestión de pesajes
        </x-ui.typography>

    </div>
</x-layouts.auth>
