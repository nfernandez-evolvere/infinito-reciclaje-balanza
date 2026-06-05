<x-layouts.auth
    title="Ingresar"
    cardTitle="Ingresá a tu cuenta"
    cardDescription="Ingresá tu correo para continuar"
>
    <div
        class="space-y-2"
        x-data="loginForm({ initialEmail: @js(old('email', '')) })"
    >
{{--         @if (!empty($usuariosPrueba) && $usuariosPrueba->isNotEmpty())
            <x-ui.alert state="info" title="Usuarios de prueba">
                <x-ui.alert.description class="flex flex-col gap-0.5 text-xs">
                    @foreach ($usuariosPrueba as $u)
                        @php $label = match($u->role) { 'super_admin' => 'Super Admin', 'admin' => 'Admin', default => 'Operador' } @endphp
                        <button
                            type="button"
                            class="text-left hover:text-foreground transition-colors"
                            @click="emailVal = @js($u->email)"
                        ><b>{{ $label }}</b> — {{ $u->email }} · pass: Evolvere123!@</button>
                    @endforeach
                </x-ui.alert.description>
            </x-ui.alert>
        @endif --}}

        @if ($errors->any())
            <x-ui.alert
                state="destructive"
                title="No pudimos iniciar sesión"
                description="Verificá la organización, correo y contraseña e intentá de nuevo."
                dismissible
            />
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <x-ui.form-field for="email">
                <x-ui.label for="email">Correo electrónico</x-ui.label>
                <x-ui.input
                    id="email"
                    type="email"
                    name="email"
                    x-model="emailVal"
                    placeholder="nombre@ejemplo.com"
                    autofocus
                    autocomplete="username"
                />
            </x-ui.form-field>

            {{-- Org combobox --}}
            <x-ui.form-field
                x-show="!isSuperAdmin"
                x-bind:class="!(fetched && !loading && orgs.length > 0) ? 'opacity-50 pointer-events-none' : ''"
            >
                <x-ui.label>Organización</x-ui.label>
                <x-ui.combobox
                    placeholder="Seleccioná una organización..."
                    searchPlaceholder="Buscar organización..."
                >
                    <x-ui.combobox.input required />
                    <x-ui.combobox.content>
                        <x-ui.combobox.list>
                            <template x-for="org in orgs" :key="org.id">
                                <div
                                    role="option"
                                    :id="uid + ':' + String(org.id)"
                                    :data-value="String(org.id)"
                                    :data-label="org.nombre.toLowerCase()"
                                    :aria-selected="value !== null && String(value) === String(org.id)"
                                    x-show="!search || org.nombre.toLowerCase().includes(search.toLowerCase())"
                                    @click="select(String(org.id)); window.dispatchEvent(new CustomEvent('login:org-select', { detail: { id: String(org.id) } }))"
                                    @mouseenter="highlighted = String(org.id)"
                                    :class="[
                                        _itemCls(),
                                        highlighted !== null && String(highlighted) === String(org.id) ? 'bg-accent text-accent-foreground' : '',
                                        'cursor-pointer'
                                    ]"
                                    class="relative flex items-center rounded-sm pl-8 pr-2 select-none outline-none"
                                >
                                    <span
                                        class="absolute left-2 flex size-4 items-center justify-center"
                                        x-show="value !== null && String(value) === String(org.id)"
                                    >
                                        <x-lucide-check class="size-3.5" stroke-width="2.5" />
                                    </span>
                                    <span x-text="org.nombre"></span>
                                </div>
                            </template>
                            <div
                                x-show="matchCount === 0 && search !== ''"
                                class="py-6 text-center text-sm text-muted-foreground select-none"
                            >Sin resultados.</div>
                        </x-ui.combobox.list>
                    </x-ui.combobox.content>
                </x-ui.combobox>
            </x-ui.form-field>

            {{-- Super admin indicator --}}
            <x-ui.alert
                state="info"
                icon="shield"
                title="Administración del sistema"
                x-show="isSuperAdmin && !loading"
                x-cloak
                class="mb-4"
            />

            {{-- No results --}}
            <x-ui.alert
                state="warning"
                title="Sin organizaciones"
                description="No encontramos organizaciones para este correo."
                x-show="fetched && !loading && !isSuperAdmin && orgs.length === 0"
                x-cloak
                class="mb-4"
            />

            {{-- Password --}}
            <x-ui.form-field
                for="password"
                x-bind:class="!(_emailOk(emailVal) && !loading && (isSuperAdmin || orgId !== null)) ? 'opacity-50 pointer-events-none' : ''"
            >
                <div class="flex items-center justify-between">
                    <x-ui.label for="password">Contraseña</x-ui.label>
                    <a href="{{ route('password.request') }}" class="text-sm text-muted-foreground hover:text-foreground transition-colors">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>
                <x-ui.input-group>
                    <x-ui.input-group.input
                        id="password"
                        name="password"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                    />
                    <x-ui.input-group.button 
                        type="button" @click="showPassword = !showPassword" 
                        tabindex="-1" aria-label="Mostrar u ocultar contraseña"
                        class="rounded-full h-8 w-8"
                        size="icon"
                    >
                        <x-lucide-eye     x-show="!showPassword"        class="size-4" />
                        <x-lucide-eye-off x-show="showPassword" x-cloak class="size-4" />
                    </x-ui.input-group.button>
                </x-ui.input-group>
            </x-ui.form-field>

            <input type="hidden" name="organizacion_id" x-bind:value="isSuperAdmin ? '' : (orgId ?? '')" />

            <x-ui.button type="submit" class="w-full" x-bind:disabled="!(_emailOk(emailVal) && !loading && (isSuperAdmin || orgId !== null))">
                Ingresar
            </x-ui.button>
        </form>
    </div>
</x-layouts.auth>
