<x-layouts.auth
    title="Ingresar"
    cardTitle="Ingresá a tu cuenta"
    cardDescription="Ingresá tu correo para continuar"
>
    <div
        class="space-y-2"
        x-data="{
            emailVal:    @js(old('email', '')),
            orgs:        [],
            orgId:       null,
            isSuperAdmin: false,
            loading:     false,
            fetched:     false,
            _t:          null,

            init() {
                if (this._emailOk(this.emailVal)) this._fetch();
                this.$watch('emailVal', v => {
                    this.orgs        = [];
                    this.orgId       = null;
                    this.isSuperAdmin = false;
                    this.fetched     = false;
                    clearTimeout(this._t);
                    if (!this._emailOk(v)) return;
                    this._t = setTimeout(() => this._fetch(), 400);
                });
            },

            _emailOk(v) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test((v ?? '').trim());
            },

            async _fetch() {
                if (!this._emailOk(this.emailVal)) return;
                this.loading = true;
                try {
                    const r = await fetch(
                        '/login/organizaciones?email=' + encodeURIComponent(this.emailVal.trim()),
                        { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                    );
                    if (r.ok) {
                        const d = await r.json();
                        this.isSuperAdmin = d.super_admin ?? false;
                        this.orgs         = d.orgs ?? [];
                    }
                } catch (e) {
                } finally {
                    this.loading  = false;
                    this.fetched  = true;
                }
            },
        }"
    >
        @if (!empty($usuariosPrueba) && $usuariosPrueba->isNotEmpty())
            <x-ui.alert state="info">
                <x-lucide-info class="size-3.5" />
                <x-ui.alert.title>Usuarios de prueba</x-ui.alert.title>
                <x-ui.alert.description class="flex flex-col gap-0.5 text-xs">
                    @foreach ($usuariosPrueba as $u)
                        @php $label = match($u->role) { 'super_admin' => 'Super Admin', 'admin' => 'Admin', default => 'Operador' } @endphp
                        <button
                            type="button"
                            class="text-left hover:text-foreground transition-colors"
                            @click="emailVal = @js($u->email)"
                        ><b>{{ $label }}</b> — {{ $u->email }} · pass: 1234</button>
                    @endforeach
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        @if ($errors->any())
            <x-ui.alert state="destructive">
                <x-lucide-circle-alert class="size-4" />
                <x-ui.alert.title>No pudimos iniciar sesión</x-ui.alert.title>
                <x-ui.alert.description>
                    Verificá la organización, correo y contraseña e intentá de nuevo.
                </x-ui.alert.description>
            </x-ui.alert>
        @endif

        <form
            method="POST"
            action="{{ route('login') }}"
            class="space-y-3"
            x-on:change="if ($event.detail && 'value' in $event.detail) orgId = $event.detail.value"
        >
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

            {{-- Loading --}}
            <div x-show="loading" x-cloak class="flex items-center gap-2 text-sm text-muted-foreground">
                <x-lucide-loader-circle class="size-4 animate-spin" />
                <span>Buscando...</span>
            </div>

            {{-- Org combobox --}}
            <x-ui.form-field
                x-show="_emailOk(emailVal) && !isSuperAdmin && !loading && orgs.length > 0"
                x-cloak
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
                                    @click="select(String(org.id))"
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
            <div
                x-show="isSuperAdmin && !loading"
                x-cloak
                class="flex items-center gap-2 rounded-md bg-muted px-3 py-2 text-sm text-muted-foreground"
            >
                <x-lucide-shield class="size-4 shrink-0" />
                <span>Administración del sistema</span>
            </div>

            {{-- No results --}}
            <div
                x-show="fetched && !loading && !isSuperAdmin && orgs.length === 0"
                x-cloak
                class="text-sm text-muted-foreground"
            >No se encontraron organizaciones para este correo.</div>

            {{-- Password --}}
            <x-ui.form-field for="password" x-show="_emailOk(emailVal) && !loading && (isSuperAdmin || orgId !== null)" x-cloak>
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
                        <x-lucide-eye     x-show="!show"        class="size-4" />
                        <x-lucide-eye-off x-show="show" x-cloak class="size-4" />
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
