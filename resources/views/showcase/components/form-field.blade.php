<x-layouts.showcase title="Form Field — Showcase">
<div class="mx-auto max-w-[--container-md] px-4 sm:px-6 lg:px-8 py-10 space-y-12">

    <div>
        <x-ui.typography as="h1">Form Field</x-ui.typography>
        <x-ui.typography as="muted" class="mt-1 max-w-prose">
            Wrapper de composición que agrupa <x-ui.typography as="code">label</x-ui.typography>,
            input y <x-ui.typography as="code">helper-text</x-ui.typography> bajo un estado semántico compartido.
            El estado se propaga a todos los sub-componentes desde un único punto de control.
        </x-ui.typography>
    </div>

    {{-- Anatomía --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Anatomía</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Un <x-ui.typography as="code">form-field</x-ui.typography> tiene tres capas: label (opcional), control (input, select, textarea, etc.) y helper-text. El <x-ui.typography as="code">gap-1.5</x-ui.typography> entre capas es fijo.
        </x-ui.typography>

        <div class="max-w-sm space-y-6">

            {{-- Solo label + input --}}
            <div>
                <x-ui.typography as="small" element="p" class="text-muted-foreground mb-2">Label + Input</x-ui.typography>
                <x-ui.form-field for="email-base">
                    <x-ui.label for="email-base">Correo electrónico</x-ui.label>
                    <x-ui.input id="email-base" type="email" placeholder="nombre@ejemplo.com" />
                </x-ui.form-field>
            </div>

            {{-- Con helper-text neutral --}}
            <div>
                <x-ui.typography as="small" element="p" class="text-muted-foreground mb-2">Label + Input + Helper text</x-ui.typography>
                <x-ui.form-field for="email-helper" message="Usaremos este email para notificaciones.">
                    <x-ui.label for="email-helper">Correo electrónico</x-ui.label>
                    <x-ui.input id="email-helper" type="email" placeholder="nombre@ejemplo.com" />
                </x-ui.form-field>
            </div>

        </div>
    </section>

    <x-ui.separator />

    {{-- Estados semánticos --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Estados semánticos</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            El prop <x-ui.typography as="code">state</x-ui.typography> colorea el ícono del helper-text y debe pasarse también al control interior para que coloree el borde.
        </x-ui.typography>

        <div class="max-w-sm space-y-5">

            <x-ui.form-field
                state="destructive"
                message="El correo ya está registrado."
                for="email-error"
            >
                <x-ui.label for="email-error">Correo electrónico</x-ui.label>
                <x-ui.input id="email-error" type="email" state="destructive" value="ocupado@ejemplo.com" />
            </x-ui.form-field>

            <x-ui.form-field
                state="success"
                message="Correo disponible."
                for="email-ok"
            >
                <x-ui.label for="email-ok">Correo electrónico</x-ui.label>
                <x-ui.input id="email-ok" type="email" state="success" value="libre@ejemplo.com" />
            </x-ui.form-field>

            <x-ui.form-field
                state="warning"
                message="Revisá que el dominio sea correcto."
                for="email-warn"
            >
                <x-ui.label for="email-warn">Correo electrónico</x-ui.label>
                <x-ui.input id="email-warn" type="email" state="warning" value="usuario@raro" />
            </x-ui.form-field>

            <x-ui.form-field
                state="info"
                message="Se enviará un código de verificación a este correo."
                for="email-info"
            >
                <x-ui.label for="email-info">Correo electrónico</x-ui.label>
                <x-ui.input id="email-info" type="email" state="info" placeholder="nombre@ejemplo.com" />
            </x-ui.form-field>

        </div>
    </section>

    <x-ui.separator />

    {{-- Con distintos controles --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Con distintos controles</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            <x-ui.typography as="code">form-field</x-ui.typography> funciona con cualquier control: input, textarea, select, native-select, combobox, checkbox, etc.
        </x-ui.typography>

        <div class="max-w-sm space-y-5">

            {{-- Textarea --}}
            <x-ui.form-field
                for="bio"
                message="Máximo 160 caracteres."
            >
                <x-ui.label for="bio">Biografía</x-ui.label>
                <x-ui.textarea id="bio" placeholder="Contá algo sobre vos..." rows="3" />
            </x-ui.form-field>

            {{-- Native select --}}
            <x-ui.form-field for="pais" message="Se usa para ajustar el idioma de la interfaz.">
                <x-ui.label for="pais">País</x-ui.label>
                <x-ui.native-select id="pais">
                    <option value="">Seleccioná un país</option>
                    <option value="ar">Argentina</option>
                    <option value="mx">México</option>
                    <option value="es">España</option>
                    <option value="cl">Chile</option>
                </x-ui.native-select>
            </x-ui.form-field>

            {{-- Checkbox --}}
            <x-ui.form-field>
                <label class="flex items-start gap-3 cursor-pointer">
                    <x-ui.checkbox id="terminos" class="mt-0.5" />
                    <div class="space-y-0.5">
                        <x-ui.typography as="small" element="p">Acepto los términos y condiciones</x-ui.typography>
                        <x-ui.typography as="muted">Al continuar aceptás nuestra política de privacidad.</x-ui.typography>
                    </div>
                </label>
            </x-ui.form-field>

            {{-- Switch --}}
            <x-ui.form-field>
                <div class="flex items-center justify-between gap-4">
                    <div class="space-y-0.5">
                        <x-ui.label for="notifs">Notificaciones por email</x-ui.label>
                        <x-ui.typography as="muted">Recibí un resumen semanal de actividad.</x-ui.typography>
                    </div>
                    <x-ui.switch id="notifs" />
                </div>
            </x-ui.form-field>

        </div>
    </section>

    <x-ui.separator />

    {{-- Formulario de registro completo --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Ejemplo — Formulario de registro</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Formulario con grilla de 2 columnas en desktop, validación con estados semánticos y acciones responsive.
        </x-ui.typography>

        <x-ui.card class="max-w-lg">
            <x-ui.card.header>
                <x-ui.card.title>Crear cuenta</x-ui.card.title>
                <x-ui.card.description>Completá tus datos para comenzar.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content class="pt-0">
                <form class="space-y-5">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.form-field for="nombre">
                            <x-ui.label for="nombre">Nombre</x-ui.label>
                            <x-ui.input id="nombre" placeholder="Juan" />
                        </x-ui.form-field>

                        <x-ui.form-field for="apellido" state="destructive" message="El apellido es requerido.">
                            <x-ui.label for="apellido">Apellido</x-ui.label>
                            <x-ui.input id="apellido" state="destructive" />
                        </x-ui.form-field>
                    </div>

                    <x-ui.form-field for="reg-email" state="success" message="Este correo está disponible.">
                        <x-ui.label for="reg-email">Correo electrónico</x-ui.label>
                        <x-ui.input id="reg-email" type="email" state="success" value="juan@ejemplo.com" />
                    </x-ui.form-field>

                    <x-ui.form-field for="reg-pass" message="Mínimo 8 caracteres, una mayúscula y un número.">
                        <x-ui.label for="reg-pass">Contraseña</x-ui.label>
                        <div x-data="{ show: false }">
                            <x-ui.input id="reg-pass" x-bind:type="show ? 'text' : 'password'" placeholder="••••••••">
                                <x-slot:trailing>
                                    <button
                                        type="button"
                                        @click="show = !show"
                                        class="pointer-events-auto text-muted-foreground hover:text-foreground transition-colors"
                                    >
                                        <x-lucide-eye x-show="!show" class="size-4" />
                                        <x-lucide-eye-off x-show="show" class="size-4" x-cloak />
                                    </button>
                                </x-slot:trailing>
                            </x-ui.input>
                        </div>
                    </x-ui.form-field>

                    <x-ui.form-field>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <x-ui.checkbox id="reg-terminos" class="mt-0.5" />
                            <x-ui.typography as="muted">
                                Acepto los <a href="#" class="text-primary underline underline-offset-4">términos y condiciones</a> y la política de privacidad.
                            </x-ui.typography>
                        </label>
                    </x-ui.form-field>

                    <div class="flex flex-col sm:flex-row-reverse gap-2 pt-1">
                        <x-ui.button type="submit" class="w-full sm:w-auto">Crear cuenta</x-ui.button>
                        <x-ui.button variant="outline" class="w-full sm:w-auto">Cancelar</x-ui.button>
                    </div>

                </form>
            </x-ui.card.content>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Formulario de perfil --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Ejemplo — Sección de perfil</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            Patrón de settings con separador entre secciones y acciones al pie.
        </x-ui.typography>

        <x-ui.card class="max-w-lg">
            <x-ui.card.header>
                <x-ui.card.title>Información personal</x-ui.card.title>
                <x-ui.card.description>Actualizá tus datos de perfil.</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content class="pt-0 space-y-6">

                {{-- Avatar --}}
                <div class="flex items-center gap-4">
                    <x-ui.avatar class="size-16">
                        <x-ui.avatar.image src="https://i.pravatar.cc/64?img=12" alt="Avatar" />
                        <x-ui.avatar.fallback>JD</x-ui.avatar.fallback>
                    </x-ui.avatar>
                    <div class="space-y-1">
                        <x-ui.button variant="outline" size="sm">Cambiar foto</x-ui.button>
                        <x-ui.typography as="muted">JPG o PNG. Máx. 2 MB.</x-ui.typography>
                    </div>
                </div>

                <x-ui.separator />

                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <x-ui.form-field for="pf-nombre">
                            <x-ui.label for="pf-nombre">Nombre</x-ui.label>
                            <x-ui.input id="pf-nombre" value="Juan" />
                        </x-ui.form-field>
                        <x-ui.form-field for="pf-apellido">
                            <x-ui.label for="pf-apellido">Apellido</x-ui.label>
                            <x-ui.input id="pf-apellido" value="Díaz" />
                        </x-ui.form-field>
                    </div>

                    <x-ui.form-field for="pf-email" message="El email no puede cambiarse sin verificación.">
                        <x-ui.label for="pf-email">Correo electrónico</x-ui.label>
                        <x-ui.input id="pf-email" type="email" value="juan@ejemplo.com" disabled />
                    </x-ui.form-field>

                    <x-ui.form-field for="pf-bio">
                        <x-ui.label for="pf-bio">Biografía</x-ui.label>
                        <x-ui.textarea id="pf-bio" rows="3" placeholder="Contá algo sobre vos...">Desarrollador full-stack apasionado por el diseño de sistemas.</x-ui.textarea>
                    </x-ui.form-field>
                </div>

            </x-ui.card.content>
            <x-ui.card.footer class="justify-end gap-2">
                <x-ui.button variant="ghost">Descartar</x-ui.button>
                <x-ui.button>Guardar cambios</x-ui.button>
            </x-ui.card.footer>
        </x-ui.card>
    </section>

    <x-ui.separator />

    {{-- Helper Text aislado --}}
    <section class="space-y-4">
        <x-ui.typography as="section-label">Helper Text — componente aislado</x-ui.typography>
        <x-ui.typography as="muted" class="max-w-prose">
            <x-ui.typography as="code">x-ui.helper-text</x-ui.typography> puede usarse solo, fuera de un <x-ui.typography as="code">form-field</x-ui.typography>, para añadir contexto a cualquier control custom.
        </x-ui.typography>

        <div class="space-y-3 max-w-sm">
            <x-ui.helper-text message="Sin estado — texto de ayuda neutral." />
            <x-ui.helper-text state="destructive" message="Este campo es requerido." />
            <x-ui.helper-text state="success" message="Valor válido." />
            <x-ui.helper-text state="warning" message="Revisá este dato antes de continuar." />
            <x-ui.helper-text state="info" message="Este campo se sincroniza automáticamente." />
        </div>
    </section>

</div>
</x-layouts.showcase>
