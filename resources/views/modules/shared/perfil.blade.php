<x-layouts.app title="Mi perfil">

    <div class="max-w-2xl space-y-6">

        <div>
            <h1 class="text-h2">Mi perfil</h1>
            <p class="text-lead mt-1">Administrá tu nombre y contraseña de acceso.</p>
        </div>

        {{-- Datos personales --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Datos personales</x-ui.card.title>
                <x-ui.card.description>Actualizá tu nombre visible en el sistema.</x-ui.card.description>
            </x-ui.card.header>

            <form method="POST" action="{{ route('perfil.update') }}" class="space-y-2">
                @csrf
                @method('PUT')

                <x-ui.form-field
                    for="name"
                    :state="$errors->updateProfile->has('name') ? 'destructive' : null"
                    :message="$errors->updateProfile->first('name')"
                >
                    <x-ui.label for="name">Nombre</x-ui.label>
                    <x-ui.input
                        id="name"
                        name="name"
                        :value="old('name', auth()->user()->name)"
                        :state="$errors->updateProfile->has('name') ? 'destructive' : null"
                        autocomplete="name"
                    />
                </x-ui.form-field>

                <x-ui.form-field for="email" message="El email no se puede modificar.">
                    <x-ui.label for="email">Email</x-ui.label>
                    <x-ui.input
                        id="email"
                        type="email"
                        :value="auth()->user()->email"
                        disabled
                    />
                </x-ui.form-field>

                <div class="flex justify-end">
                    <x-ui.button type="submit">Guardar cambios</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- Cambiar contraseña --}}
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Cambiar contraseña</x-ui.card.title>
                <x-ui.card.description>Usá una contraseña larga que no uses en otros sitios.</x-ui.card.description>
            </x-ui.card.header>

            <form method="POST" action="{{ route('perfil.password') }}" class="space-y-2">
                @csrf
                @method('PUT')

                {{-- Contraseña actual --}}
                <x-ui.form-field
                    for="current_password"
                    :state="$errors->updatePassword->has('current_password') ? 'destructive' : null"
                    :message="$errors->updatePassword->first('current_password')"
                >
                    <x-ui.label for="current_password">Contraseña actual</x-ui.label>
                    <x-ui.input-group
                        x-data="{ show: false }"
                        :class="$errors->updatePassword->has('current_password') ? 'border-destructive-border ring-2 ring-destructive/20' : ''"
                    >
                        <x-ui.input-group.input
                            id="current_password"
                            name="current_password"
                            x-bind:type="show ? 'text' : 'password'"
                            autocomplete="current-password"
                            :aria-invalid="$errors->updatePassword->has('current_password') ? 'true' : null"
                        />
                        <x-ui.input-group.button type="button" class="rounded-full h-8 w-8" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                            <x-lucide-eye     x-show="!show"        class="size-4" />
                            <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
                        </x-ui.input-group.button>
                    </x-ui.input-group>
                </x-ui.form-field>

                {{-- Nueva contraseña con checklist de requisitos --}}
                <div x-data="{ show: false, pw: {{ Js::from(old('password', '')) }} }" class="grid gap-1.5">
                    <x-ui.label for="password">Nueva contraseña</x-ui.label>

                    <x-ui.input-group :class="$errors->updatePassword->has('password') ? 'border-destructive-border ring-2 ring-destructive/20' : ''">
                        <x-ui.input-group.input
                            id="password"
                            name="password"
                            x-bind:type="show ? 'text' : 'password'"
                            x-model="pw"
                            autocomplete="new-password"
                            :aria-invalid="$errors->updatePassword->has('password') ? 'true' : null"
                        />
                        <x-ui.input-group.button type="button" class="rounded-full h-8 w-8" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                            <x-lucide-eye     x-show="!show"        class="size-4" />
                            <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
                        </x-ui.input-group.button>
                    </x-ui.input-group>

                    <ul class="mt-0.5 space-y-1.5">
                        @php
                            $rules = [
                                ['expr' => 'pw.length >= 8',                       'label' => 'Mínimo 8 caracteres'],
                                ['expr' => '/[a-z]/.test(pw) && /[A-Z]/.test(pw)', 'label' => 'Mayúsculas y minúsculas'],
                                ['expr' => '/[0-9]/.test(pw)',                     'label' => 'Al menos un número'],
                                ['expr' => '/[^a-zA-Z0-9\s]/.test(pw)',            'label' => 'Al menos un símbolo'],
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

                        @foreach ($errors->updatePassword->get('password') as $msg)
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

                {{-- Confirmar nueva contraseña --}}
                <x-ui.form-field
                    for="password_confirmation"
                    :state="$errors->updatePassword->has('password') ? 'destructive' : null"
                >
                    <x-ui.label for="password_confirmation">Confirmar nueva contraseña</x-ui.label>
                    <x-ui.input-group
                        x-data="{ show: false }"
                        :class="$errors->updatePassword->has('password') ? 'border-destructive-border ring-2 ring-destructive/20' : ''"
                    >
                        <x-ui.input-group.input
                            id="password_confirmation"
                            name="password_confirmation"
                            x-bind:type="show ? 'text' : 'password'"
                            autocomplete="new-password"
                            :aria-invalid="$errors->updatePassword->has('password') ? 'true' : null"
                        />
                        <x-ui.input-group.button type="button" class="rounded-full h-8 w-8" @click="show = !show" tabindex="-1" aria-label="Mostrar u ocultar contraseña">
                            <x-lucide-eye     x-show="!show"        class="size-4" />
                            <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
                        </x-ui.input-group.button>
                    </x-ui.input-group>
                </x-ui.form-field>

                <div class="flex justify-end">
                    <x-ui.button type="submit">Cambiar contraseña</x-ui.button>
                </div>
            </form>
        </x-ui.card>

    </div>

</x-layouts.app>
