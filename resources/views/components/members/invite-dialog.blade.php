{{--
Dialog de invitación de miembro.
Self-contained: tiene su propio x-data via x-ui.dialog.
El slot $trigger permite personalizar el botón de apertura;
si no se pasa, usa el botón por defecto.
--}}

<x-ui.dialog>
    <x-ui.dialog.trigger>
        @if($slot->isNotEmpty())
            {{ $slot }}
        @else
            <x-ui.button>
                <x-lucide-user-plus class="size-4" />
                Invitar miembro
            </x-ui.button>
        @endif
    </x-ui.dialog.trigger>

    <x-ui.dialog.content>
        <x-ui.dialog.header>
            <x-ui.dialog.title>Invitar miembro</x-ui.dialog.title>
            <x-ui.dialog.description>
                Enviamos un email de invitación. El miembro puede unirse en cualquier momento.
            </x-ui.dialog.description>
        </x-ui.dialog.header>

        <div class="px-6 pb-2 space-y-4">

            <div class="grid grid-cols-2 gap-3">
                <x-ui.form-field for="inv-nombre">
                    <x-ui.label for="inv-nombre">Nombre</x-ui.label>
                    <x-ui.input id="inv-nombre" placeholder="Juan" autocomplete="off" />
                </x-ui.form-field>
                <x-ui.form-field for="inv-apellido">
                    <x-ui.label for="inv-apellido">Apellido</x-ui.label>
                    <x-ui.input id="inv-apellido" placeholder="García" autocomplete="off" />
                </x-ui.form-field>
            </div>

            <x-ui.form-field for="inv-email">
                <x-ui.label for="inv-email">Email</x-ui.label>
                <x-ui.input id="inv-email" type="email" placeholder="juan@empresa.com">
                    <x-slot:leading><x-lucide-mail class="size-full" /></x-slot:leading>
                </x-ui.input>
            </x-ui.form-field>

            <x-ui.form-field>
                <x-ui.label>Rol</x-ui.label>
                <x-ui.select>
                    <x-ui.select.trigger>
                        <x-ui.select.value placeholder="Seleccioná un rol" />
                    </x-ui.select.trigger>
                    <x-ui.select.content>
                        <x-ui.select.item value="admin">Administrador</x-ui.select.item>
                        <x-ui.select.item value="editor">Editor</x-ui.select.item>
                        <x-ui.select.item value="member">Miembro</x-ui.select.item>
                    </x-ui.select.content>
                </x-ui.select>
            </x-ui.form-field>

            <x-ui.form-field for="inv-mensaje">
                <x-ui.label for="inv-mensaje">
                    Mensaje
                    <span class="font-normal text-muted-foreground">(opcional)</span>
                </x-ui.label>
                <x-ui.textarea id="inv-mensaje" placeholder="Hola, te invito a unirte a nuestro equipo..." rows="3" />
            </x-ui.form-field>

        </div>

        <x-ui.dialog.footer>
            <x-ui.dialog.close>
                <x-ui.button variant="outline">Cancelar</x-ui.button>
            </x-ui.dialog.close>
            <x-ui.dialog.close>
                <x-ui.button>
                    <x-lucide-send class="size-4" />
                    Enviar invitación
                </x-ui.button>
            </x-ui.dialog.close>
        </x-ui.dialog.footer>
    </x-ui.dialog.content>
</x-ui.dialog>