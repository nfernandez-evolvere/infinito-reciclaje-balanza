<div class="space-y-6 pt-4">

    {{-- Avatar --}}
    <x-ui.card>
        <x-ui.card.content class="p-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                <div class="relative inline-flex shrink-0">
                    <x-ui.avatar alt="Nicolás Fernández" size="xl" />
                    <button
                        type="button"
                        class="absolute -bottom-1 -right-1 size-7 rounded-full border-2 border-background bg-primary text-primary-foreground flex items-center justify-center hover:bg-primary/90 transition-colors"
                        aria-label="Cambiar foto"
                    >
                        <x-lucide-camera class="size-3.5" />
                    </button>
                </div>
                <div class="space-y-1">
                    <x-ui.typography as="small" element="p">Foto de perfil</x-ui.typography>
                    <x-ui.typography as="muted" class="text-xs">JPG, GIF o PNG. Máx. 1 MB.</x-ui.typography>
                    <div class="flex gap-2 pt-1">
                        <x-ui.button variant="outline" size="sm">Cambiar foto</x-ui.button>
                        <x-ui.button variant="ghost" size="sm" state="destructive">Eliminar</x-ui.button>
                    </div>
                </div>
            </div>
        </x-ui.card.content>
    </x-ui.card>

    {{-- Datos personales --}}
    <x-ui.card>
        <x-ui.card.header>
            <x-ui.card.title>Información personal</x-ui.card.title>
            <x-ui.card.description>Actualizá tu nombre y datos de contacto.</x-ui.card.description>
        </x-ui.card.header>
        <x-ui.card.content class="space-y-4">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.form-field for="p-nombre">
                    <x-ui.label for="p-nombre">Nombre</x-ui.label>
                    <x-ui.input id="p-nombre" name="nombre" value="Nicolás" />
                </x-ui.form-field>
                <x-ui.form-field for="p-apellido">
                    <x-ui.label for="p-apellido">Apellido</x-ui.label>
                    <x-ui.input id="p-apellido" name="apellido" value="Fernández" />
                </x-ui.form-field>
            </div>

            <x-ui.form-field for="p-email">
                <x-ui.label for="p-email">Email</x-ui.label>
                <x-ui.input id="p-email" name="email" type="email" value="nico@ejemplo.com">
                    <x-slot:leading><x-lucide-mail class="size-full" /></x-slot:leading>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field for="p-bio">
                <x-ui.label for="p-bio">Bio <span class="font-normal text-muted-foreground">(opcional)</span></x-ui.label>
                <x-ui.textarea id="p-bio" name="bio" rows="3" placeholder="Contá algo sobre vos...">Desarrollador apasionado por el diseño de sistemas.</x-ui.textarea>
            </x-ui.form-field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.form-field>
                    <x-ui.label>Zona horaria</x-ui.label>
                    <x-ui.select name="timezone" value="America/Argentina/Buenos_Aires">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Seleccioná zona horaria" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="America/Argentina/Buenos_Aires">Buenos Aires (GMT-3)</x-ui.select.item>
                            <x-ui.select.item value="America/Santiago">Santiago (GMT-4)</x-ui.select.item>
                            <x-ui.select.item value="America/Bogota">Bogotá (GMT-5)</x-ui.select.item>
                            <x-ui.select.item value="America/Mexico_City">Ciudad de México (GMT-6)</x-ui.select.item>
                            <x-ui.select.item value="Europe/Madrid">Madrid (GMT+1)</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>

                <x-ui.form-field>
                    <x-ui.label>Idioma</x-ui.label>
                    <x-ui.select name="language" value="es">
                        <x-ui.select.trigger>
                            <x-ui.select.value placeholder="Seleccioná idioma" />
                        </x-ui.select.trigger>
                        <x-ui.select.content>
                            <x-ui.select.item value="es">Español</x-ui.select.item>
                            <x-ui.select.item value="en">English</x-ui.select.item>
                            <x-ui.select.item value="pt">Português</x-ui.select.item>
                        </x-ui.select.content>
                    </x-ui.select>
                </x-ui.form-field>
            </div>

        </x-ui.card.content>
        <x-ui.card.footer class="justify-end">
            <x-ui.button @click="$store.toast.success('Perfil actualizado')">
                Guardar cambios
            </x-ui.button>
        </x-ui.card.footer>
    </x-ui.card>

</div>
