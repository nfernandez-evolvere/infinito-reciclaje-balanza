<x-layouts.auth
    title="Ingresar"
    cardTitle="Ingresá a tu cuenta"
    cardDescription="Usá tu correo y contraseña para continuar"
>
    <div class="space-y-2">
        @if (!empty($usuariosPrueba) && $usuariosPrueba->isNotEmpty())
            <x-ui.alert state="info">
                <x-lucide-info class="size-3.5" />
                <x-ui.alert.title>Usuarios de prueba</x-ui.alert.title>
                <x-ui.alert.description class="flex flex-col gap-0.5 text-xs">
                    @foreach ($usuariosPrueba as $u)
                        @php $label = match($u->role) { 'super_admin' => 'Super Admin', 'admin' => 'Admin', default => 'Operador' } @endphp
                        <span><b>{{ $label }}</b> — {{ $u->email }} · pass: 1234</span>
                    @endforeach
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-2">
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
                    placeholder="nombre@ejemplo.com"
                    autofocus
                    autocomplete="username"
                />
            </x-ui.form-field>

            <x-ui.form-field for="password">
                <div class="flex items-center justify-between">
                    <x-ui.label for="password">Contraseña</x-ui.label>
                    <a href="{{ route('password.request') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <x-ui.input-group x-data="{ show: false }">
                    <x-ui.input-group.input
                        id="password"
                        name="password"
                        x-bind:type="show ? 'text' : 'password'"
                        autocomplete="current-password"
                    />
                    <x-ui.input-group.button type="button" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                        <x-lucide-eye     x-show="!show"         class="size-4" />
                        <x-lucide-eye-off x-show="show" x-cloak  class="size-4" />
                    </x-ui.input-group.button>
                </x-ui.input-group>
            </x-ui.form-field>

            <x-ui.button type="submit" class="w-full">
                Ingresar
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
