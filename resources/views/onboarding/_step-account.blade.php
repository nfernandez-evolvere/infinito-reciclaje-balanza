<div class="space-y-4">
    <div class="grid grid-cols-2 gap-3">
        <x-ui.form-field for="ob-nombre">
            <x-ui.label for="ob-nombre">Nombre</x-ui.label>
            <x-ui.input id="ob-nombre" placeholder="Juan" />
        </x-ui.form-field>
        <x-ui.form-field for="ob-apellido">
            <x-ui.label for="ob-apellido">Apellido</x-ui.label>
            <x-ui.input id="ob-apellido" placeholder="García" />
        </x-ui.form-field>
    </div>
    <x-ui.form-field for="ob-email">
        <x-ui.label for="ob-email">Email de trabajo</x-ui.label>
        <x-ui.input id="ob-email" type="email" placeholder="juan@empresa.com">
            <x-slot:leading><x-lucide-mail class="size-full" /></x-slot:leading>
        </x-ui.input>
    </x-ui.form-field>
    <x-ui.form-field for="ob-pass">
        <x-ui.label for="ob-pass">Contraseña</x-ui.label>
        <x-ui.input id="ob-pass" type="password" placeholder="Mínimo 8 caracteres" />
        <x-ui.helper-text message="Usá mayúsculas, números y símbolos para mayor seguridad." />
    </x-ui.form-field>
</div>
