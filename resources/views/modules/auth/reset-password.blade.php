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

        {{-- Campo contraseña con checklist de requisitos --}}
        <div x-data="{ show: false, pw: {{ Js::from(old('password', '')) }} }" class="grid gap-1.5">
            <x-ui.label for="password">Nueva contraseña</x-ui.label>

            <x-ui.input-group :class="$errors->has('password') ? 'border-destructive-border ring-2 ring-destructive/20' : ''">
                <x-ui.input-group.input
                    id="password"
                    name="password"
                    x-bind:type="show ? 'text' : 'password'"
                    x-model="pw"
                    autocomplete="new-password"
                    :aria-invalid="$errors->has('password') ? 'true' : null"
                />
                <x-ui.input-group.button type="button" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                    <x-lucide-eye     x-show="!show"        class="size-4" />
                    <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
                </x-ui.input-group.button>
            </x-ui.input-group>

            <ul class="mt-0.5 space-y-1.5">
                @php
                    $rules = [
                        ['expr' => 'pw.length >= 8',                                      'label' => 'Mínimo 8 caracteres'],
                        ['expr' => '/[a-z]/.test(pw) && /[A-Z]/.test(pw)',                'label' => 'Mayúsculas y minúsculas'],
                        ['expr' => '/[0-9]/.test(pw)',                                    'label' => 'Al menos un número'],
                        ['expr' => '/[^a-zA-Z0-9\s]/.test(pw)',                          'label' => 'Al menos un símbolo'],
                    ];
                @endphp

                @foreach ($rules as $rule)
                    <li class="flex items-center gap-2 text-xs transition-colors"
                        :class="{{ $rule['expr'] }} ? 'text-success' : 'text-muted-foreground'">
                        <x-lucide-circle-check x-show="{{ $rule['expr'] }}"  class="size-3.5 shrink-0" />
                        <x-lucide-circle       x-show="!({{ $rule['expr'] }})" x-cloak class="size-3.5 shrink-0" />
                        {{ $rule['label'] }}
                    </li>
                @endforeach

                @foreach ($errors->get('password') as $msg)
                    @if (str_contains($msg, 'filtración'))
                        {{-- Solo mostramos el error de contraseña filtrada (uncompromised), el checklist cubre el resto --}}
                        <li class="flex items-center gap-2 text-xs text-destructive">
                            <x-lucide-circle-x class="size-3.5 shrink-0" />
                            {{ $msg }}
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <x-ui.form-field for="password_confirmation" :state="$errors->has('password_confirmation') ? 'destructive' : null" :message="$errors->first('password_confirmation')">
            <x-ui.label for="password_confirmation">Confirmá la contraseña</x-ui.label>
            <x-ui.input-group x-data="{ show: false }">
                <x-ui.input-group.input
                    id="password_confirmation"
                    name="password_confirmation"
                    x-bind:type="show ? 'text' : 'password'"
                    autocomplete="new-password"
                    :value="old('password_confirmation')"
                    :aria-invalid="$errors->has('password_confirmation') ? 'true' : null"
                />
                <x-ui.input-group.button type="button" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                    <x-lucide-eye     x-show="!show"        class="size-4" />
                    <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
                </x-ui.input-group.button>
            </x-ui.input-group>
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
