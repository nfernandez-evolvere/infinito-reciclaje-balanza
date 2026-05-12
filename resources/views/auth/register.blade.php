<x-layouts.auth title="Crear cuenta">

    <x-ui.card
        x-data="{
            loading: false,
            showPassword: false,
            showConfirm: false,
            password: '',
            strength: 0,
            checkStrength() {
                let s = 0
                if (this.password.length >= 8)          s++
                if (/[A-Z]/.test(this.password))        s++
                if (/[0-9]/.test(this.password))        s++
                if (/[^a-zA-Z0-9]/.test(this.password)) s++
                this.strength = s
            },
            strengthLabel() {
                return ['', 'Muy débil', 'Débil', 'Buena', 'Fuerte'][this.strength]
            },
            submit() {
                this.loading = true
                setTimeout(() => { this.loading = false }, 2000)
            }
        }"
    >

        <x-ui.card.header class="text-center items-center">
            <x-ui.typography as="h1">Crear cuenta</x-ui.typography>
            <x-ui.typography as="muted" class="max-w-prose">Completá tus datos para comenzar.</x-ui.typography>
        </x-ui.card.header>

        <x-ui.card.content class="space-y-4">

            {{-- Alert de validación --}}
            <x-ui.alert state="destructive">
                <x-lucide-circle-x class="size-4" />
                <x-ui.alert.title>Corregí los siguientes errores</x-ui.alert.title>
                <x-ui.alert.description>
                    <ul class="mt-1 list-disc pl-4 space-y-1">
                        <li>El email ya está en uso por otra cuenta.</li>
                        <li>La contraseña debe tener al menos 8 caracteres.</li>
                        <li>Debés aceptar los términos y condiciones.</li>
                    </ul>
                </x-ui.alert.description>
            </x-ui.alert>

            <form @submit.prevent="submit" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <x-ui.form-field for="nombre">
                        <x-ui.label for="nombre">Nombre</x-ui.label>
                        <x-ui.input id="nombre" name="nombre" type="text" autocomplete="given-name" placeholder="Juan" />
                    </x-ui.form-field>
                    <x-ui.form-field for="apellido">
                        <x-ui.label for="apellido">Apellido</x-ui.label>
                        <x-ui.input id="apellido" name="apellido" type="text" autocomplete="family-name" placeholder="García" />
                    </x-ui.form-field>
                </div>

                <x-ui.form-field for="email" state="destructive" message="Este email ya está registrado en otra cuenta.">
                    <x-ui.label for="email" state="destructive">Email</x-ui.label>
                    <x-ui.input
                        id="email" name="email" type="email"
                        state="destructive" value="juan@ejemplo.com"
                        autocomplete="email" placeholder="tu@email.com"
                    />
                </x-ui.form-field>

                <div class="grid gap-1.5">
                    <x-ui.form-field for="password" state="destructive" message="La contraseña debe tener al menos 8 caracteres.">
                        <x-ui.label for="password" state="destructive">Contraseña</x-ui.label>
                        <x-ui.input
                            id="password" name="password"
                            x-bind:type="showPassword ? 'text' : 'password'"
                            x-model="password" @input="checkStrength"
                            state="destructive" autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        >
                            <x-slot:trailing>
                                <button type="button" @click="showPassword = !showPassword"
                                    class="pointer-events-auto text-muted-foreground hover:text-foreground transition-colors"
                                    :aria-label="showPassword ? 'Ocultar' : 'Mostrar'">
                                    <x-lucide-eye x-show="!showPassword" class="size-4" />
                                    <x-lucide-eye-off x-show="showPassword" x-cloak class="size-4" />
                                </button>
                            </x-slot:trailing>
                        </x-ui.input>
                    </x-ui.form-field>

                    <div x-show="password.length > 0" x-cloak class="space-y-1">
                        <div class="flex gap-1">
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="strength >= 1 ? 'bg-destructive' : 'bg-muted'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="strength >= 2 ? 'bg-warning' : 'bg-muted'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="strength >= 3 ? 'bg-success' : 'bg-muted'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors duration-300" :class="strength >= 4 ? 'bg-success' : 'bg-muted'"></div>
                        </div>
                        <p class="text-xs text-muted-foreground" x-text="strengthLabel()"></p>
                    </div>
                </div>

                <x-ui.form-field for="password_confirm">
                    <x-ui.label for="password_confirm">Confirmar contraseña</x-ui.label>
                    <x-ui.input
                        id="password_confirm" name="password_confirmation"
                        x-bind:type="showConfirm ? 'text' : 'password'"
                        autocomplete="new-password" placeholder="Repetí tu contraseña"
                    >
                        <x-slot:trailing>
                            <button type="button" @click="showConfirm = !showConfirm"
                                class="pointer-events-auto text-muted-foreground hover:text-foreground transition-colors"
                                :aria-label="showConfirm ? 'Ocultar' : 'Mostrar'">
                                <x-lucide-eye x-show="!showConfirm" class="size-4" />
                                <x-lucide-eye-off x-show="showConfirm" x-cloak class="size-4" />
                            </button>
                        </x-slot:trailing>
                    </x-ui.input>
                </x-ui.form-field>

                <label class="flex items-start gap-3 cursor-pointer select-none">
                    <x-ui.checkbox name="terms" state="destructive" class="mt-0.5 shrink-0" />
                    <span class="text-sm text-muted-foreground leading-relaxed">
                        Acepto los
                        <a href="#" class="text-foreground underline underline-offset-4 hover:text-primary transition-colors">términos y condiciones</a>
                        y la
                        <a href="#" class="text-foreground underline underline-offset-4 hover:text-primary transition-colors">política de privacidad</a>.
                    </span>
                </label>

                <x-ui.button type="submit" class="w-full" x-bind:disabled="loading">
                    <x-ui.spinner x-show="loading" x-cloak size="sm" class="text-primary-foreground" />
                    <span x-text="loading ? 'Creando cuenta...' : 'Crear cuenta'">Crear cuenta</span>
                </x-ui.button>
            </form>

        </x-ui.card.content>

        <x-ui.card.footer class="justify-center">
            <p class="text-sm text-muted-foreground">
                ¿Ya tenés cuenta?
                <a href="/examples/auth/login" class="font-medium text-foreground underline underline-offset-4 hover:text-primary transition-colors">
                    Iniciar sesión
                </a>
            </p>
        </x-ui.card.footer>

    </x-ui.card>

</x-layouts.auth>
